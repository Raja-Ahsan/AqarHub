<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Assistant Enabled
    |--------------------------------------------------------------------------
    | When true, the AI chat widget is shown on the frontend.
    */
    'enabled' => env('AI_ASSISTANT_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Provider
    |--------------------------------------------------------------------------
    | Supported: "openai"
    */
    'provider' => env('AI_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI
    |--------------------------------------------------------------------------
    */
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'organization' => env('OPENAI_ORGANIZATION'),
        'chat_model' => env('OPENAI_CHAT_MODEL', 'gpt-4o-mini'),
        'vision_model' => env('OPENAI_VISION_MODEL', 'gpt-4o-mini'),
        'max_tokens' => (int) env('OPENAI_MAX_TOKENS', 512),
    ],

    /*
    |--------------------------------------------------------------------------
    | System prompt for the assistant
    |--------------------------------------------------------------------------
    */
    'system_prompt' => env('AI_SYSTEM_PROMPT', 'You are a helpful real estate assistant for AqarHub. You help users find properties, understand pricing, and answer questions about the platform. Be concise and friendly. If you do not know something, say so. Do not make up property listings or prices.'),

    /*
    |--------------------------------------------------------------------------
    | Rate limit (requests per minute per user/IP)
    |--------------------------------------------------------------------------
    */
    'rate_limit' => (int) env('AI_CHAT_RATE_LIMIT', 30),

    /*
    |--------------------------------------------------------------------------
    | Save chat history to database
    |--------------------------------------------------------------------------
    */
    'save_chat_history' => env('AI_SAVE_CHAT_HISTORY', true),

];
