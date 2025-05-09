<?php

namespace AIMatchFun\LaravelAI\Facades;

use AIMatchFun\LaravelAI\Services\AICreativity;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \AIMatchFun\LaravelAI\Services\AIService provider(string $provider)
 * @method static \AIMatchFun\LaravelAI\Services\AIService model(string $model)
 * @method static \AIMatchFun\LaravelAI\Services\AIService systemInstruction(string $instruction)
 * @method static \AIMatchFun\LaravelAI\Services\AIService conversationHistory(string $conversationId)
 * @method static \AIMatchFun\LaravelAI\Services\AIService creativityLevel(AICreativity $level)
 * @method static \AIMatchFun\LaravelAI\Services\AIService prompt(string $prompt)
 * @method static \AIMatchFun\LaravelAI\Services\AIService run()
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