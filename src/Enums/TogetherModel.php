<?php

namespace AIMatchFun\LaravelAI\Enums;

enum TogetherModel: string
{
    case KIMI_K2_INSTRUCT_0905 = 'moonshotai/Kimi-K2-Instruct-0905';
    case QWEN3_NEXT_80B_A3B_INSTRUCT = 'Qwen/Qwen3-Next-80B-A3B-Instruct';
    case LLAMA_4_MAVERICK_17B_128E_INSTRUCT_FP8 = 'meta-llama/Llama-4-Maverick-17B-128E-Instruct-FP8';

    /**
     * Get all available models as an array
     */
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get model name from value
     */
    public static function fromValue(string $value): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case;
            }
        }

        return null;
    }
}
