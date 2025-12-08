<?php

namespace AIMatchFun\LaravelAI\Services\Providers;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaProvider extends AbstractProvider
{
    /**
    * @var string
    */
    protected $baseUrl;
    
    /**
    * @var string
    */
    protected $model;
    
    /**
    * @var int
    */
    protected $timeout;
    
    /**
    * Create a new Ollama provider instance.
    *
    * @param string $baseUrl
    * @param string $model
    * @param int $timeout
    * @return void
    */
    public function __construct(string $baseUrl, string $model, int $timeout = 30)
    {
        $this->baseUrl = $baseUrl;
        $this->model = $model;
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

        $payload = [
            'model' => $this->model,
            'temperature' => $this->temperature,
            'stream' => false,
        ];
        
        $messages = [];
        
        if ($this->systemInstruction) {
            $messages[] = [
                'role' => 'system',
                'content' => $this->systemInstruction
            ];
        }
        
        $messages = array_merge($messages, $this->userMessages);
        
        $payload['messages'] = $messages;

        $response = Http::withToken(config('ai.providers.ollama.token'))
            ->timeout($this->timeout)
            ->post(rtrim($this->baseUrl, '/') . '/api/chat', $payload);
        
        if ($response->failed()) {
            throw new Exception('Failed to get response from Ollama: ' . $response->body());
        }
        
        $this->lastResponse = $response->json();
        
        return $this->lastResponse['message']['content'] ?? '';
    }

    public function generateStreamResponse()
    {
        $payload = [
            'model' => $this->model,
            'temperature' => $this->temperature,
            'stream' => true,
        ];
        
        $messages = [];
        
        if ($this->systemInstruction) {
            $messages[] = [
                'role' => 'system',
                'content' => $this->systemInstruction
            ];
        }
        
        $messages = array_merge($messages, $this->userMessages);
        
        $payload['messages'] = $messages;

        $response = Http::withToken(config('ai.providers.ollama.token'))
            ->timeout($this->timeout)
            ->post(rtrim($this->baseUrl, '/') . '/api/chat', $payload);

        if ($response->failed()) {
            throw new Exception('Failed to get response from Ollama: ' . $response->body());
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
            if (isset($json['message']['content'])) {
                yield $json['message']['content'];
            }
        }
    }

    /**
     * Get usage data from the last response.
     *
     * @return array|null Returns array with 'input_tokens' and 'output_tokens' keys, or null if not available
     */
    public function getUsageData(): ?array
    {
        // Ollama doesn't provide usage data in a standardized format
        return null;
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

        return [
            'model' => $this->lastResponse['model'] ?? null,
            'created_at' => $this->lastResponse['created_at'] ?? null,
            'done' => $this->lastResponse['done'] ?? null,
            'done_reason' => $this->lastResponse['done_reason'] ?? null,
            'total_duration' => $this->lastResponse['total_duration'] ?? null,
            'load_duration' => $this->lastResponse['load_duration'] ?? null,
            'prompt_eval_count' => $this->lastResponse['prompt_eval_count'] ?? null,
            'prompt_eval_duration' => $this->lastResponse['prompt_eval_duration'] ?? null,
            'eval_count' => $this->lastResponse['eval_count'] ?? null,
            'eval_duration' => $this->lastResponse['eval_duration'] ?? null,
            'thinking' => $this->lastResponse['message']['thinking'] ?? null,
            'raw' => $this->lastResponse,
        ];
    }
}
