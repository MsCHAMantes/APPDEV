<?php

return [
    'host' => getenv('OLLAMA_HOST') ?: '127.0.0.1',
    'port' => getenv('OLLAMA_PORT') ?: '11434',
    'model' => getenv('OLLAMA_MODEL') ?: 'naruto',
    
    'system_prompt' => 'You are a helpful shopping assistant for an e-commerce store. Be concise, suggest relevant products, and never invent prices.',
];


