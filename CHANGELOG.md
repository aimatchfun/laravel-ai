# Changelog

## [Unreleased]

## [2.2.3] - 2025-01-27

### Added
- New Novita models: `DEEPSEEK_V3_2`, `QWEN3_VL_235B_A22B_THINKING`, `QWEN3_MAX`, `MINIMAX_M2_1`, `GLM_47`, `SKYWORK_R1V4_LITE`

## [2.2.2] - 2025-01-XX

### Fixed
- Fixed service registration in `AIServiceProvider` to properly inject application instance

## [2.2.1] - 2024-12-XX

### Changed
- **BREAKING CHANGE**: `temperature()` method now accepts `float` (0.1 to 2.0) instead of `AICreativity` enum
  - Old: `->temperature(AICreativity::HIGH)`
  - New: `->temperature(1.5)` (float between 0.1 and 2.0)
- Added validation to ensure temperature values are between 0.1 and 2.0

## [2.2.0] - 2024-12-XX

### Added
- Support for multimodal messages (images) in `AIService` via `previewMessages()` method
- Vision Language Model support for Novita provider
- `image:analyse` command for analyzing images using Novita Vision models
- Support for image URLs and local file paths (automatically converted to base64)
- Image detail parameter (`high`, `low`, `auto`) for vision analysis

### Changed
- `previewMessages()` method now supports multimodal content (arrays) in addition to text messages
- `run()` method now accepts `previewMessages()` without requiring `prompt()` for multimodal use cases
- Improved error handling for multimodal message validation
- Added validation to ensure temperature values are between 0.1 and 2.0

## [2.0.0] - 2024-12-XX

### Added
- Advanced parameter control methods in `NovitaProvider`:
  - `maxTokens(int $maxTokens)` - Controls the maximum number of tokens generated
  - `temperature(float $temperature)` - Controls randomness (higher = more creative)
  - `topP(float $topP)` - Nucleus sampling, controls cumulative probability
  - `topK(int $topK)` - Limits candidate token count
  - `presencePenalty(float $presencePenalty)` - Controls repeated tokens in the text
  - `frequencyPenalty(float $frequencyPenalty)` - Controls token frequency in the text
  - `repetitionPenalty(float $repetitionPenalty)` - Penalizes or encourages repetition

### Changed
- **BREAKING CHANGE**: Refactored `creativityLevel` to `temperature` throughout the codebase
  - Property `$creativityLevel` renamed to `$temperature` in `AbstractProvider`
  - Method `setCreativityLevel()` renamed to `setTemperature()` in `AbstractProvider` and `AIProvider` interface
  - Method `creativityLevel()` renamed to `temperature()` in `AIService`
  - Property `$creativity` renamed to `$temperature` in `AIService`
  - All providers updated to use `$this->temperature` instead of `$this->creativityLevel`
- Removed "set" prefix from parameter methods in `NovitaProvider`:
  - `setMaxTokens()` → `maxTokens()`
  - `setTemperature()` → `temperature()`
  - `setTopP()` → `topP()`
  - `setTopK()` → `topK()`
  - `setPresencePenalty()` → `presencePenalty()`
  - `setFrequencyPenalty()` → `frequencyPenalty()`
  - `setRepetitionPenalty()` → `repetitionPenalty()`
- Parameters in `NovitaProvider` are now optional and only included in the payload when defined
- Documentation updated with new method names and examples

### Migration Guide
To migrate from version 1.x to 2.0.0:
- Replace all `creativityLevel()` calls with `temperature()`
- Update `NovitaProvider` method calls by removing the "set" prefix
- Example:
  ```php
  // Before (1.x)
  AI::creativityLevel(AICreativity::HIGH);
  $provider->setMaxTokens(1000);
  
  // After (2.0.0)
  AI::temperature(AICreativity::HIGH);
  $provider->maxTokens(1000);
  ```

## [1.14.0] - 2024-12-XX

### Added
- Full support for real-time response streaming.
- `stream(bool $stream = true)` method in `AIService` to enable streaming mode.
- `streamResponse()` method in `AIService` that returns a `Generator` for real-time chunk processing.
- `setStreamMode(bool $stream)` method in `AIProvider` contract and `AbstractProvider`.
- `generateStreamResponse()` method in `AIProvider` contract that returns a `Generator` with response chunks.
- Native streaming implementation in providers: Ollama, OpenAI, Anthropic, Novita, Together and OpenRouter.
- Automatic streaming support when `stream(true)` is called before `answer()` or `run()`.

### Changed
- `generateResponse()` method in providers now automatically detects streaming mode and uses streaming when enabled.
- Providers now process streaming responses via Server-Sent Events (SSE) when available.

## [1.12.0] - 2024-12-XX

### Added
- `inputTokens` and `outputTokens` fields in `AIResponse` for token usage tracking.
- `getUsageData()` method in all providers to extract usage data from API responses.
- Support for token data extraction in Novita, OpenAI, Anthropic, Together and OpenRouter providers.
- `lastResponse` property in `AbstractProvider` to store the complete API response.

### Changed
- `run()` method in `AIService` now extracts and passes token usage data to `AIResponse`.
- Providers now store the complete API response before returning only the content.

## [1.7.0] - 2024-12-19

### Added
- Support for multiple AI providers: Ollama, OpenAI, Anthropic, Novita.
- Fluent interface via `AI` Facade for prompts, models, system instructions and creativity.
- Methods `prompt`, `temperature`, `systemInstruction`, `provider`, `model`, `previewMessages` and `run`.
- `AICreativity` enum for creativity levels (`LOW`, `MEDIUM`, `HIGH`).
- `Message` class for validation and formatting of messages with valid roles (`system`, `user`, `assistant`).
- `previewMessages` method to provide conversation context without database persistence.
- The `run` method returns a structured object (`AIResponse`) with `answer`.
- Extension example for custom providers.
- `NovitaModel` enum with all available Novita models for type safety and autocomplete.
- Utility methods in `NovitaModel` enum: `getValues()` and `fromValue()`.
- README documentation on using the `NovitaModel` enum with practical examples.

### Changed
- Removed conversation history persistence system in database.
- Replaced `conversationHistory` with `previewMessages` for conversation context.
- Removed `conversation_id` from `AIResponse`.
- Simplified public interface aligned with the new preview messages system.

### Removed
- `conversationHistory` method and all database persistence logic.
- `conversation_history` configurations in the configuration file.
- Migration for `laravelai_conversation_histories` table (no longer needed).
- `persistMessageToHistory` and `setConversationId` methods.

## [1.0.2] - 2024-06-08

### Added
- Section in README explaining how to run the necessary migration for conversation history, including instructions for using the `php artisan migrate` command.

---

For usage details and examples, see README.md.
