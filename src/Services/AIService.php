<?php

namespace AIMatchFun\LaravelAI\Services;

use Illuminate\Support\Manager;
use AIMatchFun\LaravelAI\Contracts\AIProvider;
use AIMatchFun\LaravelAI\Services\AICreativity;
use AIMatchFun\LaravelAI\Services\Message;
use InvalidArgumentException;

class AIService extends Manager
{
    /**
    * @var string|null
    */
    protected $provider = null;

    /**
    * @var string|null
    */
    protected $model = null;

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
    * @var array
    */
    protected $previewMessages = [];

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
    public function provider(string|AIProvider $provider)
    {
        $this->provider = $provider;
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
        $this->model = $model;

        return $this;
    }

    /**
    * Set the system instruction.
    *
    * @param string $instruction
    * @return $this
    */
    public function systemInstruction(string $instruction)
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
    public function creativityLevel(AICreativity $level)
    {
        $this->creativity = $level->value * 0.1;

        return $this;
    }

    /**
    * Get the answer from the AI.
    *
    * @return string
    */
    public function answer()
    {
        $provider = $this->driver($this->provider ?: $this->getDefaultDriver());

        if ($this->model) {
            $provider->setModel($this->model);
        }

        if ($this->systemInstruction) {
            $provider->setSystemInstruction($this->systemInstruction);
        }

        if (empty($this->userMessages)) {
            throw new InvalidArgumentException('No user messages provided. Call prompt() before calling run().');
        }

        // Merge preview messages with current user messages
        $allMessages = array_merge($this->previewMessages, $this->userMessages);

        $provider->setUserMessages($allMessages);

        $provider->setCreativityLevel($this->creativity);

        $response = $provider->generateResponse();

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
    * Create the Novita driver.
    *
    * @return \AIMatchFun\LaravelAI\Contracts\AIProvider
    */
    protected function createNovitaDriver()
    {
        $config = $this->config->get('ai.providers.novita', []);

        return new Providers\NovitaProvider(
            $config['api_key'] ?? '',
            $config['default_model'] ?? 'deepseek/deepseek-v3-0324',
            $config['timeout'] ?? 30
        );
    }

    /**
    * Create the ModelsLab driver.
    *
    * @return \AIMatchFun\LaravelAI\Contracts\AIProvider
    */
    protected function createModelsLabDriver()
    {
        $config = $this->config->get('ai.providers.modelslab', []);

        return new Providers\ModelsLabProvider(
            $config['api_key'] ?? '',
            $config['default_model'] ?? 'llama3',
            $config['timeout'] ?? 30
        );
    }

    /**
    * Define a mensagem do usuário (prompt principal).
    *
    * @param string $prompt
    * @return $this
    */
    public function prompt(string $prompt)
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
        return new AIResponse($response);
    }

    /**
    * Define mensagens de preview para o contexto da conversa.
    *
    * @param array $messages Array de mensagens no formato ['role' => 'user', 'content' => 'message'] ou array de objetos Message
    * @return $this
    */
    public function previewMessages(array $messages)
    {
        $messageObjects = Message::fromArray($messages);
        $this->previewMessages = Message::toArrayFormat($messageObjects);

        return $this;
    }


}

