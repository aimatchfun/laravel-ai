<?php

namespace AIMatchFun\LaravelAI\Services\Providers;

use Exception;
use Illuminate\Support\Facades\Http;

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
     * @param string $defaultModel
     * @param int $timeout
     * @return void
     */
    public function __construct(string $baseUrl, string $defaultModel, int $timeout = 30)
    {
        $this->baseUrl = $baseUrl;
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
        try {
            $payload = [
                'model' => $this->model,
                'temperature' => $this->creativityLevel,
                'stream' => false,
            ];

            // Format messages according to Ollama's API
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

            $response = Http::timeout($this->timeout)->post($this->baseUrl . '/api/chat', $payload);

            if ($response->successful()) {
                $data = $response->json();
                return $data['message']['content'] ?? '';
            } else {
                throw new Exception('Failed to get response from Ollama: ' . $response->body());
            }
        } catch (Exception $e) {
            throw new Exception('Ollama API error: ' . $e->getMessage());
        }
    }
}