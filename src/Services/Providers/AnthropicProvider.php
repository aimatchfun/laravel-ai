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
     * @var int
     */
    protected $timeout;

    /**
     * Create a new Anthropic provider instance.
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
            ])->timeout($this->timeout)->post('https://api.anthropic.com/v1/messages', $payload);

            if ($response->successful()) {
                $this->lastResponse = $response->json();
                return $this->lastResponse['content'][0]['text'] ?? '';
            } else {
                throw new Exception('Failed to get response from Anthropic: ' . $response->body());
            }
        } catch (Exception $e) {
            throw new Exception('Anthropic API error: ' . $e->getMessage());
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
            'input_tokens' => $usage['input_tokens'] ?? null,
            'output_tokens' => $usage['output_tokens'] ?? null,
        ];
    }
}