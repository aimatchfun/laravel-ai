<?php

namespace AIMatchFun\LaravelAI\Facades;

use AIMatchFun\LaravelAI\Enums\AIProvider as AIProviderEnum;
use AIMatchFun\LaravelAI\Services\AICreativity;
use AIMatchFun\LaravelAI\Services\AIResponse;
use Illuminate\Support\Facades\Facade;

/**
 * AI Facade
 * 
 * Provides a fluent interface for interacting with AI providers.
 * 
 * @method static \AIMatchFun\LaravelAI\Services\AIService provider(string|\AIMatchFun\LaravelAI\Enums\AIProvider $provider) Set the AI provider to use
 * @method static \AIMatchFun\LaravelAI\Services\AIService model(string $model) Set the model to use
 * @method static \AIMatchFun\LaravelAI\Services\AIService systemInstruction(string $instruction) Set the system instruction
 * @method static \AIMatchFun\LaravelAI\Services\AIService previewMessages(array $messages) Set preview messages for conversation context
 * @method static \AIMatchFun\LaravelAI\Services\AIService temperature(\AIMatchFun\LaravelAI\Services\AICreativity $level) Set the temperature/creativity level
 * @method static \AIMatchFun\LaravelAI\Services\AIService prompt(string $prompt) Set the user prompt message
 * @method static \AIMatchFun\LaravelAI\Services\AIService stream(bool $stream = true) Enable or disable stream mode
 * @method static \AIMatchFun\LaravelAI\Services\AIService responseFormat(array $format) Set the response format for structured outputs
 * @method static \Generator streamResponse() Get streaming response from the AI
 * @method static string answer() Get the answer from the AI (returns string)
 * @method static \AIMatchFun\LaravelAI\Services\AIResponse run() Execute the AI request and get response with usage data
 * @method static string getDefaultDriver() Get the default AI provider name
 *
 * @see \AIMatchFun\LaravelAI\Services\AIService
 */
class AI extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ai';
    }
}