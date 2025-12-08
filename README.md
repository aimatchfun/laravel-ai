# Laravel AI

A Laravel package that provides a fluent interface for interacting with AI providers:

- [Ollama](https://ollama.ai/)
- [OpenAI](https://openai.com/)
- [Anthropic](https://www.anthropic.com/)
- [Novita](https://novita.ai/)
- [OpenRouter](https://openrouter.ai/)
- [Together](https://www.together.ai/)

## Installation

You can install the package via composer:

```bash
composer require aimatchfun/laravel-ai
```

The package will automatically register itself.

### Standalone Usage (Without Laravel)

If you want to test the examples without installing Laravel, see [STANDALONE_EXAMPLES.md](STANDALONE_EXAMPLES.md) for instructions on running examples with minimal dependencies.

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

OPENROUTER_API_KEY=your-api-key
OPENROUTER_DEFAULT_MODEL=openrouter/auto
OPENROUTER_TIMEOUT=30
```

**Timeout:** You can set the timeout (in seconds) for each provider. If a request takes longer than this value, it will fail with a timeout error. The default is 30 seconds for all providers.

## Usage

The package provides a fluent interface through the `AI` facade:

```php
use AIMatchFun\LaravelAI\Facades\AI;
use AIMatchFun\LaravelAI\Enums\AIProvider;
use AIMatchFun\LaravelAI\Services\AICreativity;

// Basic usage with default provider
$response = AI::prompt('What is Laravel?')
    ->run();

// $response is an object with:

// $response->answer (string)

// Specify a provider using string
$response = AI::provider('ollama')
    ->prompt('What is Laravel?')
    ->run();

// Specify a provider using enum (recommended for type safety)
$response = AI::provider(AIProvider::OLLAMA)
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
    ->temperature(AICreativity::HIGH)
    ->run();

// Using response format for structured outputs (JSON schema)
$response = AI::provider('novita')
    ->model('deepseek/deepseek-v3-0324')
    ->responseFormat([
        'type' => 'json_object',
        'schema' => [
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string'],
                'age' => ['type' => 'integer']
            ],
            'required' => ['name', 'age']
        ]
    ])
    ->prompt('Extract name and age from: John is 30 years old.')
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
$inputTokens = $response->inputTokens; // int|null
$outputTokens = $response->outputTokens; // int|null
```

**Standard fields:**
- `answer`: The AI's response to your prompt(s).
- `inputTokens`: Number of input tokens used (available for Novita, OpenAI, Anthropic, Together, and OpenRouter providers).
- `outputTokens`: Number of output tokens used (available for Novita, OpenAI, Anthropic, Together, and OpenRouter providers).

**Ollama-specific fields:**
- `model`: The model used for the response.
- `createdAt`: Timestamp when the response was created.
- `done`: Whether the response is complete.
- `doneReason`: Reason why the response finished.
- `totalDuration`: Total duration in nanoseconds.
- `loadDuration`: Model load duration in nanoseconds.
- `promptEvalCount`: Number of tokens in the prompt.
- `promptEvalDuration`: Time spent evaluating the prompt in nanoseconds.
- `evalCount`: Number of tokens generated.
- `evalDuration`: Time spent generating tokens in nanoseconds.
- `thinking`: The thinking process (if available in the model).

**Anthropic-specific fields:**
- `id`: Unique identifier for the message.
- `type`: Type of the response (usually "message").
- `role`: Role of the message (usually "assistant").
- `stopReason`: Reason why the response stopped (e.g., "end_turn").
- `stopSequence`: Stop sequence that triggered the end (if any).
- `cacheCreationInputTokens`: Number of input tokens used for cache creation.
- `cacheReadInputTokens`: Number of input tokens read from cache.
- `cacheCreation`: Cache creation details with ephemeral token counts.
- `serviceTier`: Service tier used for the request (e.g., "standard").

**OpenAI-specific fields:**
- `id`: Unique identifier for the completion.
- `object`: Type of object (usually "chat.completion").
- `created`: Unix timestamp when the response was created.
- `index`: Index of the choice in the choices array.
- `finishReason`: Reason why the response finished (e.g., "stop").
- `refusal`: Refusal message if the model refused to respond.
- `annotations`: Array of annotations on the response.
- `logprobs`: Log probabilities for the response.
- `totalTokens`: Total number of tokens used (input + output).
- `promptTokensDetails`: Details about prompt tokens (cached_tokens, audio_tokens).
- `completionTokensDetails`: Details about completion tokens (reasoning_tokens, audio_tokens, etc.).
- `systemFingerprint`: System fingerprint for the response.

**Novita-specific fields:**
- `contentFilterResults`: Content filter results including hate, self_harm, sexual, violence, jailbreak, and profanity filters with their filtered/detected status.

**Together-specific fields:**
- `seed`: The seed value used for generation (if provided).
- `toolCalls`: Array of tool calls made by the model (if any).
- `cachedTokens`: Number of cached tokens used in the response.

**Common fields:**
- `raw`: Raw response data from the provider.

**Note:** Token information is only available for providers that return usage data in their API responses. For providers like Ollama, these values will be `null`. Provider-specific fields are only available when using the corresponding provider.

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

### Response Format (Structured Outputs)

The `responseFormat` method allows you to request structured outputs from the AI, such as JSON objects following a specific schema. This is useful when you need the AI to return data in a consistent, parseable format.

**Supported Providers:** Novita only

Example with JSON schema:

```php
// Request a JSON object response
$response = AI::provider('novita')
    ->model('deepseek/deepseek-v3-0324')
    ->responseFormat([
        'type' => 'json_object'
    ])
    ->prompt('Return a JSON object with name and age.')
    ->run();

// Request structured output with schema
$response = AI::provider('novita')
    ->model('deepseek/deepseek-v3-0324')
    ->responseFormat([
        'type' => 'json_object',
        'schema' => [
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string'],
                'age' => ['type' => 'integer'],
                'email' => ['type' => 'string']
            ],
            'required' => ['name', 'age']
        ]
    ])
    ->prompt('Extract name, age, and email from: John Doe is 30 years old and his email is john@example.com')
    ->run();

$data = json_decode($response->answer, true);
// $data will contain: ['name' => 'John Doe', 'age' => 30, 'email' => 'john@example.com']
```

**Note:** Currently, only the Novita provider supports structured outputs via `responseFormat`. Please consult the [Novita documentation on structured outputs](https://novita.ai/docs/guides/llm-structured-outputs) to confirm which models support this feature and for specific schema requirements.

## Available Providers Enum

The package provides an enum with all available AI providers for easy access and type safety:

```php
use AIMatchFun\LaravelAI\Enums\AIProvider;

// Use enum instead of string (recommended for type safety)
$response = AI::provider(AIProvider::OLLAMA)
    ->prompt('What is Laravel?')
    ->run();

// Get all available providers
$allProviders = AIProvider::values();
// Returns: ['ollama', 'openai', 'anthropic', 'novita', 'openrouter', 'together']

// Get providers with labels
$options = AIProvider::options();
// Returns: ['ollama' => 'Ollama', 'openai' => 'OpenAI', ...]

// Check if a provider is valid
if (AIProvider::isValid('ollama')) {
    // Provider is valid
}

// Get provider from string value
$provider = AIProvider::fromValue('ollama');
// Returns: AIProvider::OLLAMA or null if not found

// Get provider from string value or throw exception
$provider = AIProvider::fromValueOrFail('ollama');
// Returns: AIProvider::OLLAMA or throws InvalidArgumentException

// Get label for a provider
$label = AIProvider::OLLAMA->label();
// Returns: 'Ollama'
```

### Advanced Parameters (NovitaProvider)

The `NovitaProvider` supports advanced parameter control methods for fine-tuning AI responses. These methods can be chained together when using the provider directly:

```php
use AIMatchFun\LaravelAI\Services\Providers\NovitaProvider;

$provider = new NovitaProvider(
    config('ai.providers.novita.api_key'),
    config('ai.providers.novita.default_model'),
    config('ai.providers.novita.timeout', 30)
);

$response = $provider
    ->setModel('deepseek/deepseek-v3-0324')
    ->setSystemInstruction('You are a helpful assistant.')
    ->setUserMessages([['role' => 'user', 'content' => 'Write a creative story.']])
    ->temperature(0.7)           // Controls randomness (higher = more creative)
    ->maxTokens(1000)           // Maximum number of tokens to generate
    ->topP(0.9)                 // Nucleus sampling, controls cumulative probability
    ->topK(50)                  // Limits candidate token count
    ->presencePenalty(0.1)       // Controls repeated tokens in the text
    ->frequencyPenalty(0.1)     // Controls token frequency in the text
    ->repetitionPenalty(1.1)     // Penalizes or encourages repetition
    ->generateResponse();
```

**Available Methods:**

- `temperature(float $temperature)` - Controls randomness. Higher values = more creative responses. Range typically 0.0 to 2.0.
- `maxTokens(int $maxTokens)` - Sets the maximum number of tokens the AI can generate in its response.
- `topP(float $topP)` - Nucleus sampling parameter. Controls cumulative probability of token selection. Range typically 0.0 to 1.0.
- `topK(int $topK)` - Limits the number of candidate tokens considered at each step. Lower values make output more focused.
- `presencePenalty(float $presencePenalty)` - Penalizes tokens that have already appeared in the text, encouraging more diverse vocabulary.
- `frequencyPenalty(float $frequencyPenalty)` - Reduces the likelihood of repeating tokens that have appeared frequently in the text.
- `repetitionPenalty(float $repetitionPenalty)` - General repetition control. Values > 1.0 penalize repetition, values < 1.0 encourage it.

**Note:** All parameters are optional. If not specified, the API will use its default values. Parameters are only included in the request payload when explicitly set.

## Available Providers Enum

The package provides an enum with all available AI providers for easy access and type safety:

```php
use AIMatchFun\LaravelAI\Enums\AIProvider;

// Use enum instead of string
$response = AI::provider(AIProvider::OLLAMA)
    ->prompt('What is Laravel?')
    ->run();

// Get all available providers
$allProviders = AIProvider::values();
// Returns: ['ollama', 'openai', 'anthropic', 'novita', 'openrouter', 'together']

// Get providers with labels
$options = AIProvider::options();
// Returns: ['ollama' => 'Ollama', 'openai' => 'OpenAI', ...]

// Check if a provider is valid
if (AIProvider::isValid('ollama')) {
    // Provider is valid
}

// Get provider from string value
$provider = AIProvider::fromValue('ollama');
// Returns: AIProvider::OLLAMA or null if not found

// Get provider from string value or throw exception
$provider = AIProvider::fromValueOrFail('ollama');
// Returns: AIProvider::OLLAMA or throws InvalidArgumentException

// Get label for a provider
$label = AIProvider::OLLAMA->label();
// Returns: 'Ollama'
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

## Testing

The package includes integration tests for all supported AI providers. These tests make real API calls to verify that each provider works correctly.

### Setup

Before running the tests, create a `.env` file in the root directory with the necessary environment variables:

```bash
cp .env.example .env
```

Edit the `.env` file and add your API keys. You don't need to configure all providers - only the ones you want to test.

### Running Tests

Run all integration tests:

```bash
php vendor/bin/phpunit tests/Integration
```

Run tests for a specific provider:

```bash
php vendor/bin/phpunit tests/Integration/OpenAIProviderTest.php
```

Run a specific test:

```bash
php vendor/bin/phpunit tests/Integration/OpenAIProviderTest.php --filter test_can_generate_response
```

### Test Behavior

- **Tests are automatically skipped** if the provider is not configured (environment variable not set)
- **Tests make real API calls** to provider APIs - make sure you have credits available
- **No API keys are hardcoded** - all come from the `.env` file

### Test Coverage

Each provider is tested for:

1. ✅ Basic response generation (`test_can_generate_response`)
2. ✅ Response generation with system instruction (`test_can_generate_response_with_system_instruction`)
3. ✅ Streaming response generation (`test_can_generate_stream_response`)
4. ✅ Temperature configuration (`test_can_set_temperature`)
5. ✅ Model configuration (`test_can_set_model`)
6. ✅ Usage data retrieval (`test_can_get_usage_data`) - when supported
7. ✅ Exception validation when no messages provided (`test_throws_exception_when_no_user_messages`)

### Important Notes

- ⚠️ **Never commit the `.env` file** with your real API keys
- ⚠️ Tests make real API calls and may consume credits
- ⚠️ Some providers may have rate limits - run tests carefully
- ✅ Tests are designed to be idempotent and should not cause side effects

### Simple PHP Test Scripts

Alternatively, you can use simple PHP scripts to test each provider individually:

```bash
# Test OpenAI
php tests/test-openai.php

# Test Anthropic
php tests/test-anthropic.php

# Test Ollama
php tests/test-ollama.php

# Test Novita
php tests/test-novita.php

# Test OpenRouter
php tests/test-openrouter.php

# Test Together
php tests/test-together.php
```

These scripts will:
- Load environment variables from `.env` file
- Test basic response generation
- Test system instructions
- Test streaming responses
- Test usage data (when available)

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
