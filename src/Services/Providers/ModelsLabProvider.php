<?php

namespace AIMatchFun\LaravelAI\Services\Providers;

use AIMatchFun\LaravelAI\Contracts\AIProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ModelsLabProvider extends AbstractProvider implements AIProvider
{
    /**
     * @var string
     */
    protected $baseUrl = 'https://modelslab.com';

    /**
     * @var string
     */
    protected $model;

    /**
     * @var int
     */ 

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var int
     */
    protected $timeout; 

    /**
     * Create a new ModelsLab provider instance.whatever
     *
     * @param string $baseUrl
     * @param string $model
     * @param int $timeout
     * @param string $apiKey
     * @return void
     */ 

    public function __construct(string $apiKey, string $model, int $timeout = 30)
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
        $this->timeout = $timeout;
    }

    public function generateResponse()
    {
        if ($this->streamMode) {
            $fullResponse = '';
            foreach ($this->generateStreamResponse() as $chunk) {
                $fullResponse .= $chunk;
            }
            return $fullResponse;
        }

        $messages = array_merge([[
            'role' => 'system',
            'content' => $this->systemInstruction
        ]], $this->userMessages);

        $response = Http::timeout($this->timeout)
            ->post($this->baseUrl . '/api/v6/llm/uncensored_chat', [
                'key' => $this->apiKey,
                'messages' => $messages,
                'max_tokens' => 1000,
                'temperature' => $this->temperature
            ]);
        
        if ($response->failed()) {
            Log::error("Erro ao chamar API ModelsLab: " . $response->body());
            throw new Exception("Erro ao chamar API ModelsLab: " . $response->body());
        }
        
        $this->lastResponse = $response->json();
        
        return $this->lastResponse['message'] ?? '';
    }

    public function generateStreamResponse()
    {
        // ModelsLab doesn't support streaming, so we yield the full response
        $messages = array_merge([[
            'role' => 'system',
            'content' => $this->systemInstruction
        ]], $this->userMessages);

        $response = Http::timeout($this->timeout)
            ->post($this->baseUrl . '/api/v6/llm/uncensored_chat', [
                'key' => $this->apiKey,
                'messages' => $messages,
                'max_tokens' => 1000,
                'temperature' => $this->temperature
            ]);
        
        if ($response->failed()) {
            Log::error("Erro ao chamar API ModelsLab: " . $response->body());
            throw new Exception("Erro ao chamar API ModelsLab: " . $response->body());
        }
        
        $this->lastResponse = $response->json();
        $message = $this->lastResponse['message'] ?? '';
        
        yield $message;
    }

    /**
     * Get usage data from the last response.
     *
     * @return array|null Returns array with 'input_tokens' and 'output_tokens' keys, or null if not available
     */
    public function getUsageData(): ?array
    {
        // ModelsLab doesn't provide usage data in a standardized format
        return null;
    }
}

