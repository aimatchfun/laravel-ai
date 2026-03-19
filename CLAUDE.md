# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Laravel AI** is a Laravel package providing a fluent interface for interacting with multiple AI providers:
- Ollama, OpenAI, Anthropic, Novita, OpenRouter, Together

The package is structured as a service manager with pluggable provider implementations. Users interact through the `AI` facade, which delegates to `AIService`, which manages provider instances.

## Architecture

### Core Components

1. **AIService** (`src/Services/AIService.php`) - The main service manager that:
   - Extends Laravel's `Manager` class
   - Manages provider selection and configuration state
   - Implements fluent interface methods (provider, model, temperature, etc.)
   - Important: Resets state on `provider()` call to prevent singleton state leakage (see commit 26e73ac)

2. **AbstractProvider** (`src/Services/Providers/AbstractProvider.php`) - Base class that:
   - Implements the `AIProvider` contract
   - Provides common state management (model, temperature, messages, responseFormat, streamMode)
   - Includes `getUsageData()` and `getResponseMetadata()` hooks for provider-specific data

3. **Provider Implementations** (`src/Services/Providers/`) - One per API:
   - `OllamaProvider` - Local AI models
   - `OpenAIProvider` - OpenAI API
   - `AnthropicProvider` - Anthropic API
   - `NovitaProvider` - Novita API with advanced parameters
   - `OpenRouterProvider` - OpenRouter API
   - `TogetherProvider` - Together API
   - Each extends `AbstractProvider` and implements `generateResponse()` and `generateStreamResponse()`

4. **AIResponse** (`src/Services/AIResponse.php`) - Response data object with:
   - Universal fields: `answer`, `inputTokens`, `outputTokens`, `raw`
   - Provider-specific optional fields (Ollama timing/metrics, Anthropic cache stats, OpenAI fingerprints, etc.)

5. **AI Facade** (`src/Facades/AI.php`) - Public entry point providing fluent methods

6. **Enums**:
   - `AIProvider` - Available providers with label support
   - `NovitaModel` - Novita models list
   - `TogetherModel` - Together models list

### Key Design Patterns

- **Manager Pattern**: AIService extends Manager for provider resolution
- **Fluent Interface**: All configuration methods return `$this`
- **State Reset**: `AIService::provider()` resets all state to prevent singleton leakage
- **Contract-Based**: Providers implement `AIProvider` contract for flexibility
- **Hook Methods**: `getUsageData()` and `getResponseMetadata()` for provider metadata

## Common Development Tasks

### Running Tests

```bash
# All tests (integration tests with real API calls)
php vendor/bin/phpunit

# Specific provider tests
php vendor/bin/phpunit tests/Integration/OpenAIProviderTest.php

# Single test method
php vendor/bin/phpunit tests/Integration/OpenAIProviderTest.php --filter test_can_generate_response

# Individual provider test scripts (simple PHP, no PHPUnit setup)
php tests/test-openai.php
php tests/test-anthropic.php
php tests/test-ollama.php
php tests/test-novita.php
php tests/test-openrouter.php
php tests/test-together.php
```

**Note**: Tests make real API calls and require `.env` with provider API keys configured. Tests auto-skip if provider not configured.

### Building and Development

```bash
# Build workbench (for testing in Laravel context)
composer build

# Serve the workbench
composer serve

# Clear and prepare skeleton
composer clear
composer prepare
```

### Configuration

- Configuration file: `config/ai.php` (published via `php artisan vendor:publish --provider="AIMatchFun\LaravelAI\Providers\AIServiceProvider" --tag="config"`)
- Environment variables: `AI_PROVIDER`, `{PROVIDER}_API_KEY`, `{PROVIDER}_DEFAULT_MODEL`, `{PROVIDER}_TIMEOUT`

## Code Patterns and Standards

### Adding a New Provider

1. Create `src/Services/Providers/YourProvider.php` extending `AbstractProvider`
2. Implement `generateResponse()` and `generateStreamResponse()`
3. Format messages according to your API's schema
4. Throw exceptions for missing user messages
5. Store response in `$this->lastResponse` for metadata hooks
6. Override `getUsageData()` and `getResponseMetadata()` to return provider-specific data
7. Add to `AIProvider` enum if needed
8. Register in `AIServiceProvider::createManager()`

### Streaming Implementation

- `generateStreamResponse()` returns a `Generator` yielding content chunks
- Most providers support streaming (check API docs)
- `generateResponse()` falls back to consuming stream if `$streamMode` is true (see OpenAIProvider pattern)

### Message Formatting

Messages must be formatted per provider API spec:
- Most providers: `['role' => 'user|assistant|system', 'content' => 'text']`
- Ollama and some providers use `[['role' => 'user', 'content' => '...'], ...]`
- Preview messages are merged with current prompt before API call
- See `openai()` or `anthropic()` for examples

### Metadata and Token Usage

- Store last response in `$this->lastResponse` after successful API call
- Implement `getUsageData()` to return `['input_tokens' => int, 'output_tokens' => int]` if available
- Implement `getResponseMetadata()` to return all other response metadata as associative array
- AIResponse constructor accepts all fields including provider-specific ones

## Known Issues and Gotchas

1. **Singleton State Leakage** - AIService is a singleton. Always call `provider()` to reset state at start of new request chain (handled by provider() method, see commit 26e73ac)

2. **Response Format** - Only Novita supports structured outputs via `responseFormat()`. Field is set but may be ignored by other providers.

3. **Token Usage** - Only Novita, OpenAI, Anthropic, Together, and OpenRouter provide token counts. Ollama returns null for inputTokens/outputTokens.

4. **Streaming + State** - Ensure `$streamMode` is properly reset when switching providers

5. **Preview Messages** - User can pass array format or Message objects; both are supported but messages must follow provider API format

## Recent Changes

- **Latest**: Changed `seed` type from int to `int|float` with default -1 (commit 403389f)
- **Previous**: Fixed provider state reset on every call to prevent singleton leakage (commit 26e73ac)
- **Before**: Fixed singleton responseFormat state issue (commit d13b451)
- **Models**: Added many Novita models including KIMI_K2_5, deepseek-v3.2, qwen3, minimax, glm-4.7, skywork r1v4-lite

## Testing Notes

- Integration tests make **real API calls** - configure API keys in `.env` before running
- Never commit `.env` with real API keys
- Tests are skipped if provider not configured (auto-skip mechanism)
- Test coverage includes: basic generation, system instruction, streaming, temperature, model selection, usage data
- Some tests fail gracefully if unsupported (e.g., structured outputs only on Novita)

## File Structure Summary

```
src/
├── Contracts/AIProvider.php       # Interface for providers
├── Enums/
│   ├── AIProvider.php             # Available providers enum
│   ├── NovitaModel.php           # Novita models enum
│   └── TogetherModel.php         # Together models enum
├── Facades/AI.php                 # Public facade
├── Providers/AIServiceProvider.php # Service registration
└── Services/
    ├── AIService.php              # Manager and fluent interface
    ├── AIResponse.php             # Response data object
    ├── Message.php                # Message helper
    ├── AICreativity.php           # (Legacy - may be deprecated)
    └── Providers/
        ├── AbstractProvider.php    # Base provider class
        ├── OllamaProvider.php
        ├── OpenAIProvider.php
        ├── AnthropicProvider.php
        ├── NovitaProvider.php
        ├── OpenRouterProvider.php
        └── TogetherProvider.php
```
