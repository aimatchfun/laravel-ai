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
     * Create a new OpenAI provider instance.
     *
     * @param string $apiKey
     * @param string $defaultModel
     * @return void
     */
    public function __construct(string $apiKey, string $defaultModel)
    {
        $this->apiKey = $apiKey;
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
                'temperature' => $this->creativityLevel
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
            ])->post('https://api.openai.com/v1/chat/completions', $payload);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? '';
            } else {
                throw new Exception('Failed to get response from OpenAI: ' . $response->body());
            }
        } catch (Exception $e) {
            throw new Exception('OpenAI API error: ' . $e->getMessage());
        }
    }
}