<?php

/**
 * Example usage of the new previewMessages feature
 * 
 * This example demonstrates how to use the previewMessages method
 * to provide conversation context without database persistence.
 */

use AIMatchFun\LaravelAI\Facades\AI;
use AIMatchFun\LaravelAI\Services\Message;
use AIMatchFun\LaravelAI\Services\AICreativity;

// Example 1: Using array format for preview messages
echo "=== Example 1: Array format ===\n";

$messages = [
    ['role' => 'user', 'content' => 'Hello, what is your name?'],
    ['role' => 'assistant', 'content' => 'My name is Claude, I am an AI assistant.'],
    ['role' => 'user', 'content' => 'What can you help me with?']
];

$response = AI::provider('ollama')
    ->model('llama3')
    ->previewMessages($messages)
    ->prompt('Can you explain Laravel to me?')
    ->run();

echo "Response: " . $response->answer . "\n\n";

// Example 2: Using Message objects for better type safety
echo "=== Example 2: Message objects ===\n";

$messages = [
    Message::user('Hello, what is your name?'),
    Message::assistant('My name is Claude, I am an AI assistant.'),
    Message::user('What can you help me with?')
];

$response = AI::provider('ollama')
    ->model('llama3')
    ->previewMessages($messages)
    ->prompt('Can you explain Laravel to me?')
    ->run();

echo "Response: " . $response->answer . "\n\n";

// Example 3: Continuing a conversation
echo "=== Example 3: Continuing conversation ===\n";

// First interaction
$response1 = AI::provider('ollama')
    ->model('llama3')
    ->prompt('Hello, who are you?')
    ->run();

echo "First response: " . $response1->answer . "\n";

// Continue conversation with context
$conversationMessages = [
    ['role' => 'user', 'content' => 'Hello, who are you?'],
    ['role' => 'assistant', 'content' => $response1->answer]
];

$response2 = AI::provider('ollama')
    ->model('llama3')
    ->previewMessages($conversationMessages)
    ->prompt('What can you help me with?')
    ->run();

echo "Second response: " . $response2->answer . "\n\n";

// Example 4: Using system instruction with preview messages
echo "=== Example 4: System instruction with preview ===\n";

$messages = [
    ['role' => 'user', 'content' => 'What is 2+2?'],
    ['role' => 'assistant', 'content' => '2+2 equals 4.']
];

$response = AI::provider('ollama')
    ->model('llama3')
    ->systemInstruction('You are a helpful math tutor.')
    ->previewMessages($messages)
    ->prompt('What is 3+3?')
    ->run();

echo "Response: " . $response->answer . "\n\n";

// Example 5: Error handling with invalid messages
echo "=== Example 5: Error handling ===\n";

try {
    $invalidMessages = [
        ['role' => 'invalid_role', 'content' => 'This should fail'],
    ];
    
    $response = AI::previewMessages($invalidMessages)
        ->prompt('This should not work')
        ->run();
} catch (InvalidArgumentException $e) {
    echo "Error caught: " . $e->getMessage() . "\n";
}

try {
    $invalidMessages = [
        ['role' => 'user', 'content' => ''], // Empty content
    ];
    
    $response = AI::previewMessages($invalidMessages)
        ->prompt('This should not work')
        ->run();
} catch (InvalidArgumentException $e) {
    echo "Error caught: " . $e->getMessage() . "\n";
} 