<?php

namespace AIMatchFun\LaravelAI\Services\Providers;

use Exception;
use Illuminate\Support\Facades\Http;

class OpenAIProvider extends AbstractProvider
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
     * @var int
     */
    protected $timeout;

    /**
     * Create a new OpenAI provider instance.
     *
     * @param string $apiKey
     * @param string $defaultModel
     * @param int $timeout
     * @return void
     */
    public function __construct(string $apiKey, string $defaultModel, int $timeout = 30)
    {
        $this->apiKey = $apiKey;
        $this->model = $defaultModel;
        $this->timeout = $timeout;
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
                'temperature' => $this->temperature
            ];

            // Format messages according to OpenAI's API
            $messages = [];
            
            if ($this->systemInstruction) {
                $messages[] = [
                    'role' => 'system',
                    'content' => $this->systemInstruction
                ];
            }
            
            if (!empty($this->userMessages)) {
                $messages = array_merge($messages, $this->userMessages);
            } else {
                throw new Exception('No user messages provided.');
            }
            
            $payload['messages'] = $messages;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->timeout($this->timeout)->post('https://api.openai.com/v1/chat/completions', $payload);

            if ($response->successful()) {
                $this->lastResponse = $response->json();
                return $this->lastResponse['choices'][0]['message']['content'] ?? '';
            } else {
                throw new Exception('Failed to get response from OpenAI: ' . $response->body());
            }
        } catch (Exception $e) {
            throw new Exception('OpenAI API error: ' . $e->getMessage());
        }
    }

    public function generateStreamResponse()
    {
        try {
            $payload = [
                'model' => $this->model,
                'temperature' => $this->temperature,
                'stream' => true
            ];

            $messages = [];
            
            if ($this->systemInstruction) {
                $messages[] = [
                    'role' => 'system',
                    'content' => $this->systemInstruction
                ];
            }
            
            if (!empty($this->userMessages)) {
                $messages = array_merge($messages, $this->userMessages);
            } else {
                throw new Exception('No user messages provided.');
            }
            
            $payload['messages'] = $messages;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->timeout($this->timeout)->post('https://api.openai.com/v1/chat/completions', $payload);

            if ($response->failed()) {
                throw new Exception('Failed to get response from OpenAI: ' . $response->body());
            }

            $body = $response->body();
            $lines = explode("\n", $body);

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || !str_starts_with($line, 'data: ')) {
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
            throw new Exception('OpenAI API error: ' . $e->getMessage());
        }
    }

    /**
     * Get usage data from the last response.
     *
     * @return array|null Returns array with 'input_tokens' and 'output_tokens' keys, or null if not available
     */
    public function getUsageData(): ?array
    {
        if (!$this->lastResponse || !isset($this->lastResponse['usage'])) {
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
        if (!$this->lastResponse) {
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
}