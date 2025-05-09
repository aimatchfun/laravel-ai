<?php

namespace AIMatchFun\LaravelAI\Services;

use Illuminate\Support\Manager;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use AIMatchFun\LaravelAI\Services\AICreativity;

class AIService extends Manager
{
    /**
    * @var string|null
    */
    protected $selectedProvider = null;
    
    /**
    * @var string|null
    */
    protected $selectedModel = null;
    
    /**
    * @var string|null
    */
    protected $systemInstruction = null;
    
    /**
    * @var array
    */
    protected $userMessages = [];
    
    /**
    * @var float
    */
    protected $creativity = 1.0;
    
    /**
    * @var string|null
    */
    protected $conversationHistoryConnection = null;
    
    /**
    * @var string|null
    */
    protected $conversationId = null;
    
    /**
    * Get the default AI provider name.
    *
    * @return string
    */
    public function getDefaultDriver()
    {
        return $this->config->get('ai.default_provider', 'ollama');
    }
    
    /**
    * Set the AI provider to use.
    *
    * @param string $provider
    * @return $this
    */
    public function provider(string $provider)
    {
        $this->selectedProvider = $provider;
        return $this;
    }
    
    /**
    * Set the model to use.
    *
    * @param string $model
    * @return $this
    */
    public function model(string $model)
    {
        $this->selectedModel = $model;
        return $this;
    }
    
    /**
    * Set the system instruction.
    *
    * @param string $instruction
    * @return $this
    */
    public function withSystemInstruction(string $instruction)
    {
        $this->systemInstruction = $instruction;
        return $this;
    }
    
    /**
    * Set the creativity level.
    *
    * @param float|AICreativity $level
    * @return $this
    */
    public function creativityLevel(float|AICreativity $level)
    {
        if ($level instanceof AICreativity) {
            $level = $level->value;
        }
        $this->creativity = $level;
        return $this;
    }
    
    /**
    * Get the answer from the AI.
    *
    * @return string
    */
    public function answer()
    {
        $provider = $this->driver($this->selectedProvider ?: $this->getDefaultDriver());
        
        if ($this->selectedModel) {
            $provider->setModel($this->selectedModel);
        }
        
        if ($this->systemInstruction) {
            $provider->setSystemInstruction($this->systemInstruction);
        }
        
        if (!empty($this->userMessages)) {
            $provider->setUserMessages($this->userMessages);
        }
        
        $provider->setCreativityLevel($this->creativity);
        
        $response = $provider->generateResponse();
        
        $historyEnabled = $this->config->get('ai.conversation_history.enabled');
        if (!$historyEnabled) {
            return $response;
        }
        
        if (!$this->conversationHistoryConnection) {
            throw new InvalidConversationHistoryException('Conversation history connection not set');
        }
        
        foreach ($this->userMessages as $msg) {
            $this->persistMessageToHistory($msg['role'], $msg['content']);
        }
        $this->persistMessageToHistory('assistant', $response);
        
        return $response;
    }
    
    /**
    * Create an instance of the specified driver.
    *
    * @param string $driver
    * @return \AIMatchFun\LaravelAI\Contracts\AIProvider
    *
    * @throws \InvalidArgumentException
    */
    protected function createDriver($driver)
    {
        $method = 'create'.ucfirst($driver).'Driver';
        
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        
        throw new InvalidArgumentException("Driver [{$driver}] not supported.");
    }
    
    /**
    * Create the Ollama driver.
    *
    * @return \AIMatchFun\LaravelAI\Contracts\AIProvider
    */
    protected function createOllamaDriver()
    {
        $config = $this->config->get('ai.providers.ollama', []);
        
        return new Providers\OllamaProvider(
            $config['base_url'] ?? 'http://localhost:11434',
            $config['default_model'] ?? 'llama3',
            $config['timeout'] ?? 30
        );
    }
    
    /**
    * Create the OpenAI driver.
    *
    * @return \AIMatchFun\LaravelAI\Contracts\AIProvider
    */
    protected function createOpenaiDriver()
    {
        $config = $this->config->get('ai.providers.openai', []);
        
        return new Providers\OpenAIProvider(
            $config['api_key'] ?? '',
            $config['default_model'] ?? 'gpt-4o',
            $config['timeout'] ?? 30
        );
    }
    
    /**
    * Create the Anthropic driver.
    *
    * @return \AIMatchFun\LaravelAI\Contracts\AIProvider
    */
    protected function createAnthropicDriver()
    {
        $config = $this->config->get('ai.providers.anthropic', []);
        
        return new Providers\AnthropicProvider(
            $config['api_key'] ?? '',
            $config['default_model'] ?? 'claude-3-opus-20240229',
            $config['timeout'] ?? 30
        );
    }
    
    /**
    * Define a mensagem do usuário (prompt principal).
    *
    * @param string $prompt
    * @return $this
    */
    public function withPrompt(string $prompt)
    {
        $this->userMessages = [['role' => 'user', 'content' => $prompt]];
        return $this;
    }
    
    /**
    * Alias for answer to match README usage.
    *
    * @return AIResponse
    */
    public function run() : AIResponse
    {
        $response = $this->answer();
        return new AIResponse((string)$this->conversationId, $response);
    }
    
    /**
    * Habilita o uso de histórico de conversa, persistindo e buscando mensagens do banco.
    *
    * @param string|null $connection Nome da conexão do Laravel a ser usada para persistência.
    * @return $this
    */
    public function withConversationHistory(string $conversationId)
    {
        if ($this->config->get('ai.conversation_history.enabled') === false) {
            return $this;
        }
        
        $history = DB::connection(config('ai.conversation_history.connection'))
        ->table('laravelai_conversation_histories')
        ->where('conversation_id', $conversationId)
        ->orderBy('created_at')
        ->get();
        
        $this->userMessages = $history->map(function ($row) {
            return [
                'role' => $row->role,
                'content' => $row->content,
            ];
        })->toArray();
        
        return $this;
    }
    
    /**
    * Persiste a mensagem no histórico, se a conexão estiver definida.
    *
    * @param string $role
    * @param string $content
    * @return void
    */
    protected function persistMessageToHistory(string $role, string $content)
    {
        if ($this->config->get('ai.conversation_history.enabled') === false) {
            return;
        }
        
        return DB::connection(config('ai.conversation_history.connection'))
        ->table('laravelai_conversation_histories')
        ->insert([
            'conversation_id' => $this->conversationId,
            'provider' => $this->selectedProvider ?: $this->getDefaultDriver(),
            'model' => $this->selectedModel,
            'role' => $role,
            'content' => $content,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    /**
    * Define o conversation_id manualmente para persistência de histórico.
    *
    * @param string $id
    * @return $this
    */
    public function setConversationId(string $id)
    {
        $this->conversationId = $id;
        return $this;
    }
}

