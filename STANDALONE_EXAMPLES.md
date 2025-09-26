# Executando Exemplos sem Laravel

Este documento explica como executar os exemplos do Laravel AI sem precisar instalar o framework Laravel completo.

## Opções Disponíveis

### 1. ✅ Versão Recomendada: cURL Puro (`simple_answer_curl.php`)

Esta é a abordagem mais simples e não requer nenhuma dependência do Laravel.

```bash
php examples/ollama/simple_answer_curl.php
```

**Vantagens:**
- ✅ Não requer Laravel
- ✅ Não requer instalação de pacotes adicionais
- ✅ Funciona com PHP puro + cURL
- ✅ Código simples e direto
- ✅ Fácil de modificar

**Pré-requisitos:**
- PHP com extensão cURL habilitada
- Ollama rodando localmente

### 2. Versão com Facades (Não Funcional)

Os exemplos originais usam `AI::` facades do Laravel, que requerem o container IoC completo do Laravel. Estes não funcionam sem uma instalação completa do Laravel.

### 3. Versão com ServiceProvider (Não Testada)

Criamos um `bootstrap_standalone.php` que tenta inicializar componentes mínimos do Laravel, mas requer dependências adicionais que não estão incluídas no `composer.json` atual.

## Como Usar

1. **Certifique-se de que o Ollama está rodando:**
   ```bash
   # Verificar se Ollama está respondendo
   curl http://localhost:11434/api/tags

   # Se não estiver rodando, inicie o Ollama
   ollama serve
   ```

2. **Verifique se o modelo está disponível:**
   ```bash
   # Listar modelos disponíveis
   curl http://localhost:11434/api/tags

   # Baixar um modelo (se necessário)
   ollama pull tinyllama
   ```

3. **Execute o exemplo:**
   ```bash
   cd /path/to/laravel-ai
   php examples/ollama/simple_answer_curl.php
   ```

## Configuração

No arquivo `simple_answer_curl.php`, você pode modificar:

```php
$ollamaUrl = 'http://localhost:11434/api/chat';  // URL do Ollama
$model = 'tinyllama';                            // Modelo a usar
$messages = [...];                               // Mensagens de contexto
```

## Outros Provedores

Para usar outros provedores de IA (OpenAI, Anthropic, etc.), você precisará:

1. Instalar as dependências necessárias (Guzzle, etc.)
2. Modificar o código para fazer as chamadas HTTP apropriadas
3. Incluir suas chaves de API

## Estrutura dos Arquivos

```
examples/
├── ollama/
│   ├── simple_answer.php          # Original (requer Laravel)
│   ├── simple_answer_curl.php     # ✅ Recomendado
│   └── ...
```

## Solução de Problemas

### "cURL Error: Failed to connect"
- Verifique se o Ollama está rodando: `curl http://localhost:11434/api/tags`
- Certifique-se de que a porta 11434 não está bloqueada

### "model not found"
- Baixe o modelo: `ollama pull tinyllama`
- Ou mude para um modelo disponível: `ollama pull llama3`

### "PHP Fatal error: Call to undefined function curl_init"
- Instale/enable a extensão cURL do PHP
- No Ubuntu/Debian: `sudo apt-get install php-curl`
- No macOS: `brew install php` (ou habilite no php.ini)
