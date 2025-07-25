<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default AI provider that will be used for
    | text generation. Supported providers are "ollama", "openai", 
    | "anthropic", and others as implemented.
    |
    */
    'default_provider' => env('AI_PROVIDER', 'ollama'),

    /*
    |--------------------------------------------------------------------------
    | AI Providers Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can define all the configuration settings for each provider.
    |
    */
    'providers' => [
        'ollama' => [
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
            'default_model' => env('OLLAMA_DEFAULT_MODEL', 'llama3'),
            'timeout' => env('OLLAMA_TIMEOUT', 30), 
        ],
        
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'default_model' => env('OPENAI_DEFAULT_MODEL', 'gpt-4o'),
            'timeout' => env('OPENAI_TIMEOUT', 30), 
        ],
        
        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'default_model' => env('ANTHROPIC_DEFAULT_MODEL', 'claude-3-opus-20240229'),
            'timeout' => env('ANTHROPIC_TIMEOUT', 30), 
        ],

        'novita' => [
            'api_key' => env('NOVITA_API_KEY'),
            'default_model' => env('NOVITA_DEFAULT_MODEL', 'deepseek/deepseek-v3-0324'),
            'timeout' => env('NOVITA_TIMEOUT', 30), 
        ],

        'modelslab' => [
            'api_key' => env('MODELSLAB_API_KEY'),
            'default_model' => env('MODELSLAB_DEFAULT_MODEL', 'llama3'),
            'timeout' => env('MODELSLAB_TIMEOUT', 30), 
        ],
    ],
];