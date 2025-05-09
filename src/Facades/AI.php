<?php

namespace Daavelar\LaravelAI\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Daavelar\LaravelAI\Services\AIService provider(string $provider)
 * @method static \Daavelar\LaravelAI\Services\AIService model(string $model)
 * @method static \Daavelar\LaravelAI\Services\AIService withSystemInstruction(string $instruction)
 * @method static \Daavelar\LaravelAI\Services\AIService withUserMessage(string $message)
 * @method static \Daavelar\LaravelAI\Services\AIService withUserMessages(array $messages)
 * @method static \Daavelar\LaravelAI\Services\AIService creativityLevel(float $level)
 * @method static string answer()
 * @method static \Daavelar\LaravelAI\Services\AIService withPrompt(string $prompt)
 * @method static \Daavelar\LaravelAI\Services\AIService withPrompts(array $prompts)
 * @method static string run()
 * 
 * @see \Daavelar\LaravelAI\Services\AIService
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