<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAssistantService
{
    protected string $apiKey;

    protected string $model;

    protected int $maxTokens;

    protected string $systemPrompt;

    public function __construct()
    {
        $this->apiKey = (string) (config('ai.openai.api_key') ?? '');
        $this->model = (string) (config('ai.openai.chat_model') ?? 'gpt-4o-mini');
        $this->maxTokens = (int) (config('ai.openai.max_tokens') ?? 512);
        $this->systemPrompt = (string) (config('ai.system_prompt') ?? 'You are a helpful real estate assistant.');
    }

    /**
     * Send a user message and get an AI reply.
     *
     * @param  string  $userMessage
     * @param  array  $conversationHistory  Optional. Array of ['role' => 'user'|'assistant', 'content' => '...']
     * @return array{ success: bool, message?: string, error?: string }
     */
    public function chat(string $userMessage, array $conversationHistory = []): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'AI assistant is not configured.'];
        }

        $userMessage = $this->sanitizeMessage($userMessage);
        if (mb_strlen($userMessage) > 2000) {
            return ['success' => false, 'error' => 'Message is too long.'];
        }

        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt],
        ];

        foreach (array_slice($conversationHistory, -10) as $msg) {
            $role = $msg['role'] ?? 'user';
            $content = $msg['content'] ?? '';
            if ($role === 'user' || $role === 'assistant') {
                $messages[] = ['role' => $role, 'content' => $this->sanitizeMessage((string) $content)];
            }
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => $messages,
                    'max_tokens' => $this->maxTokens,
                    'temperature' => 0.7,
                ]);

            if (! $response->successful()) {
                Log::warning('AI Assistant API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [
                    'success' => false,
                    'error' => 'Sorry, the assistant is temporarily unavailable. Please try again.',
                ];
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';

            return ['success' => true, 'message' => trim($content)];
        } catch (\Throwable $e) {
            Log::error('AI Assistant exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Sorry, something went wrong. Please try again later.',
            ];
        }
    }

    protected function sanitizeMessage(string $text): string
    {
        return strip_tags(preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $text));
    }

    /**
     * Check if the AI assistant is properly configured and enabled.
     */
    public function isAvailable(): bool
    {
        return config('ai.enabled', false)
            && ! empty($this->apiKey);
    }

    /**
     * Parse natural language into property search filters.
     * When conversationHistory is provided, uses it to resolve follow-ups (e.g. "cheaper ones", "same but 3 bed").
     * Returns array with keys: beds, baths, min_price, max_price, city, state, country, type, purpose.
     *
     * @param  array<int, array{role: string, content: string}>  $conversationHistory  Optional. Previous user/assistant messages for context.
     * @return array{ success: bool, filters?: array, error?: string }
     */
    public function parseSearchQuery(string $query, array $conversationHistory = []): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'AI is not configured.'];
        }

        $query = $this->sanitizeMessage($query);
        if (mb_strlen($query) > 500) {
            return ['success' => false, 'error' => 'Query too long.'];
        }

        $systemPrompt = 'You are a real estate search parser. Extract property search criteria for the CURRENT user request. '
            . 'Reply with ONLY a JSON object (no markdown, no code block) with these keys when relevant: beds (number), baths (number), min_price (number), max_price (number), city (string), state (string), country (string), type (residential or commercial or empty), purpose (sale or rent or empty). '
            . 'Use empty string or omit keys when not mentioned or unknown. '
            . 'If the user refers to a previous search (e.g. "cheaper ones", "lower budget", "same but 3 bedroom", "in Madrid instead", "what about 2 bed?"), use the conversation history to infer the previous criteria and apply the new change to output the complete filters for the current search. '
            . 'Example: {"beds":2,"max_price":350000,"city":"Dubai"}';

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        $recentHistory = array_slice($conversationHistory, -8);
        if (! empty($recentHistory)) {
            foreach ($recentHistory as $msg) {
                $role = $msg['role'] ?? 'user';
                $content = $msg['content'] ?? '';
                if (($role === 'user' || $role === 'assistant') && $content !== '') {
                    $messages[] = ['role' => $role, 'content' => $this->sanitizeMessage((string) $content)];
                }
            }
        }
        if (empty($messages) || ($messages[count($messages) - 1]['role'] ?? '') !== 'user' || ($messages[count($messages) - 1]['content'] ?? '') !== $query) {
            $messages[] = ['role' => 'user', 'content' => $query];
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(15)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => $messages,
                    'max_tokens' => 200,
                    'temperature' => 0.3,
                ]);

            if (! $response->successful()) {
                return ['success' => false, 'error' => 'Search parsing failed.'];
            }

            $content = $response->json('choices.0.message.content', '');
            $content = trim(preg_replace('/^```\w*\s*|\s*```$/m', '', $content));
            $filters = json_decode($content, true);
            if (! is_array($filters)) {
                return ['success' => false, 'error' => 'Could not parse search filters.'];
            }

            return ['success' => true, 'filters' => $filters];
        } catch (\Throwable $e) {
            Log::error('AI parseSearchQuery: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Search parsing failed.'];
        }
    }

    /**
     * Generate a human-friendly property description from title, location, and optional form context.
     *
     * @param  array<string, string>  $context  Optional: purpose, category, country, state, city, amenities, price, video_url, beds, bath, area, type
     * @return array{ success: bool, description?: string, meta_keywords?: string, meta_description?: string, error?: string }
     */
    public function generatePropertyDescription(string $title, string $location = '', string $features = '', array $context = []): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'AI is not configured.'];
        }

        $title = $this->sanitizeMessage($title);
        $location = $this->sanitizeMessage($location);
        $features = $this->sanitizeMessage($features);
        if (mb_strlen($title) > 500 || mb_strlen($location) > 300 || mb_strlen($features) > 1000) {
            return ['success' => false, 'error' => 'Input too long.'];
        }

        $lines = ["Title: {$title}", 'Location: ' . ($location ?: 'Not specified')];
        if ($features !== '') {
            $lines[] = 'Additional features / notes: ' . $features;
        }
        foreach ($context as $key => $value) {
            if ($value !== null && $value !== '') {
                $label = str_replace('_', ' ', ucfirst($key));
                $lines[] = "{$label}: " . (is_string($value) ? $this->sanitizeMessage($value) : $value);
            }
        }
        $detailsBlock = implode("\n", $lines);

        $userPrompt = "Use the following property details to write a detailed, human-friendly listing description in HTML. Structure it for a potential buyer or tenant with clear sections and formatting.\n\nProperty details:\n{$detailsBlock}\n\nReply in this exact format (include the section headers):\n\nDESCRIPTION:\n[Output valid HTML only. Use this structure:\n- Start with a short introductory <p> paragraph (2-3 sentences) summarizing the property.\n- Add sub-headings with <h3> or <h4> for sections like \"Location\", \"Features & Amenities\", \"Property Details\", \"Why This Property\".\n- Use <strong> for important terms and <em> for emphasis where appropriate.\n- Use <ul><li>...</li></ul> to list key features, amenities, or highlights (each item in <li>).\n- Use <p> for short paragraphs under sub-headings.\n- Be detailed and inviting. Include all relevant details from the property info above. No markdown, only HTML.]\n\nMETA KEYWORDS:\n[5-8 comma-separated SEO keywords]\n\nMETA DESCRIPTION:\n[1-2 sentences, under 160 characters, for search results]";

        $maxTokensForDescription = max(1024, $this->maxTokens);

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(35)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You write real estate listings. For the DESCRIPTION section output only valid HTML: use <p>, <h3>, <h4>, <ul>, <li>, <strong>, <em>. No markdown. Output the three sections (DESCRIPTION, META KEYWORDS, META DESCRIPTION) with those exact headers.'],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'max_tokens' => $maxTokensForDescription,
                    'temperature' => 0.5,
                ]);

            if (! $response->successful()) {
                return ['success' => false, 'error' => 'Description generation failed.'];
            }

            $raw = trim($response->json('choices.0.message.content', ''));
            $description = '';
            $metaKeywords = '';
            $metaDescription = '';

            if (preg_match('/META KEYWORDS:\s*(.+?)(?=\n\nMETA DESCRIPTION:|\n*$)/is', $raw, $m)) {
                $metaKeywords = trim(preg_replace('/\s+/', ' ', $m[1]));
            }
            if (preg_match('/META DESCRIPTION:\s*([\s\S]+)/', $raw, $m)) {
                $metaDescription = trim(preg_replace('/\s+/', ' ', $m[1]));
            }
            if (preg_match('/DESCRIPTION:\s*(.+?)(?=\n\nMETA KEYWORDS:|\n*$)/is', $raw, $m)) {
                $description = trim($m[1]);
            }
            if ($description === '' && $raw !== '') {
                $description = $raw;
            }

            return [
                'success' => true,
                'description' => $description,
                'meta_keywords' => $metaKeywords,
                'meta_description' => $metaDescription,
            ];
        } catch (\Throwable $e) {
            Log::error('AI generatePropertyDescription: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Description generation failed.'];
        }
    }

    /**
     * Classify user message intent for lead scoring (9.2).
     * Returns one of: ready_to_buy, interested, browsing, question, other.
     *
     * @return array{ success: bool, intent?: string, error?: string }
     */
    public function classifyIntent(string $message): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'AI is not configured.'];
        }

        $message = $this->sanitizeMessage($message);
        if (mb_strlen($message) > 1000) {
            return ['success' => false, 'error' => 'Message too long.'];
        }

        $systemPrompt = 'You are a real estate lead classifier. Classify the user message intent. Reply with ONLY one word, nothing else: ready_to_buy, interested, browsing, question, or other.';

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(10)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $message],
                    ],
                    'max_tokens' => 20,
                    'temperature' => 0.2,
                ]);

            if (! $response->successful()) {
                return ['success' => false, 'error' => 'Classification failed.'];
            }

            $intent = trim(strtolower($response->json('choices.0.message.content', 'other')));
            $allowed = ['ready_to_buy', 'interested', 'browsing', 'question', 'other'];
            if (! in_array($intent, $allowed)) {
                $intent = 'other';
            }

            return ['success' => true, 'intent' => $intent];
        } catch (\Throwable $e) {
            Log::error('AI classifyIntent: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Classification failed.'];
        }
    }

    /**
     * Translate text to target language (9.5).
     *
     * @return array{ success: bool, translation?: string, error?: string }
     */
    public function translate(string $text, string $targetLanguage): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'AI is not configured.'];
        }

        $text = $this->sanitizeMessage($text);
        $targetLanguage = trim($targetLanguage);
        if (mb_strlen($text) > 8000 || mb_strlen($targetLanguage) > 50) {
            return ['success' => false, 'error' => 'Input too long.'];
        }

        $userPrompt = "Translate the following real estate listing text to " . $targetLanguage . ". Keep tone professional and do not add or remove content. Reply with only the translation.\n\n" . $text;

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a professional translator for real estate content. Output only the translated text.'],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'max_tokens' => $this->maxTokens,
                    'temperature' => 0.3,
                ]);

            if (! $response->successful()) {
                return ['success' => false, 'error' => 'Translation failed.'];
            }

            $translation = trim($response->json('choices.0.message.content', ''));
            return ['success' => true, 'translation' => $translation];
        } catch (\Throwable $e) {
            Log::error('AI translate: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Translation failed.'];
        }
    }

    /**
     * Analyze property image with vision API (9.4). Returns tags and optional one-sentence description.
     * $imageData: base64-encoded image string, or full data URL (data:image/...;base64,...), or public URL.
     *
     * @return array{ success: bool, tags?: string[], description?: string, error?: string }
     */
    public function analyzePropertyImage(string $imageData): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'AI is not configured.'];
        }

        $visionModel = config('ai.openai.vision_model', 'gpt-4o-mini');
        $imageUrl = $imageData;
        if (str_starts_with($imageData, 'data:')) {
            // already data URL
        } elseif (! str_starts_with($imageData, 'http')) {
            $imageUrl = 'data:image/jpeg;base64,' . $imageData;
        }

        $userContent = [
            [
                'type' => 'text',
                'text' => 'Look at this real estate property image. Reply with a JSON object only (no markdown): {"tags": ["tag1", "tag2", "tag3", "tag4", "tag5"], "description": "One concise professional sentence describing the property or scene."}. Use exactly 5 short tags (single words or two words). Description should be one sentence, SEO-friendly.',
            ],
            [
                'type' => 'image_url',
                'image_url' => ['url' => $imageUrl],
            ],
        ];

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(25)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $visionModel,
                    'messages' => [
                        ['role' => 'user', 'content' => $userContent],
                    ],
                    'max_tokens' => 300,
                    'temperature' => 0.4,
                ]);

            if (! $response->successful()) {
                Log::warning('AI analyzePropertyImage API error', ['body' => $response->body()]);
                return ['success' => false, 'error' => 'Image analysis failed.'];
            }

            $content = trim($response->json('choices.0.message.content', ''));
            $content = preg_replace('/^```\w*\s*|\s*```$/m', '', $content);
            $data = json_decode($content, true);
            if (! is_array($data)) {
                return ['success' => false, 'error' => 'Could not parse analysis.'];
            }

            $tags = $data['tags'] ?? [];
            $description = $data['description'] ?? '';
            if (! is_array($tags)) {
                $tags = [];
            }

            return ['success' => true, 'tags' => $tags, 'description' => $description];
        } catch (\Throwable $e) {
            Log::error('AI analyzePropertyImage: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Image analysis failed.'];
        }
    }
}
