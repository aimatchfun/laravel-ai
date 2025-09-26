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
        };
    }

    /**
     * Check if the provider is valid
     */
    public static function isValid(string $provider): bool
    {
        return in_array($provider, self::values());
    }
} 