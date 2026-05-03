<?php

namespace AIMatchFun\LaravelAI\Services\Providers;

use Exception;
use Illuminate\Support\Facades\Http;

class GrokProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $model;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * Create a new xAI Grok provider instance.
     *
     * @param string $apiKey
     * @param string $defaultModel
     * @param int $timeout
     * @param string $baseUrl
     * @return void
     */
    public function __construct(string $apiKey, string $defaultModel, int $timeout = 30, string $baseUrl = 'https://api.x.ai/v1')
    {
        $this->apiKey = $apiKey;
        $this->model = $defaultModel;
        $this->timeout = $timeout;
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Generate a response from the AI.
     *
     * @return string
     * @throws \Exception
     */
    public function generateResponse()
    {
        if ($this->streamMode) {
            $fullResponse = '';
            foreach ($this->generateStreamResponse() as $chunk) {
                $fullResponse .= $chunk;
            }

            return $fullResponse;
        }

        try {
            $payload = [
                'model' => $this->model,
                'temperature' => $this->temperature,
            ];

            $messages = [];

            if ($this->systemInstruction) {
                $messages[] = [
                    'role' => 'system',
                    'content' => $this->systemInstruction,
                ];
            }

            if (! empty($this->userMessages)) {
                $messages = array_merge($messages, $this->userMessages);
            } else {
                throw new Exception('No user messages provided.');
            }

            $payload['messages'] = $messages;

            $this->applyResponseFormatToPayload($payload);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout($this->timeout)->post($this->baseUrl.'/chat/completions', $payload);

            if ($response->successful()) {
                $this->lastResponse = $response->json();

                return $this->lastResponse['choices'][0]['message']['content'] ?? '';
            }

            throw new Exception('Failed to get response from Grok: '.$response->body());
        } catch (Exception $e) {
            throw new Exception('Grok API error: '.$e->getMessage());
        }
    }

    public function generateStreamResponse()
    {
        try {
            $payload = [
                'model' => $this->model,
                'temperature' => $this->temperature,
                'stream' => true,
            ];

            $messages = [];

            if ($this->systemInstruction) {
                $messages[] = [
                    'role' => 'system',
                    'content' => $this->systemInstruction,
                ];
            }

            if (! empty($this->userMessages)) {
                $messages = array_merge($messages, $this->userMessages);
            } else {
                throw new Exception('No user messages provided.');
            }

            $payload['messages'] = $messages;

            $this->applyResponseFormatToPayload($payload);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout($this->timeout)->post($this->baseUrl.'/chat/completions', $payload);

            if ($response->failed()) {
                throw new Exception('Failed to get response from Grok: '.$response->body());
            }

            $body = $response->body();
            $lines = explode("\n", $body);

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || ! str_starts_with($line, 'data: ')) {
                    continue;
                }

                $data = substr($line, 6);
                if ($data === '[DONE]') {
                    break;
                }

                $json = json_decode($data, true);
                if (isset($json['choices'][0]['delta']['content'])) {
                    yield $json['choices'][0]['delta']['content'];
                }
            }
        } catch (Exception $e) {
            throw new Exception('Grok API error: '.$e->getMessage());
        }
    }

    /**
     * Get usage data from the last response.
     *
     * @return array|null Returns array with 'input_tokens' and 'output_tokens' keys, or null if not available
     */
    public function getUsageData(): ?array
    {
        if (! $this->lastResponse || ! isset($this->lastResponse['usage'])) {
            return null;
        }

        $usage = $this->lastResponse['usage'];

        return [
            'input_tokens' => $usage['prompt_tokens'] ?? null,
            'output_tokens' => $usage['completion_tokens'] ?? null,
        ];
    }

    /**
     * Get additional metadata from the last response.
     *
     * @return array|null Returns array with additional response metadata, or null if not available
     */
    public function getResponseMetadata(): ?array
    {
        if (! $this->lastResponse) {
            return null;
        }

        $choice = $this->lastResponse['choices'][0] ?? [];
        $message = $choice['message'] ?? [];
        $usage = $this->lastResponse['usage'] ?? [];

        return [
            'model' => $this->lastResponse['model'] ?? null,
            'id' => $this->lastResponse['id'] ?? null,
            'object' => $this->lastResponse['object'] ?? null,
            'created' => $this->lastResponse['created'] ?? null,
            'index' => $choice['index'] ?? null,
            'finish_reason' => $choice['finish_reason'] ?? null,
            'refusal' => $message['refusal'] ?? null,
            'annotations' => $message['annotations'] ?? null,
            'logprobs' => $choice['logprobs'] ?? null,
            'total_tokens' => $usage['total_tokens'] ?? null,
            'prompt_tokens_details' => $usage['prompt_tokens_details'] ?? null,
            'completion_tokens_details' => $usage['completion_tokens_details'] ?? null,
            'service_tier' => $this->lastResponse['service_tier'] ?? null,
            'system_fingerprint' => $this->lastResponse['system_fingerprint'] ?? null,
            'raw' => $this->lastResponse,
        ];
    }

    /**
     * Attach OpenAI/x.ai-compatible structured output config when set via AbstractProvider::setResponseFormat().
     *
     * @param  array<string, mixed>  $payload
     */
    protected function applyResponseFormatToPayload(array &$payload): void
    {
        if ($this->responseFormat === null || $this->responseFormat === []) {
            return;
        }

        $payload['response_format'] = $this->normalizeResponseFormatForGrok($this->responseFormat);
    }

    /**
     * Grok follows x.ai structured outputs (response_format.type json_schema, etc.).
     * Maps legacy `{ "type": "json_object", "schema": {...} }` (Novita-style) to `json_schema` + strict envelope.
     *
     * @param  array<string, mixed>  $format
     * @return array<string, mixed>
     */
    protected function normalizeResponseFormatForGrok(array $format): array
    {
        $type = $format['type'] ?? '';

        if ($type === 'json_object' && isset($format['schema']) && is_array($format['schema'])) {
            $jsonSchemaMeta = isset($format['json_schema']) && is_array($format['json_schema'])
                ? $format['json_schema']
                : [];

            $name = 'structured_response';
            if (isset($jsonSchemaMeta['name']) && is_string($jsonSchemaMeta['name']) && $jsonSchemaMeta['name'] !== '') {
                $name = $jsonSchemaMeta['name'];
            } elseif (isset($format['name']) && is_string($format['name']) && $format['name'] !== '') {
                $name = $format['name'];
            }

            $strict = true;
            if (array_key_exists('strict', $jsonSchemaMeta)) {
                $strict = (bool) $jsonSchemaMeta['strict'];
            } elseif (array_key_exists('strict', $format)) {
                $strict = (bool) $format['strict'];
            }

            return [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => $name,
                    'strict' => $strict,
                    'schema' => $format['schema'],
                ],
            ];
        }

        return $format;
    }
}
