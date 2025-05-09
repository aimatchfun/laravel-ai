<?php

namespace AIMatchFun\LaravelAI\Services\Providers;

use Exception;
use Illuminate\Support\Facades\Http;

class AnthropicProvider extends AbstractProvider
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
     * Create a new Anthropic provider instance.
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
                'temperature' => $this->creativityLevel,
                'max_tokens' => 1024
            ];

            // Format messages according to Anthropic's API
            $messages = [];
            
            if ($this->systemInstruction) {
                $payload['system'] = $this->systemInstruction;
            }
            
            if (!empty($this->userMessages)) {
                foreach ($this->userMessages as $msg) {
                    $messages[] = [
                        'role' => $msg['role'],
                        'content' => $msg['content']
                    ];
                }
            } else {
                throw new Exception('No user messages provided.');
            }
            
            $payload['messages'] = $messages;

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json'
            ])->post('https://api.anthropic.com/v1/messages', $payload);

            if ($response->successful()) {
                $data = $response->json();
                return $data['content'][0]['text'] ?? '';
            } else {
                throw new Exception('Failed to get response from Anthropic: ' . $response->body());
            }
        } catch (Exception $e) {
            throw new Exception('Anthropic API error: ' . $e->getMessage());
        }
    }
}