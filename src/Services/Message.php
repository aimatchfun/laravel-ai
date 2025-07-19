<?php

namespace AIMatchFun\LaravelAI\Services;

use InvalidArgumentException;

class Message
{
    /**
     * @var string
     */
    public readonly string $role;

    /**
     * @var string
     */
    public readonly string $content;

    /**
     * Create a new message instance.
     *
     * @param string $role
     * @param string $content
     * @throws InvalidArgumentException
     */
    public function __construct(string $role, string $content)
    {
        $this->validateRole($role);
        $this->validateContent($content);
        
        $this->role = $role;
        $this->content = $content;
    }

    /**
     * Validate the role.
     *
     * @param string $role
     * @throws InvalidArgumentException
     */
    private function validateRole(string $role): void
    {
        $validRoles = ['system', 'user', 'assistant'];
        
        if (!in_array($role, $validRoles)) {
            throw new InvalidArgumentException(
                "Invalid role '{$role}'. Valid roles are: " . implode(', ', $validRoles)
            );
        }
    }

    /**
     * Validate the content.
     *
     * @param string $content
     * @throws InvalidArgumentException
     */
    private function validateContent(string $content): void
    {
        if (empty(trim($content))) {
            throw new InvalidArgumentException('Message content cannot be empty.');
        }
    }

    /**
     * Convert the message to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'role' => $this->role,
            'content' => $this->content,
        ];
    }

    /**
     * Create a system message.
     *
     * @param string $content
     * @return static
     */
    public static function system(string $content): static
    {
        return new static('system', $content);
    }

    /**
     * Create a user message.
     *
     * @param string $content
     * @return static
     */
    public static function user(string $content): static
    {
        return new static('user', $content);
    }

    /**
     * Create an assistant message.
     *
     * @param string $content
     * @return static
     */
    public static function assistant(string $content): static
    {
        return new static('assistant', $content);
    }

    /**
     * Create messages from an array.
     *
     * @param array $messages
     * @return array
     * @throws InvalidArgumentException
     */
    public static function fromArray(array $messages): array
    {
        $result = [];
        
        foreach ($messages as $message) {
            if (is_array($message)) {
                if (!isset($message['role']) || !isset($message['content'])) {
                    throw new InvalidArgumentException(
                        'Each message must have "role" and "content" keys.'
                    );
                }
                $result[] = new static($message['role'], $message['content']);
            } elseif ($message instanceof Message) {
                $result[] = $message;
            } else {
                throw new InvalidArgumentException(
                    'Each message must be an array with "role" and "content" keys or a Message instance.'
                );
            }
        }
        
        return $result;
    }

    /**
     * Convert an array of Message objects to array format.
     *
     * @param array $messages
     * @return array
     */
    public static function toArrayFormat(array $messages): array
    {
        return array_map(fn(Message $message) => $message->toArray(), $messages);
    }
} 