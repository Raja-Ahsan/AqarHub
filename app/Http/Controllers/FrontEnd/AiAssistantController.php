<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use App\Models\AiChatMessage;
use App\Services\AiAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class AiAssistantController extends Controller
{
    protected AiAssistantService $aiService;

    public function __construct(AiAssistantService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Handle chat message from the frontend widget.
     */
    public function chat(Request $request): JsonResponse
    {
        if (! $this->aiService->isAvailable()) {
            return response()->json([
                'success' => false,
                'error' => 'AI assistant is not available.',
            ], 503);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array',
            'history.*.role' => 'string|in:user,assistant',
            'history.*.content' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ], 422);
        }

        $message = $request->input('message');
        $history = $request->input('history', []);

        $result = $this->aiService->chat($message, $history);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Unknown error',
            ], 400);
        }

        $replyMessage = $result['message'];

        // 9.3: If user message looks like a property search, append search link to reply
        if ($this->looksLikePropertySearch($message)) {
            $searchResult = $this->aiService->parseSearchQuery($message);
            if ($searchResult['success'] && ! empty($searchResult['filters'])) {
                $params = [];
                if (! empty($searchResult['filters']['beds'])) {
                    $params['beds'] = $searchResult['filters']['beds'];
                }
                if (! empty($searchResult['filters']['baths'])) {
                    $params['baths'] = $searchResult['filters']['baths'];
                }
                if (! empty($searchResult['filters']['min_price'])) {
                    $params['min'] = $searchResult['filters']['min_price'];
                }
                if (! empty($searchResult['filters']['max_price'])) {
                    $params['max'] = $searchResult['filters']['max_price'];
                }
                if (! empty($searchResult['filters']['city'])) {
                    $params['city'] = $searchResult['filters']['city'];
                }
                if (! empty($searchResult['filters']['state'])) {
                    $params['state'] = $searchResult['filters']['state'];
                }
                if (! empty($searchResult['filters']['country'])) {
                    $params['country'] = $searchResult['filters']['country'];
                }
                if (! empty($searchResult['filters']['type']) && in_array($searchResult['filters']['type'], ['residential', 'commercial'])) {
                    $params['type'] = $searchResult['filters']['type'];
                }
                if (! empty($searchResult['filters']['purpose']) && in_array($searchResult['filters']['purpose'], ['sale', 'rent'])) {
                    $params['purpose'] = $searchResult['filters']['purpose'];
                }
                $searchUrl = count($params) > 0
                    ? route('frontend.properties') . '?' . http_build_query($params)
                    : route('frontend.properties');
                $replyMessage .= "\n\nYou can browse matching properties here: " . $searchUrl;
            }
        }

        if (config('ai.save_chat_history', true) && Schema::hasTable('ai_chat_messages')) {
            $sessionId = $request->session()->getId();
            $userId = Auth::guard('web')->id();
            $intent = null;
            if (Schema::hasColumn('ai_chat_messages', 'intent')) {
                $classify = $this->aiService->classifyIntent($message);
                if ($classify['success'] && ! empty($classify['intent'])) {
                    $intent = $classify['intent'];
                }
            }
            AiChatMessage::create(array_filter([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'role' => 'user',
                'content' => $message,
                'intent' => $intent,
            ]));
            AiChatMessage::create([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'role' => 'assistant',
                'content' => $replyMessage,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $replyMessage,
        ]);
    }

    /**
     * Heuristic: does the message look like a property search request?
     */
    protected function looksLikePropertySearch(string $message): bool
    {
        $lower = mb_strtolower($message);
        $patterns = ['find', 'search', 'looking for', 'show me', 'properties with', 'bed', 'beds', 'bath', 'under', 'above', 'rent', 'sale', 'buy', 'budget', 'max price', 'min price', 'in the city', 'near', 'location'];
        $hits = 0;
        foreach ($patterns as $p) {
            if (str_contains($lower, $p)) {
                $hits++;
            }
        }
        return $hits >= 1 && strlen(trim($message)) >= 10;
    }

    /**
     * Natural language property search: parse query and return properties page URL with filters.
     */
    public function search(Request $request): JsonResponse
    {
        if (! $this->aiService->isAvailable()) {
            return response()->json(['success' => false, 'error' => 'AI assistant is not available.'], 503);
        }

        $query = $request->input('q', $request->input('query', ''));
        if (strlen(trim($query)) < 2) {
            return response()->json(['success' => false, 'error' => 'Please enter a search query.'], 422);
        }

        $result = $this->aiService->parseSearchQuery($query);
        if (! $result['success']) {
            return response()->json(['success' => false, 'error' => $result['error'] ?? 'Could not parse search.'], 400);
        }

        $filters = $result['filters'];
        $params = [];
        if (! empty($filters['beds'])) {
            $params['beds'] = $filters['beds'];
        }
        if (! empty($filters['baths'])) {
            $params['baths'] = $filters['baths'];
        }
        if (! empty($filters['min_price'])) {
            $params['min'] = $filters['min_price'];
        }
        if (! empty($filters['max_price'])) {
            $params['max'] = $filters['max_price'];
        }
        if (! empty($filters['city'])) {
            $params['city'] = $filters['city'];
        }
        if (! empty($filters['state'])) {
            $params['state'] = $filters['state'];
        }
        if (! empty($filters['country'])) {
            $params['country'] = $filters['country'];
        }
        if (! empty($filters['type']) && in_array($filters['type'], ['residential', 'commercial'])) {
            $params['type'] = $filters['type'];
        }
        if (! empty($filters['purpose']) && in_array($filters['purpose'], ['sale', 'rent'])) {
            $params['purpose'] = $filters['purpose'];
        }

        $url = route('frontend.properties') . (count($params) ? '?' . http_build_query($params) : '');

        return response()->json([
            'success' => true,
            'url' => $url,
            'filters' => $filters,
        ]);
    }

    /**
     * Generate property description (for admin/vendor property form).
     * Requires auth:vendor or auth:admin.
     */
    public function generateDescription(Request $request): JsonResponse
    {
        try {
            if (! Auth::guard('vendor')->check() && ! Auth::guard('admin')->check()) {
                return response()->json(['success' => false, 'error' => 'Unauthorized.'], 401);
            }
            if (! $this->aiService->isAvailable()) {
                return response()->json(['success' => false, 'error' => 'AI assistant is not available. Set OPENAI_API_KEY in .env and AI_ASSISTANT_ENABLED=true.'], 503);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:500',
                'location' => 'nullable|string|max:300',
                'features' => 'nullable|string|max:1000',
                'purpose' => 'nullable|string|max:50',
                'category' => 'nullable|string|max:200',
                'country' => 'nullable|string|max:200',
                'state' => 'nullable|string|max:200',
                'city' => 'nullable|string|max:200',
                'amenities' => 'nullable|string|max:800',
                'price' => 'nullable|string|max:50',
                'video_url' => 'nullable|string|max:500',
                'beds' => 'nullable|string|max:20',
                'bath' => 'nullable|string|max:20',
                'area' => 'nullable|string|max:50',
                'type' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
            }

            $context = [];
            foreach (['purpose', 'category', 'country', 'state', 'city', 'amenities', 'price', 'video_url', 'beds', 'bath', 'area', 'type'] as $key) {
                $v = $request->input($key);
                if ($v !== null && (string) $v !== '') {
                    $context[$key] = (string) $v;
                }
            }

            $result = $this->aiService->generatePropertyDescription(
                (string) ($request->input('title') ?? ''),
                (string) ($request->input('location') ?? ''),
                (string) ($request->input('features') ?? ''),
                $context
            );

            if (! $result['success']) {
                return response()->json(['success' => false, 'error' => $result['error'] ?? 'Generation failed.'], 400);
            }

            return response()->json([
                'success' => true,
                'description' => $result['description'] ?? '',
                'meta_keywords' => $result['meta_keywords'] ?? '',
                'meta_description' => $result['meta_description'] ?? '',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => config('app.debug') ? $e->getMessage() : 'Server error. Please try again.',
            ], 500);
        }
    }

    /**
     * Analyze property image with AI vision (9.4). Vendor or admin only.
     */
    public function analyzeImage(Request $request): JsonResponse
    {
        if (! Auth::guard('vendor')->check() && ! Auth::guard('admin')->check()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.'], 401);
        }
        if (! $this->aiService->isAvailable()) {
            return response()->json(['success' => false, 'error' => 'AI assistant is not available.'], 503);
        }

        if ($request->hasFile('image')) {
            $request->validate(['image' => 'required|image|mimes:jpeg,png,gif,webp|max:5120']);
            $file = $request->file('image');
            $base64 = base64_encode($file->get());
            $result = $this->aiService->analyzePropertyImage($base64);
        } elseif ($request->filled('image_url')) {
            $url = $request->input('image_url');
            if (! preg_match('#^https?://#i', $url)) {
                return response()->json(['success' => false, 'error' => 'Invalid URL.'], 422);
            }
            $result = $this->aiService->analyzePropertyImage($url);
        } else {
            return response()->json(['success' => false, 'error' => 'Send image file or image_url.'], 422);
        }

        if (! $result['success']) {
            return response()->json(['success' => false, 'error' => $result['error'] ?? 'Analysis failed.'], 400);
        }

        return response()->json([
            'success' => true,
            'tags' => $result['tags'] ?? [],
            'description' => $result['description'] ?? '',
        ]);
    }

    /**
     * Translate text to target language (9.5). Vendor or admin only.
     */
    public function translate(Request $request): JsonResponse
    {
        if (! Auth::guard('vendor')->check() && ! Auth::guard('admin')->check()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.'], 401);
        }
        if (! $this->aiService->isAvailable()) {
            return response()->json(['success' => false, 'error' => 'AI assistant is not available.'], 503);
        }

        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:8000',
            'target_language' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }

        $result = $this->aiService->translate(
            $request->input('text'),
            $request->input('target_language')
        );

        if (! $result['success']) {
            return response()->json(['success' => false, 'error' => $result['error'] ?? 'Translation failed.'], 400);
        }

        return response()->json([
            'success' => true,
            'translation' => $result['translation'],
        ]);
    }
}
