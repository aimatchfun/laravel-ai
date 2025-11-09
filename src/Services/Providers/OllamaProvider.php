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
            'model' => $this->model ?? $this->config->get('ai.providers.ollama.default_model'),
            'temperature' => $this->creativityLevel,
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
            'model' => $this->model ?? $this->config->get('ai.providers.ollama.default_model'),
            'temperature' => $this->creativityLevel,
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
}
