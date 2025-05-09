# Laravel AI

A Laravel package that provides a fluent interface for interacting with AI providers like Ollama, OpenAI, and Anthropic.

## Installation

You can install the package via composer:

```bash
composer require daavelar/laravel-ai
```

The package will automatically register itself.

You can publish the configuration file with:

```bash
php artisan vendor:publish --provider="Daavelar\LaravelAI\Providers\AIServiceProvider" --tag="config"
```

This will publish a `config/ai.php` file where you can configure your AI providers.

## Configuration

After publishing the configuration file, you can configure your AI providers in the `config/ai.php` file:

```php
return [
    'default' => env('AI_PROVIDER', 'ollama'),

    'providers' => [
        'ollama' => [
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
            'default_model' => env('OLLAMA_DEFAULT_MODEL', 'llama3'),
        ],
        
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'default_model' => env('OPENAI_DEFAULT_MODEL', 'gpt-4o'),
        ],
        
        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'default_model' => env('ANTHROPIC_DEFAULT_MODEL', 'claude-3-opus-20240229'),
        ],
    ],
];
```

You can also set these values in your `.env` file:

```
AI_PROVIDER=ollama
OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_DEFAULT_MODEL=llama3

OPENAI_API_KEY=your-openai-api-key
OPENAI_DEFAULT_MODEL=gpt-4o

ANTHROPIC_API_KEY=your-anthropic-api-key
ANTHROPIC_DEFAULT_MODEL=claude-3-opus-20240229
```

## Usage

The package provides a fluent interface through the `AI` facade:

```php
use Daavelar\LaravelAI\Facades\AI;

// Basic usage with default provider
$response = AI::withPrompt('What is Laravel?')
    ->run();

// $response é um objeto com:
// $response->conversation_id (int)
// $response->answer (string)

// Specify a provider
$response = AI::provider('ollama')
    ->withPrompt('What is Laravel?')
    ->run();

// Specify a model
$response = AI::provider('ollama')
    ->model('llama3')
    ->withPrompt('What is Laravel?')
    ->run();

// With system instruction
$response = AI::provider('ollama')
    ->model('llama3')
    ->withSystemInstruction('You are a helpful AI assistant.')
    ->withPrompt('What is Laravel?')
    ->run();

// Multiple user messages (for conversation history)
$response = AI::provider('ollama')
    ->model('llama3')
    ->withPrompts([
        ['role' => 'user', 'content' => 'What is Laravel?'],
        ['role' => 'assistant', 'content' => 'Laravel is a PHP web application framework.'],
        ['role' => 'user', 'content' => 'Is it open source?']
    ])
    ->run();

// Adjust creativity level (temperature)
$response = AI::provider('ollama')
    ->model('llama3')
    ->withPrompt('Write a poem about Laravel.')
    ->creativityLevel(AICreativity::HIGH)
    ->run();

// Using conversation history (persisted in the database)
$response = AI::provider('ollama')
    ->model('llama3')
    ->withPrompt('Tell me a joke.')
    ->withConversationHistory('mysql') // Use your Laravel connection name
    ->run();

// The conversation_id will be reused for subsequent calls if you use withConversationHistory
```

### About the AIResponse object

The `run()` method returns an instance of `AIResponse`:

```php
$response = AI::withPrompt('What is Laravel?')->run();

$conversationId = $response->conversation_id; // int
$answer = $response->answer; // string
```

- `conversation_id`: The unique identifier for the conversation. Use this to continue a conversation or fetch its history.
- `answer`: The AI's response to your prompt(s).

### Conversation History

If you use `withConversationHistory('connection_name')`, the conversation will be persisted in the database (table `laravelai_conversation_histories`).
- If you do **not** use `withConversationHistory`, each call to `run()` will start a new conversation (new `conversation_id`).
- If you use `withConversationHistory`, the conversation will be continued and messages will be grouped by `conversation_id`.

You can use the `conversation_id` to fetch or display the full conversation history from the database if needed.

## Extending

You can add your own AI providers by extending the `AIService` class in a service provider:

```php
use Daavelar\LaravelAI\Services\AIService;
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

Your custom provider needs to implement the `Daavelar\LaravelAI\Contracts\AIProvider` interface or extend the `Daavelar\LaravelAI\Services\Providers\AbstractProvider` class.

## License

This package is open-sourced software licensed under the MIT license.