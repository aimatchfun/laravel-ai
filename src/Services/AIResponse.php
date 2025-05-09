<?php

namespace Daavelar\LaravelAI\Services;

class AIResponse
{
    public function __construct(
        public int $conversation_id,
        public string $answer
    ) {}
}