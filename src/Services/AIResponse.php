<?php

namespace AIMatchFun\LaravelAI\Services;

class AIResponse
{
    public function __construct(
        public string $answer,
        public ?int $inputTokens = null,
        public ?int $outputTokens = null,
        public ?string $model = null,
        public ?string $createdAt = null,
        public ?bool $done = null,
        public ?string $doneReason = null,
        public ?int $totalDuration = null,
        public ?int $loadDuration = null,
        public ?int $promptEvalCount = null,
        public ?int $promptEvalDuration = null,
        public ?int $evalCount = null,
        public ?int $evalDuration = null,
        public ?string $thinking = null,
        
        // Anthropic-specific fields
        public ?string $id = null,
        public ?string $type = null,
        public ?string $role = null,
        public ?string $stopReason = null,
        public ?string $stopSequence = null,
        public ?int $cacheCreationInputTokens = null,
        public ?int $cacheReadInputTokens = null,
        public ?array $cacheCreation = null,
        public ?string $serviceTier = null,
        // OpenAI-specific fields
        public ?string $object = null,
        public ?int $created = null,
        public ?int $index = null,
        public ?string $finishReason = null,
        public ?string $refusal = null,
        public ?array $annotations = null,
        public ?array $logprobs = null,
        public ?int $totalTokens = null,
        public ?array $promptTokensDetails = null,
        public ?array $completionTokensDetails = null,
        public ?string $systemFingerprint = null,
        // Novita-specific fields
        public ?array $contentFilterResults = null,
        // Together-specific fields
        public ?int $seed = null,
        public ?array $toolCalls = null,
        public ?int $cachedTokens = null,
        public ?array $raw = null
    ) {}
}