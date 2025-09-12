<?php

namespace AIMatchFun\LaravelAI\Enums;

enum NovitaModel: string
{
    case ERNIE_4_5_VL_28B_A3B = 'baidu/ernie-4.5-vl-28b-a3b';
    case ERNIE_4_5_21B_A3B = 'baidu/ernie-4.5-21B-a3b';
    case ERNIE_4_5_0_3B = 'baidu/ernie-4.5-0.3b';
    case GEMMA_3_1B_IT = 'google/gemma-3-1b-it';
    case QWEN_3_4B_FP8 = 'qwen/qwen3-4b-fp8';
    case QWEN_2_5_7B_INSTRUCT = 'qwen/qwen2.5-7b-instruct';
    case LLAMA_3_2_1B_INSTRUCT = 'meta-llama/llama-3.2-1b-instruct';
    case DEEPSEEK_V3_0324 = 'deepseek/deepseek-v3-0324';
    case KIMI_K2_INSTRUCT = 'moonshotai/kimi-k2-instruct';
    case DEEPSEEK_R1_0528 = 'deepseek/deepseek-r1-0528';
    case ERNIE_4_5_VL_424B_A47B = 'baidu/ernie-4.5-vl-424b-a47b';
    case ERNIE_4_5_300B_A47B_PADDLE = 'baidu/ernie-4.5-300b-a47b-paddle';
    case QWEN_3_30B_A3B_FP8 = 'qwen/qwen3-30b-a3b-fp8';
    case MINIMAX_M1_80K = 'minimaxai/minimax-m1-80k';
    case DEEPSEEK_R1_0528_QWEN3_8B = 'deepseek/deepseek-r1-0528-qwen3-8b';
    case QWEN_3_32B_FP8 = 'qwen/qwen3-32b-fp8';
    case QWEN_2_5_VL_72B_INSTRUCT = 'qwen/qwen2.5-vl-72b-instruct';
    case QWEN_3_235B_A22B_FP8 = 'qwen/qwen3-235b-a22b-fp8';
    case DEEPSEEK_V3_TURBO = 'deepseek/deepseek-v3-turbo';
    case GLM_4_1V_9B_THINKING = 'thudm/glm-4.1v-9b-thinking';
    case LLAMA_4_MAVERICK_17B_128E_INSTRUCT_FP8 = 'meta-llama/llama-4-maverick-17b-128e-instruct-fp8';
    case GEMMA_3_27B_IT = 'google/gemma-3-27b-it';
    case DEEPSEEK_R1_TURBO = 'deepseek/deepseek-r1-turbo';
    case L3_8B_STHENO_V3_2 = 'Sao10K/L3-8B-Stheno-v3.2';
    case MYTHOMAX_L2_13B = 'gryphe/mythomax-l2-13b';
    case DEEPSEEK_PROVER_V2_671B = 'deepseek/deepseek-prover-v2-671b';
    case LLAMA_4_SCOUT_17B_16E_INSTRUCT = 'meta-llama/llama-4-scout-17b-16e-instruct';
    case DEEPSEEK_R1_DISTILL_LLAMA_8B = 'deepseek/deepseek-r1-distill-llama-8b';
    case LLAMA_3_1_8B_INSTRUCT = 'meta-llama/llama-3.1-8b-instruct';
    case DEEPSEEK_R1_DISTILL_QWEN_14B = 'deepseek/deepseek-r1-distill-qwen-14b';
    case LLAMA_3_3_70B_INSTRUCT = 'meta-llama/llama-3.3-70b-instruct';
    case QWEN_2_5_72B_INSTRUCT = 'qwen/qwen-2.5-72b-instruct';
    case MISTRAL_NEMO = 'mistralai/mistral-nemo';
    case DEEPSEEK_R1_DISTILL_QWEN_32B = 'deepseek/deepseek-r1-distill-qwen-32b';
    case LLAMA_3_8B_INSTRUCT = 'meta-llama/llama-3-8b-instruct';
    case WIZARDLM_2_8X22B = 'microsoft/wizardlm-2-8x22b';
    case DEEPSEEK_R1_DISTILL_LLAMA_70B = 'deepseek/deepseek-r1-distill-llama-70b';
    case MISTRAL_7B_INSTRUCT = 'mistralai/mistral-7b-instruct';
    case LLAMA_3_70B_INSTRUCT = 'meta-llama/llama-3-70b-instruct';
    case HERMES_2_PRO_LLAMA_3_8B = 'nousresearch/hermes-2-pro-llama-3-8b';
    case L3_70B_EURYALE_V2_1 = 'sao10k/l3-70b-euryale-v2.1';
    case DOLPHIN_MIXTRAL_8X22B = 'cognitivecomputations/dolphin-mixtral-8x22b';
    case MIDNIGHT_ROSE_70B = 'sophosympatheia/midnight-rose-70b';
    case L3_8B_LUNARIS = 'sao10k/l3-8b-lunaris';
    case QWEN_3_8B_FP8 = 'qwen/qwen3-8b-fp8';
    case GLM_4_32B_0414 = 'thudm/glm-4-32b-0414';
    case LLAMA_3_2_3B_INSTRUCT = 'meta-llama/llama-3.2-3b-instruct';
    case L31_70B_EURYALE_V2_2 = 'sao10k/l31-70b-euryale-v2.2';
    case KIMI_K2_0905 = 'moonshotai/kimi-k2-0905';

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
