<?php

namespace AIMatchFun\LaravelAI\Services;

class AIResponse
{
    public function __construct(
        public string $conversation_id,
        public string $answer
    ) {}
}