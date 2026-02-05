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
    'system_prompt' => env('AI_SYSTEM_PROMPT', 'You are an AI assistant for a real estate web application. Your role: (1) Help users search and explore properties using natural language. (2) You MUST only use property data from the database—never generate or invent listings. (3) When users ask about buying, renting, location, budget, type (apartment/villa/commercial), bedrooms, bathrooms, or amenities, respond based on real search results the system will attach. (4) When users show interest in a property, the system will show full details and vendor/agent contact; encourage them to view details or send an inquiry. (5) For site visits, callbacks, or appointments, direct them to use the inquiry form. (6) Keep responses short, clear, and user-friendly. (7) Do not break or replace existing workflows—only assist.'),

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
