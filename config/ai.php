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

    /*
    |--------------------------------------------------------------------------
    | Package AI features (point-wise list for pricing/package display)
    |--------------------------------------------------------------------------
    | Shown when a package has has_ai_features = true. Used on pricing page,
    | home page packages section, and vendor/agent package list.
    */
    'package_features' => [
        'generate_description' => 'Generate property/project description with AI',
        'bulk_generate_description' => 'Bulk generate descriptions for multiple listings',
        'analyze_image' => 'Analyze image and suggest description',
        'translate' => 'Translate content with AI',
        'check_compliance' => 'Check description compliance',
        'suggest_price' => 'AI-powered price suggestion',
        'generate_social_copy' => 'Generate social media copy (Facebook, LinkedIn, etc.)',
        'post_to_social' => 'Post to social pages (Facebook, LinkedIn, Instagram, Twitter, TikTok)',
        'smart_campaigns' => 'Smart email/WhatsApp campaigns (e.g. price drop, new listing)',
        'suggest_reply' => 'Suggest reply with AI for inquiries',
        'lead_intent_scoring' => 'Lead intent & scoring on messages',
    ],

];
