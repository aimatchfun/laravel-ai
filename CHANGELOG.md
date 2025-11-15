# Changelog

## [Unreleased]

## [2.0.0] - 2024-12-XX

### Added
- Métodos avançados de controle de parâmetros no `NovitaProvider`:
  - `maxTokens(int $maxTokens)` - Controla o número máximo de tokens gerados
  - `temperature(float $temperature)` - Controla aleatoriedade (maior = mais criativo)
  - `topP(float $topP)` - Nucleus sampling, controla probabilidade cumulativa
  - `topK(int $topK)` - Limita a contagem de tokens candidatos
  - `presencePenalty(float $presencePenalty)` - Controla tokens repetidos no texto
  - `frequencyPenalty(float $frequencyPenalty)` - Controla frequência de tokens no texto
  - `repetitionPenalty(float $repetitionPenalty)` - Penaliza ou encoraja repetição

### Changed
- **BREAKING CHANGE**: Refatorado `creativityLevel` para `temperature` em toda a codebase
  - Propriedade `$creativityLevel` renomeada para `$temperature` no `AbstractProvider`
  - Método `setCreativityLevel()` renomeado para `setTemperature()` no `AbstractProvider` e interface `AIProvider`
  - Método `creativityLevel()` renomeado para `temperature()` no `AIService`
  - Propriedade `$creativity` renomeada para `$temperature` no `AIService`
  - Todos os providers atualizados para usar `$this->temperature` ao invés de `$this->creativityLevel`
- Removido prefixo "set" dos métodos de parâmetros no `NovitaProvider`:
  - `setMaxTokens()` → `maxTokens()`
  - `setTemperature()` → `temperature()`
  - `setTopP()` → `topP()`
  - `setTopK()` → `topK()`
  - `setPresencePenalty()` → `presencePenalty()`
  - `setFrequencyPenalty()` → `frequencyPenalty()`
  - `setRepetitionPenalty()` → `repetitionPenalty()`
- Parâmetros no `NovitaProvider` agora são opcionais e só são incluídos no payload quando definidos
- Documentação atualizada com novos nomes de métodos e exemplos

### Migration Guide
Para migrar da versão 1.x para 2.0.0:
- Substitua todas as chamadas de `creativityLevel()` por `temperature()`
- Atualize chamadas de métodos do `NovitaProvider` removendo o prefixo "set"
- Exemplo:
  ```php
  // Antes (1.x)
  AI::creativityLevel(AICreativity::HIGH);
  $provider->setMaxTokens(1000);
  
  // Depois (2.0.0)
  AI::temperature(AICreativity::HIGH);
  $provider->maxTokens(1000);
  ```

## [1.14.0] - 2024-12-XX

### Added
- Suporte completo para streaming de respostas em tempo real.
- Método `stream(bool $stream = true)` no `AIService` para ativar modo de streaming.
- Método `streamResponse()` no `AIService` que retorna um `Generator` para processamento de chunks em tempo real.
- Método `setStreamMode(bool $stream)` no contrato `AIProvider` e `AbstractProvider`.
- Método `generateStreamResponse()` no contrato `AIProvider` que retorna um `Generator` com chunks da resposta.
- Implementação de streaming nativo nos provedores: Ollama, OpenAI, Anthropic, Novita, Together e OpenRouter.
- Suporte automático para streaming quando `stream(true)` é chamado antes de `answer()` ou `run()`.

### Changed
- Método `generateResponse()` nos provedores agora detecta automaticamente o modo de streaming e usa streaming quando ativado.
- Provedores agora processam respostas streaming via Server-Sent Events (SSE) quando disponível.

## [1.12.0] - 2024-12-XX

### Added
- Campos `inputTokens` e `outputTokens` no `AIResponse` para rastreamento de uso de tokens.
- Método `getUsageData()` em todos os provedores para extrair dados de uso das respostas da API.
- Suporte para extração de dados de tokens nos provedores Novita, OpenAI, Anthropic, Together e OpenRouter.
- Propriedade `lastResponse` no `AbstractProvider` para armazenar a resposta completa da API.

### Changed
- Método `run()` do `AIService` agora extrai e passa dados de uso de tokens para o `AIResponse`.
- Provedores agora armazenam a resposta completa da API antes de retornar apenas o conteúdo.

## [1.7.0] - 2024-12-19

### Added
- Suporte ao Models Lab provider para integração com a plataforma Models Lab.
- Provider `ModelsLabProvider` com implementação completa da interface `AIProvider`.
- Configurações específicas para o Models Lab no arquivo de configuração.
- Suporte a múltiplos provedores de IA: Ollama, OpenAI, Anthropic, Novita, Models Lab.
- Interface fluente via Facade `AI` para prompts, modelos, instruções de sistema e criatividade.
- Métodos `prompt`, `temperature`, `systemInstruction`, `provider`, `model`, `previewMessages` e `run`.
- Enum `AICreativity` para níveis de criatividade (`LOW`, `MEDIUM`, `HIGH`).
- Classe `Message` para validação e formatação de mensagens com roles válidos (`system`, `user`, `assistant`).
- Método `previewMessages` para fornecer contexto de conversa sem persistência em banco.
- O método `run` retorna um objeto estruturado (`AIResponse`) com `answer`.
- Exemplo de extensão para provedores customizados.
- Enum `NovitaModel` com todos os modelos disponíveis da Novita para type safety e autocompletar.
- Métodos utilitários no enum `NovitaModel`: `getValues()` e `fromValue()`.
- Documentação no README sobre o uso do enum `NovitaModel` com exemplos práticos.

### Changed
- Removido sistema de persistência de histórico de conversas em banco de dados.
- Substituído `conversationHistory` por `previewMessages` para contexto de conversa.
- Removido `conversation_id` do `AIResponse`.
- Interface pública simplificada e alinhada ao novo sistema de preview messages.

### Removed
- Método `conversationHistory` e toda lógica de persistência em banco.
- Configurações de `conversation_history` no arquivo de configuração.
- Migration da tabela `laravelai_conversation_histories` (não mais necessária).
- Métodos `persistMessageToHistory` e `setConversationId`.

## [1.0.2] - 2024-06-08

### Added
- Seção no README explicando como rodar a migration necessária para o histórico de conversas, incluindo instrução de uso do comando `php artisan migrate`.

---

Para detalhes de uso e exemplos, consulte o README.md. 