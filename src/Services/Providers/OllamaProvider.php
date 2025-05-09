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

        Log::debug('Model: ' . $this->model);
        Log::debug('Creativity: ' . $this->creativityLevel);
        
        $response = Http::timeout($this->timeout)->post(rtrim($this->baseUrl, '/') . '/api/chat', $payload);
        
        if ($response->failed()) {
            throw new Exception('Failed to get response from Ollama: ' . $response->body());
        }
        
        return $response->json('message.content') ?? '';
        
    }
}