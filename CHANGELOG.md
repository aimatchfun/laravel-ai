# Changelog

## [Unreleased]

### Added
- Suporte a múltiplos provedores de IA: Ollama, OpenAI, Anthropic, Novita.
- Interface fluente via Facade `AI` para prompts, modelos, instruções de sistema e criatividade.
- Métodos `prompt`, `creativityLevel`, `systemInstruction`, `provider`, `model`, `previewMessages` e `run`.
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