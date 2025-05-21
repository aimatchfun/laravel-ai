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

If conversation history is disabled, you do **not** need to run the migrations and no data will be stored in the database, even if you use the `conversationHistory` method.

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
// $response->conversation_id (int)
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

// Using conversation history (persisted in the database)
$response = AI::provider('ollama')
    ->model('llama3')
    ->conversationHistory('mysql') // Use your Laravel connection name
    ->prompt('What is Laravel?')
    ->run();

// Adjust creativity level (temperature)
$response = AI::provider('ollama')
    ->model('llama3')
    ->prompt('Write a poem about Laravel.')
    ->creativityLevel(AICreativity::HIGH)
    ->run();

// Continue a conversation using the returned conversation_id
$response = AI::prompt('Hello, who are you?')->run();
$conversationId = $response->conversation_id;

$response = AI::prompt('And what can you do?')
    ->conversationHistory($conversationId)
    ->run();
```

### About the AIResponse object

The `run()` method returns an instance of `AIResponse`:

```php
$response = AI::prompt('What is Laravel?')->run();

$conversationId = $response->conversation_id; // int
$answer = $response->answer; // string
```

- `conversation_id`: The unique identifier for the conversation. Use this to continue a conversation or fetch its history.
- `answer`: The AI's response to your prompt(s).

### Conversation History

> **Note:** Conversation history will only be persisted if `conversation_history_enabled` is set to `true` in your configuration or `.env` file.

The `conversationHistory` method uses the database connection specified in your configuration file (`config/ai.php`). For example, if you use `conversationHistory('mysql')`, the conversation will be persisted using the `mysql` connection defined in your Laravel project.

- If you **do not** use the `conversationHistory` method, each call to the `run()` method will start a new conversation and a new `conversation_id` will be returned.
- If you use the `conversationHistory` method, the conversation will be continued and messages will be grouped by the same `conversation_id`.

You can use the returned `conversation_id` to continue the conversation in future calls by passing it to the `conversationHistory($conversationId)` method.

Example:

```php
// Start a new conversation and get the conversation_id
$response = AI::prompt('Hello, who are you?')->run();
$conversationId = $response->conversation_id;

// Continue the conversation using the same conversation_id
$response = AI::prompt('And what can you do?')
    ->conversationHistory($conversationId)
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
