<?php

namespace AIMatchFun\LaravelAI\Services\Providers;

use Exception;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Psr\Http\Message\StreamInterface;

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
     * @var int|null
     */
    protected $maxTokens = null;

    /**
     * @var float|null
     */
    protected $temperature = null;

    /**
     * @var float|null
     */
    protected $topP = null;

    /**
     * @var int|null
     */
    protected $topK = null;

    /**
     * @var float|null
     */
    protected $presencePenalty = null;

    /**
     * @var float|null
     */
    protected $frequencyPenalty = null;

    /**
     * @var float|null
     */
    protected $repetitionPenalty = null;

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
        if ($this->streamMode) {
            $fullResponse = '';
            foreach ($this->generateStreamResponse() as $chunk) {
                $fullResponse .= $chunk;
            }
            return $fullResponse;
        }

        $payload = [
            'model' => $this->model,
            'stream' => false,
            'min_p' => 0
        ];

        if ($this->temperature !== null) {
            $payload['temperature'] = $this->temperature;
        }

        if ($this->topP !== null) {
            $payload['top_p'] = $this->topP;
        }

        if ($this->topK !== null) {
            $payload['top_k'] = $this->topK;
        }

        if ($this->presencePenalty !== null) {
            $payload['presence_penalty'] = $this->presencePenalty;
        }

        if ($this->frequencyPenalty !== null) {
            $payload['frequency_penalty'] = $this->frequencyPenalty;
        }

        if ($this->repetitionPenalty !== null) {
            $payload['repetition_penalty'] = $this->repetitionPenalty;
        }

        if ($this->maxTokens !== null) {
            $payload['max_tokens'] = $this->maxTokens;
        }

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

    public function generateStreamResponse()
    {
        $payload = [
            'model' => $this->model,
            'stream' => true,
            'min_p' => 0
        ];

        if ($this->temperature !== null) {
            $payload['temperature'] = $this->temperature;
        }

        if ($this->topP !== null) {
            $payload['top_p'] = $this->topP;
        }

        if ($this->topK !== null) {
            $payload['top_k'] = $this->topK;
        }

        if ($this->presencePenalty !== null) {
            $payload['presence_penalty'] = $this->presencePenalty;
        }

        if ($this->frequencyPenalty !== null) {
            $payload['frequency_penalty'] = $this->frequencyPenalty;
        }

        if ($this->repetitionPenalty !== null) {
            $payload['repetition_penalty'] = $this->repetitionPenalty;
        }

        if ($this->maxTokens !== null) {
            $payload['max_tokens'] = $this->maxTokens;
        }

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

        // Use Guzzle directly for streaming
        $client = new Client([
            'timeout' => $this->timeout,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
        ]);

        try {
            $response = $client->post('https://api.novita.ai/v3/openai/chat/completions', [
                'json' => $payload,
                'stream' => true,
            ]);

            $stream = $response->getBody();
            $buffer = '';

            while (!$stream->eof()) {
                $chunk = $stream->read(8192); // Read 8KB at a time
                if ($chunk === '') {
                    break;
                }

                $buffer .= $chunk;
                
                // Process complete lines
                $lines = explode("\n", $buffer);
                $buffer = array_pop($lines); // Keep incomplete line in buffer

                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line) || !str_starts_with($line, 'data: ')) {
                        continue;
                    }

                    $data = substr($line, 6);
                    if ($data === '[DONE]') {
                        return;
                    }

                    $json = json_decode($data, true);
                    if (json_last_error() === JSON_ERROR_NONE && isset($json['choices'][0]['delta']['content'])) {
                        yield $json['choices'][0]['delta']['content'];
                    }
                }
            }

            // Process remaining buffer
            if (!empty($buffer)) {
                $line = trim($buffer);
                if (str_starts_with($line, 'data: ')) {
                    $data = substr($line, 6);
                    if ($data !== '[DONE]') {
                        $json = json_decode($data, true);
                        if (json_last_error() === JSON_ERROR_NONE && isset($json['choices'][0]['delta']['content'])) {
                            yield $json['choices'][0]['delta']['content'];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            throw new Exception('Failed to get streaming response from Novita: ' . $e->getMessage());
        }
    }

    /**
     * Set the maximum number of tokens to generate.
     *
     * @param int $maxTokens
     * @return $this
     */
    public function maxTokens(int $maxTokens)
    {
        $this->maxTokens = $maxTokens;
        return $this;
    }

    /**
     * Set the temperature for the AI response.
     * Controls randomness. Higher = more creative.
     *
     * @param float $temperature
     * @return $this
     */
    public function temperature(float $temperature)
    {
        $this->temperature = $temperature;
        return $this;
    }

    /**
     * Set the top_p parameter for nucleus sampling.
     * Controls cumulative probability.
     *
     * @param float $topP
     * @return $this
     */
    public function topP(float $topP)
    {
        $this->topP = $topP;
        return $this;
    }

    /**
     * Set the top_k parameter.
     * Limits candidate token count.
     *
     * @param int $topK
     * @return $this
     */
    public function topK(int $topK)
    {
        $this->topK = $topK;
        return $this;
    }

    /**
     * Set the presence penalty.
     * Controls repeated tokens of the texts. If one token has already existed in the text, penalty will come, this results in more token in the text.
     *
     * @param float $presencePenalty
     * @return $this
     */
    public function presencePenalty(float $presencePenalty)
    {
        $this->presencePenalty = $presencePenalty;
        return $this;
    }

    /**
     * Set the frequency penalty.
     * Control token frequency of the texts. Every time the same token exist in the text, penalty will come, which results in less same token in the future in the text.
     *
     * @param float $frequencyPenalty
     * @return $this
     */
    public function frequencyPenalty(float $frequencyPenalty)
    {
        $this->frequencyPenalty = $frequencyPenalty;
        return $this;
    }

    /**
     * Set the repetition penalty.
     * Penalizes or encourages repetition.
     *
     * @param float $repetitionPenalty
     * @return $this
     */
    public function repetitionPenalty(float $repetitionPenalty)
    {
        $this->repetitionPenalty = $repetitionPenalty;
        return $this;
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
