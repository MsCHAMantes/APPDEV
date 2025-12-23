<?php
// Ollama connection settings
// Customize via environment variables if desired.

return [
    'host' => getenv('OLLAMA_HOST') ?: '127.0.0.1',
    'port' => getenv('OLLAMA_PORT') ?: '11434',
    'model' => getenv('OLLAMA_MODEL') ?: 'naruto',
    // Optional system prompt to steer behavior
    'system_prompt' => 'You are a helpful shopping assistant for an e-commerce store. Be concise, suggest relevant products, and never invent prices.',
];


