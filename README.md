# Laravel AI

A Laravel package that provides a fluent interface for interacting with AI providers like Ollama, OpenAI, Anthropic, Novita, and ModelsLab.

## Installation

You can install the package via composer:

```bash
composer require aimatchfun/laravel-ai
```

The package will automatically register itself.

You can publish the configuration file with:

```bash
php artisan vendor:publish --provider="AIMatchFun\LaravelAI\Providers\AIServiceProvider" --tag="config"
```

This will publish a `config/ai.php` file where you can configure your AI providers.

### Preview Messages

The package now supports preview messages to provide context for conversations. You can pass an array of messages in the format that AI models expect:

```php
$messages = [
    ['role' => 'user', 'content' => 'Hello, how are you?'],
    ['role' => 'assistant', 'content' => 'I am doing well, thank you!'],
    ['role' => 'user', 'content' => 'What can you help me with?']
];
```

Or use the Message object for better type safety:

```php
use AIMatchFun\LaravelAI\Services\Message;

$messages = [
    Message::user('Hello, how are you?'),
    Message::assistant('I am doing well, thank you!'),
    Message::user('What can you help me with?')
];
```

## Configuration

After publishing the configuration file, you can configure your AI providers in the `config/ai.php` file:

```php
return [
    'default' => env('AI_PROVIDER', 'ollama'),

    'providers' => [
        'ollama' => [
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
            'default_model' => env('OLLAMA_DEFAULT_MODEL', 'llama3'),
            'timeout' => env('OLLAMA_TIMEOUT', 30), // Timeout in seconds
        ],
        
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'default_model' => env('OPENAI_DEFAULT_MODEL', 'gpt-4o'),
            'timeout' => env('OPENAI_TIMEOUT', 30), // Timeout in seconds
        ],
        
        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'default_model' => env('ANTHROPIC_DEFAULT_MODEL', 'claude-3-opus-20240229'),
            'timeout' => env('ANTHROPIC_TIMEOUT', 30), // Timeout in seconds
        ],

        'novita' => [
            'api_key' => env('NOVITA_API_KEY'),
            'default_model' => env('NOVITA_DEFAULT_MODEL', 'deepseek/deepseek-v3-0324'),
            'timeout' => env('NOVITA_TIMEOUT', 30), // Timeout in seconds
        ],

        'modelslab' => [
            'api_key' => env('MODELSLAB_API_KEY'),
            'default_model' => env('MODELSLAB_DEFAULT_MODEL', 'llama3'),
            'timeout' => env('MODELSLAB_TIMEOUT', 30), // Timeout in seconds
        ],
    ],
];
```

You can also set these values in your `.env` file:

```
AI_PROVIDER=ollama
OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_DEFAULT_MODEL=llama3
OLLAMA_TIMEOUT=30

OPENAI_API_KEY=your-openai-api-key
OPENAI_DEFAULT_MODEL=gpt-4o
OPENAI_TIMEOUT=30

ANTHROPIC_API_KEY=your-anthropic-api-key
ANTHROPIC_DEFAULT_MODEL=claude-3-opus-20240229
ANTHROPIC_TIMEOUT=30

NOVITA_API_KEY=your-novita-api-key
NOVITA_DEFAULT_MODEL=deepseek/deepseek-v3-0324
NOVITA_TIMEOUT=30

MODELSLAB_API_KEY=your-modelslab-api-key
MODELSLAB_DEFAULT_MODEL=llama3
MODELSLAB_TIMEOUT=30
```

**Timeout:** You can set the timeout (in seconds) for each provider. If a request takes longer than this value, it will fail with a timeout error. The default is 30 seconds for all providers.

## Usage

The package provides a fluent interface through the `AI` facade:

```php
use AIMatchFun\LaravelAI\Facades\AI;
use AIMatchFun\LaravelAI\Services\AICreativity;

// Basic usage with default provider
$response = AI::prompt('What is Laravel?')
    ->run();

// $response is an object with:

// $response->answer (string)

// Specify a provider
$response = AI::provider('ollama')
    ->prompt('What is Laravel?')
    ->run();

// Specify a model
$response = AI::provider('ollama')
    ->model('llama3')
    ->prompt('What is Laravel?')
    ->run();

// With system instruction
$response = AI::provider('ollama')
    ->model('llama3')
    ->systemInstruction('You are a helpful AI assistant.')
    ->prompt('What is Laravel?')
    ->run();

// Using preview messages for context
$messages = [
    ['role' => 'user', 'content' => 'Hello, how are you?'],
    ['role' => 'assistant', 'content' => 'I am doing well, thank you!']
];

$response = AI::provider('ollama')
    ->model('llama3')
    ->previewMessages($messages)
    ->prompt('What is Laravel?')
    ->run();

// Adjust creativity level (temperature)
$response = AI::provider('ollama')
    ->model('llama3')
    ->prompt('Write a poem about Laravel.')
    ->creativityLevel(AICreativity::HIGH)
    ->run();

// Continue a conversation using preview messages
$response = AI::prompt('Hello, who are you?')->run();

$messages = [
    ['role' => 'user', 'content' => 'Hello, who are you?'],
    ['role' => 'assistant', 'content' => $response->answer]
];

$response = AI::prompt('And what can you do?')
    ->previewMessages($messages)
    ->run();
```

### About the AIResponse object

The `run()` method returns an instance of `AIResponse`:

```php
$response = AI::prompt('What is Laravel?')->run();

$answer = $response->answer; // string
```

- `answer`: The AI's response to your prompt(s).

### Preview Messages

The `previewMessages` method allows you to provide context for your AI conversations by passing an array of previous messages. This is useful for maintaining conversation context without persisting data to a database.

- Messages should be in the format `['role' => 'user|assistant|system', 'content' => 'message content']`
- You can also use the `Message` object for better type safety and validation
- Preview messages are merged with the current prompt before sending to the AI provider

Example:

```php
// Using array format
$messages = [
    ['role' => 'user', 'content' => 'Hello, who are you?'],
    ['role' => 'assistant', 'content' => 'I am an AI assistant.']
];

$response = AI::previewMessages($messages)
    ->prompt('What can you help me with?')
    ->run();

// Using Message objects
use AIMatchFun\LaravelAI\Services\Message;

$messages = [
    Message::user('Hello, who are you?'),
    Message::assistant('I am an AI assistant.')
];

$response = AI::previewMessages($messages)
    ->prompt('What can you help me with?')
    ->run();
```

## Available Models

### Novita Models

The package provides an enum with all available Novita models for easy access and type safety:

```php
use AIMatchFun\LaravelAI\Enums\NovitaModel;

// Use a specific model
$response = AI::provider('novita')
    ->model(NovitaModel::ERNIE_4_5_0_3B->value)
    ->prompt('What is Laravel?')
    ->run();

// Get all available models
$allModels = NovitaModel::getValues();

// Find a model by value
$model = NovitaModel::fromValue('baidu/ernie-4.5-0.3b');
```

**Note:** The list of available models in the enum may become outdated as Novita adds or removes models. Always check the [official Novita documentation](https://novita.ai/) for the most current list of available models.

## Extending

You can add your own AI providers by extending the `AIService` class in a service provider:

```php
use AIMatchFun\LaravelAI\Services\AIService;
use App\Services\AI\CustomProvider;

public function boot()
{
    $this->app->extend('ai', function (AIService $service, $app) {
        $service->extend('custom', function () {
            return new CustomProvider(
                config('ai.providers.custom.api_key'),
                config('ai.providers.custom.default_model')
            );
        });
        
        return $service;
    });
}
```

Your custom provider needs to implement the `AIMatchFun\LaravelAI\Contracts\AIProvider` interface or extend the `AIMatchFun\LaravelAI\Services\Providers\AbstractProvider` class.

## Contributing

Contributions are welcome! If you would like to improve this package, please follow these steps:

1. Fork the repository.
2. Create a branch for your feature or bugfix (`git checkout -b my-feature`).
3. Make your changes and add tests if necessary.
4. Commit your changes (`git commit -am 'Add new feature'`).
5. Push to your branch (`git push origin my-feature`).
6. Open a Pull Request describing your changes.

Please follow the project's code style and write tests whenever possible.

## License

This package is open-sourced software licensed under the MIT license.
