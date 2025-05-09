# Laravel AI

A Laravel package that provides a fluent interface for interacting with AI providers like Ollama, OpenAI, and Anthropic.

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

### Conversation History Configuration

By default, conversation history is **disabled**. If you want to enable conversation history (persisting messages in the database), set the following in your `.env` file:

```
AI_CONVERSATION_HISTORY_ENABLED=true
```

Or directly in `config/ai.php`:

```php
'conversation_history_enabled' => true,
```

If conversation history is disabled, you do **not** need to run the migrations and no data will be stored in the database, even if you use the `withConversationHistory` method.

### Run the migrations

If you want to use conversation history, you need to run the migration to create the necessary table **and** enable the option as described above:

```bash
php artisan migrate
```

This will create the `laravelai_conversation_histories` table in your database.

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
use AIMatchFun\LaravelAI\Facades\AI;

// Basic usage with default provider
$response = AI::withPrompt('What is Laravel?')
    ->run();

// $response is an object with:
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

// Using conversation history (persisted in the database)
$response = AI::provider('ollama')
    ->model('llama3')
    ->withConversationHistory('mysql') // Use your Laravel connection name
    ->withPrompt('What is Laravel?')
    ->run();

// Adjust creativity level (temperature)
$response = AI::provider('ollama')
    ->model('llama3')
    ->withPrompt('Write a poem about Laravel.')
    ->creativityLevel(AICreativity::HIGH)
    ->run();

// Continue a conversation using the returned conversation_id
$response = AI::withPrompt('Hello, who are you?')->run();
$conversationId = $response->conversation_id;

$response = AI::withPrompt('And what can you do?')
    ->withConversationHistory($conversationId)
    ->run();
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

> **Note:** Conversation history will only be persisted if `conversation_history_enabled` is set to `true` in your configuration or `.env` file.

The `withConversationHistory` method uses the database connection specified in your configuration file (`config/ai.php`). For example, if you use `withConversationHistory('mysql')`, the conversation will be persisted using the `mysql` connection defined in your Laravel project.

- If you **do not** use the `withConversationHistory` method, each call to the `run()` method will start a new conversation and a new `conversation_id` will be returned.
- If you use the `withConversationHistory` method, the conversation will be continued and messages will be grouped by the same `conversation_id`.

You can use the returned `conversation_id` to continue the conversation in future calls by passing it to the `withConversationHistory($conversationId)` method.

Example:

```php
// Start a new conversation and get the conversation_id
$response = AI::withPrompt('Hello, who are you?')->run();
$conversationId = $response->conversation_id;

// Continue the conversation using the same conversation_id
$response = AI::withPrompt('And what can you do?')
    ->withConversationHistory($conversationId)
    ->run();
```

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

## License

This package is open-sourced software licensed under the MIT license.