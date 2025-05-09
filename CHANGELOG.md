# Changelog

## [Unreleased]

### Added
- Suporte a múltiplos provedores de IA: Ollama, OpenAI, Anthropic.
- Interface fluente via Facade `AI` para prompts, modelos, instruções de sistema e criatividade.
- Métodos `withPrompt`, `withPrompts`, `creativityLevel`, `withSystemInstruction`, `provider`, `model` e `run`.
- Enum `AICreativity` para níveis de criatividade (`LOW`, `MEDIUM`, `HIGH`).
- Persistência de histórico de conversas em banco de dados com `withConversationHistory`, usando a tabela `laravelai_conversation_histories`.
- O método `run` retorna um objeto estruturado (`AIResponse`) com `conversation_id` e `answer`.
- Exemplo de extensão para provedores customizados.

### Changed
- Interface pública alinhada ao README, métodos antigos de mensagens de usuário removidos.

### Migration
- Migration para a tabela `laravelai_conversation_histories` com suporte a múltiplas conversas e agrupamento por `conversation_id`.

## [1.0.2] - 2024-06-08

### Added
- Seção no README explicando como rodar a migration necessária para o histórico de conversas, incluindo instrução de uso do comando `php artisan migrate`.

---

Para detalhes de uso e exemplos, consulte o README.md. 