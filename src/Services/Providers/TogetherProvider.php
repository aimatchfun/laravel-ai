<?php

namespace AIMatchFun\LaravelAI\Services\Providers;

use Exception;
use Illuminate\Support\Facades\Http;

class TogetherProvider extends AbstractProvider
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
     * Create a new Together provider instance.
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

            // Format messages according to Together AI's API (OpenAI compatible)
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

            // Add response format if specified
            if ($this->responseFormat) {
                $payload['response_format'] = $this->responseFormat;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->timeout($this->timeout)->post('https://api.together.xyz/v1/chat/completions', $payload);

            if ($response->successful()) {
                $this->lastResponse = $response->json();
                return $this->lastResponse['choices'][0]['message']['content'] ?? '';
            } else {
                throw new Exception('Failed to get response from Together AI: ' . $response->body());
            }
        } catch (Exception $e) {
            throw new Exception('Together AI API error: ' . $e->getMessage());
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

            if ($this->responseFormat) {
                $payload['response_format'] = $this->responseFormat;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->timeout($this->timeout)->post('https://api.together.xyz/v1/chat/completions', $payload);

            if ($response->failed()) {
                throw new Exception('Failed to get response from Together AI: ' . $response->body());
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
            throw new Exception('Together AI API error: ' . $e->getMessage());
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
}
