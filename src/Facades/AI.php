<?php

namespace AIMatchFun\LaravelAI\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \AIMatchFun\LaravelAI\Services\AIService provider(string $provider)
 * @method static \AIMatchFun\LaravelAI\Services\AIService model(string $model)
 * @method static \AIMatchFun\LaravelAI\Services\AIService systemInstruction(string $instruction)
 * @method static \AIMatchFun\LaravelAI\Services\AIService withUserMessage(string $message)
 * @method static \AIMatchFun\LaravelAI\Services\AIService withUserMessages(array $messages)
 * @method static \AIMatchFun\LaravelAI\Services\AIService creativityLevel(float $level)
 * @method static string answer()
 * @method static \AIMatchFun\LaravelAI\Services\AIService prompt(string $prompt)
 * @method static \AIMatchFun\LaravelAI\Services\AIService prompts(array $prompts)
 * @method static string run()
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