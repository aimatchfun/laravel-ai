<?php

namespace AIMatchFun\LaravelAI\Services\Providers;

use AIMatchFun\LaravelAI\Contracts\AIProvider;

abstract class AbstractProvider implements AIProvider
{
    /**
     * @var string
     */
    protected $model;

    /**
     * @var string|null
     */
    protected $systemInstruction = null;

    /**
     * @var array
     */
    protected $userMessages = [];

    /**
     * @var float
     */
    protected $temperature = 1.0;

    /**
     * @var array|null
     */
    protected $responseFormat = null;

    /**
     * @var bool
     */
    protected $streamMode = false;

    /**
     * @var array|null
     */
    protected $lastResponse = null;

    /**
     * Get usage data from the last response.
     *
     * @return array|null Returns array with 'input_tokens' and 'output_tokens' keys, or null if not available
     */
    public function getUsageData(): ?array
    {
        return null;
    }

    /**
     * Set the model to use.
     *
     * @param string $model
     * @return $this
     */
    public function setModel(string $model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Set the system instruction.
     *
     * @param string $instruction
     * @return $this
     */
    public function setSystemInstruction(string $instruction)
    {
        $this->systemInstruction = $instruction;
        return $this;
    }

    /**
     * Set the user messages.
     *
     * @param array $messages
     * @return $this
     */
    public function setUserMessages(array $messages)
    {
        $this->userMessages = $messages;
        return $this;
    }

    /**
     * Set the temperature.
     *
     * @param float $level
     * @return $this
     */
    public function setTemperature(float $level)
    {
        $this->temperature = $level;
        return $this;
    }

    /**
     * Set the response format for structured outputs.
     *
     * @param array $format
     * @return $this
     */
    public function setResponseFormat(array $format)
    {
        $this->responseFormat = $format;
        return $this;
    }

    /**
     * Set the stream mode.
     *
     * @param bool $stream
     * @return $this
     */
    public function setStreamMode(bool $stream)
    {
        $this->streamMode = $stream;
        return $this;
    }

    /**
     * Generate a response from the AI.
     *
     * @return string
     */
    abstract public function generateResponse();

    /**
     * Generate a streaming response from the AI.
     *
     * @return \Generator
     */
    abstract public function generateStreamResponse();
}