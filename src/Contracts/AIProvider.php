<?php

namespace AIMatchFun\LaravelAI\Contracts;

interface AIProvider
{
    /**
     * Set the model to use.
     *
     * @param string $model
     * @return $this
     */
    public function setModel(string $model);

    /**
     * Set the system instruction.
     *
     * @param string $instruction
     * @return $this
     */
    public function setSystemInstruction(string $instruction);

    /**
     * Set the user messages.
     *
     * @param array $messages
     * @return $this
     */
    public function setUserMessages(array $messages);

    /**
     * Set the creativity level.
     *
     * @param float $level
     * @return $this
     */
    public function setCreativityLevel(float $level);

    /**
     * Set the response format for structured outputs.
     *
     * @param array $format
     * @return $this
     */
    public function setResponseFormat(array $format);

    /**
     * Generate a response from the AI.
     *
     * @return string
     */
    public function generateResponse();
}