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
     * Create a new Ollama provider instance.
     *
     * @param string $baseUrl
     * @param string $defaultModel
     * @return void
     */
    public function __construct(string $baseUrl, string $defaultModel)
    {
        $this->baseUrl = $baseUrl;
        $this->model = $defaultModel;
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

            $response = Http::post($this->baseUrl . '/api/chat', $payload);

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