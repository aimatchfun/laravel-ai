<?php

namespace AIMatchFun\LaravelAI\Services\Providers;

use Exception;
use Illuminate\Support\Facades\Http;

class NovitaProvider extends AbstractProvider
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
     * Create a new Novita provider instance.
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
        $payload = [
            'model' => $this->model,
            'temperature' => $this->creativityLevel,
            'stream' => false,
            'top_p' => 1,
            'min_p' => 0,
            'top_k' => 50,
            'presence_penalty' => 0,
            'frequency_penalty' => 0,
            'repetition_penalty' => 1
        ];

        // Set response format - use structured outputs if provided, otherwise default to text
        if ($this->responseFormat) {
            $payload['response_format'] = $this->responseFormat;
        } else {
            $payload['format'] = ['type' => 'text'];
        }

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
        ])->timeout($this->timeout)->post('https://api.novita.ai/v3/openai/chat/completions', $payload);

        if ($response->failed()) {
            throw new Exception('Failed to get response from Novita: ' . $response->body());
        }

        $this->lastResponse = $response->json();

        return $this->lastResponse['choices'][0]['message']['content'] ?? '';
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
