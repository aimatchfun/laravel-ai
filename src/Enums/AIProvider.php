<?php

namespace AIMatchFun\LaravelAI\Enums;

enum AIProvider: string
{
    case OLLAMA = 'ollama';
    case OPENAI = 'openai';
    case ANTHROPIC = 'anthropic';
    case NOVITA = 'novita';
    case MODELSLAB = 'modelslab';
    case OPENROUTER = 'openrouter';
    case TOGETHER = 'together';

    /**
     * Get all available providers as an array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all available providers as an array with labels
     */
    public static function options(): array
    {
        return [
            self::OLLAMA->value => 'Ollama',
            self::OPENAI->value => 'OpenAI',
            self::ANTHROPIC->value => 'Anthropic',
            self::NOVITA->value => 'Novita',
            self::MODELSLAB->value => 'Modelslab',
            self::OPENROUTER->value => 'OpenRouter',
            self::TOGETHER->value => 'Together',
        ];
    }

    /**
     * Get the display name for the provider
     */
    public function label(): string
    {
        return match($this) {
            self::OLLAMA => 'Ollama',
            self::OPENAI => 'OpenAI',
            self::ANTHROPIC => 'Anthropic',
            self::NOVITA => 'Novita',
            self::MODELSLAB => 'Modelslab',
            self::OPENROUTER => 'OpenRouter',
            self::TOGETHER => 'Together',
        };
    }

    /**
     * Check if the provider is valid
     */
    public static function isValid(string $provider): bool
    {
        return in_array($provider, self::values());
    }

    /**
     * Get provider from string value
     */
    public static function fromValue(string $value): ?self
    {
        return self::tryFrom($value);
    }

    /**
     * Get provider from string value or throw exception
     */
    public static function fromValueOrFail(string $value): self
    {
        $provider = self::tryFrom($value);
        if ($provider === null) {
            throw new \InvalidArgumentException("Invalid provider: {$value}");
        }
        return $provider;
    }
} 