<?php

namespace AIMatchFun\LaravelAI\Services;

class AIResponse
{
    public function __construct(
        public string $answer,
        public ?int $inputTokens = null,
        public ?int $outputTokens = null
    ) {}
}