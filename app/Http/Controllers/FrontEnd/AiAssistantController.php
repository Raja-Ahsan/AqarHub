<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use App\Http\Helpers\VendorPermissionHelper;
use App\Models\AiChatMessage;
use App\Models\Agent;
use App\Models\AgentInfo;
use App\Models\Property\City;
use App\Models\Property\CityContent;
use App\Models\Property\Content as PropertyContent;
use App\Models\Property\CountryContent;
use App\Models\Property\Property;
use App\Models\Property\StateContent;
use App\Jobs\BulkGenerateDescriptionJob;
use App\Models\Admin;
use App\Models\SocialConnection;
use App\Models\Vendor;
use App\Models\VendorInfo;
use App\Services\AiAssistantService;
use App\Services\SocialPostingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class AiAssistantController extends Controller
{
    public function __construct(
        protected AiAssistantService $aiService,
        protected SocialPostingService $socialPostingService
    ) {}

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

        $searchUrl = null;
        $properties = [];

        // If message looks like a property search (or a follow-up to a previous search): parse filters using chat history, fetch real properties, build search URL
        if ($this->looksLikePropertySearch($message, $history)) {
            $searchResult = $this->aiService->parseSearchQuery($message, $history);
            if ($searchResult['success'] && ! empty($searchResult['filters'])) {
                $filters = $searchResult['filters'];
                // Only add type/purpose when user explicitly asks (e.g. "for rent", "for sale", "commercial")
                $useTypePurpose = $this->userExplicitlyAskedTypeOrPurpose($message);
                if (! $useTypePurpose) {
                    $filters['type'] = null;
                    $filters['purpose'] = null;
                }
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
                    if (empty($filters['state']) && empty($filters['country'])) {
                        $locationFromCity = $this->getStateAndCountryFromCity($filters['city']);
                        if (! empty($locationFromCity['state'])) {
                            $params['state'] = $locationFromCity['state'];
                            $filters['state'] = $locationFromCity['state'];
                        }
                        if (! empty($locationFromCity['country'])) {
                            $params['country'] = $locationFromCity['country'];
                            $filters['country'] = $locationFromCity['country'];
                        }
                    }
                }
                if (! empty($filters['state'])) {
                    $params['state'] = $filters['state'];
                }
                if (! empty($filters['country'])) {
                    $params['country'] = $filters['country'];
                }
                if ($useTypePurpose && ! empty($filters['type']) && in_array($filters['type'], ['residential', 'commercial'])) {
                    $params['type'] = $filters['type'];
                }
                if ($useTypePurpose && ! empty($filters['purpose']) && in_array($filters['purpose'], ['sale', 'rent'])) {
                    $params['purpose'] = $filters['purpose'];
                }
                $searchUrl = count($params) > 0
                    ? route('frontend.properties') . '?' . http_build_query($params)
                    : route('frontend.properties');

                $properties = $this->fetchPropertiesByFilters($filters, 12);

                // Replace generic AI reply with a clear message when we have real data or search link
                $count = count($properties);
                if ($count > 0) {
                    $replyMessage = $count === 1
                        ? __('I found 1 property matching your search. Browse it below or view all results.')
                        : __('I found :count properties matching your search. Browse them below or view all results.', ['count' => $count]);
                } else {
                    $replyMessage = __('Use the link below to browse properties with your filters on the platform. You can refine your search there.');
                }
            }
        }

        if (config('ai.save_chat_history', true) && Schema::hasTable('ai_chat_messages')) {
            $sessionId = $request->session()->getId();
            $userId = Auth::guard('web')->id();
            $intent = null;
            $leadScore = null;
            if (Schema::hasColumn('ai_chat_messages', 'intent')) {
                $classify = $this->aiService->classifyIntentAndScore($message);
                if ($classify['success']) {
                    $intent = $classify['intent'] ?? null;
                    $leadScore = isset($classify['lead_score']) ? (int) $classify['lead_score'] : null;
                }
            }
            AiChatMessage::create(array_filter([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'role' => 'user',
                'content' => $message,
                'intent' => $intent,
                'lead_score' => $leadScore,
            ]));
            AiChatMessage::create([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'role' => 'assistant',
                'content' => $replyMessage,
            ]);
        }

        $payload = [
            'success' => true,
            'message' => $replyMessage,
        ];
        if ($searchUrl !== null) {
            $payload['search_url'] = $searchUrl;
        }
        if (! empty($properties)) {
            $payload['properties'] = $properties;
        }

        return response()->json($payload);
    }

    /**
     * Get full property details plus vendor/agent contact for display in chat (e.g. when user asks "show details" or "interested in this property").
     */
    public function propertyDetails(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $slug = $request->input('slug');
        if (! $id && ! $slug) {
            return response()->json(['success' => false, 'error' => 'Property id or slug required.'], 422);
        }

        $misc = new MiscellaneousController;
        $language = $misc->getLanguage();
        if (! $language) {
            return response()->json(['success' => false, 'error' => 'Language not set.'], 400);
        }

        if ($id) {
            $property = Property::where([['properties.status', 1], ['properties.approve_status', 1]])
                ->where('properties.id', (int) $id)->first();
        } else {
            $contentRow = PropertyContent::where('language_id', $language->id)->where('slug', $slug)->first();
            if (! $contentRow) {
                return response()->json(['success' => false, 'error' => 'Property not found.'], 404);
            }
            $property = Property::where([['properties.status', 1], ['properties.approve_status', 1]])
                ->where('properties.id', $contentRow->property_id)->first();
        }

        if (! $property) {
            return response()->json(['success' => false, 'error' => 'Property not found.'], 404);
        }

        $content = $property->getContent($language->id) ?? $property->propertyContents->first();
        if (! $content) {
            return response()->json(['success' => false, 'error' => 'Property content not found.'], 404);
        }

        $imgPath = trim((string) ($property->featured_image ?? ''));
        $imageUrl = $imgPath !== ''
            ? asset('assets/img/property/featureds/' . $imgPath)
            : asset('assets/front/images/placeholder.png');
        $description = isset($content->description) ? trim(strip_tags($content->description)) : '';

        $agent = $property->agent_id ? Agent::with(['agent_info' => fn ($q) => $q->where('language_id', $language->id)])->find($property->agent_id) : null;
        $vendor = $property->vendor_id ? Vendor::with(['vendor_info' => fn ($q) => $q->where('language_id', $language->id)])->find($property->vendor_id) : null;

        $contact = null;
        if ($agent && $agent->agent_info) {
            $name = trim(($agent->agent_info->first_name ?? '') . ' ' . ($agent->agent_info->last_name ?? ''));
            $contact = [
                'name' => $name ?: $agent->username,
                'role' => 'Agent',
                'phone' => $agent->phone ?? '',
                'email' => $agent->email ?? '',
                'company' => $agent->vendor?->vendor_info?->name ?? '',
            ];
        } elseif ($vendor && $vendor->vendor_info) {
            $contact = [
                'name' => $vendor->vendor_info->name ?? $vendor->username,
                'role' => 'Vendor',
                'phone' => $vendor->phone ?? '',
                'email' => $vendor->email ?? '',
                'company' => $vendor->vendor_info->name ?? '',
            ];
        }

        return response()->json([
            'success' => true,
            'property' => [
                'id' => $property->id,
                'title' => $content->title ?? '',
                'slug' => $content->slug ?? '',
                'price' => $property->price,
                'address' => $content->address ?? '',
                'description' => $description,
                'beds' => $property->beds,
                'baths' => $property->bath,
                'image' => $imageUrl,
                'url' => route('frontend.property.details', ['slug' => $content->slug]),
                'vendor_id' => $property->vendor_id ?? 0,
                'agent_id' => $property->agent_id ?? null,
            ],
            'contact' => $contact,
        ]);
    }

    /**
     * Get state and country names (for current language) from a city name. Used to auto-set state/country in search URL.
     *
     * @return array{ state?: string, country?: string }
     */
    protected function getStateAndCountryFromCity(string $cityName): array
    {
        $misc = new MiscellaneousController;
        $language = $misc->getLanguage();
        if (! $language) {
            return [];
        }
        $cityContent = CityContent::where('language_id', $language->id)
            ->where('name', $cityName)->first();
        if (! $cityContent) {
            $cityContent = CityContent::where('language_id', $language->id)
                ->where('name', 'LIKE', '%' . trim($cityName) . '%')->first();
        }
        if (! $cityContent) {
            return [];
        }
        $cityRecord = City::find($cityContent->city_id);
        if (! $cityRecord) {
            return [];
        }
        $result = [];
        if ($cityRecord->state_id) {
            $stateContent = StateContent::where('state_id', $cityRecord->state_id)
                ->where('language_id', $language->id)->first();
            if ($stateContent && ! empty($stateContent->name)) {
                $result['state'] = $stateContent->name;
            }
        }
        if ($cityRecord->country_id) {
            $countryContent = CountryContent::where('country_id', $cityRecord->country_id)
                ->where('language_id', $language->id)->first();
            if ($countryContent && ! empty($countryContent->name)) {
                $result['country'] = $countryContent->name;
            }
        }
        return $result;
    }

    /**
     * Fetch real properties from the application by parsed search filters.
     * Returns array of { id, title, slug, price, address, image, url } for chat display.
     *
     * @param  array<string, mixed>  $filters  Keys: beds, baths, min_price, max_price, city, state, country, type, purpose
     * @param  int  $limit
     * @return array<int, array<string, mixed>>
     */
    protected function fetchPropertiesByFilters(array $filters, int $limit = 6): array
    {
        $misc = new MiscellaneousController;
        $language = $misc->getLanguage();
        if (! $language) {
            return [];
        }

        $countryId = $stateId = $cityId = null;
        if (! empty($filters['country'])) {
            $c = CountryContent::where('language_id', $language->id)
                ->where(function ($q) use ($filters) {
                    $q->where('name', 'LIKE', '%' . $filters['country'] . '%');
                })->first();
            if ($c) {
                $countryId = $c->country_id;
            }
        }
        if (! empty($filters['state'])) {
            $s = StateContent::where('language_id', $language->id)
                ->where('name', 'LIKE', '%' . $filters['state'] . '%')->first();
            if ($s) {
                $stateId = $s->state_id;
            }
        }
        if (! empty($filters['city'])) {
            $cityName = trim((string) $filters['city']);
            $city = CityContent::where('language_id', $language->id)
                ->where('name', $cityName)->first();
            if (! $city) {
                $city = CityContent::where('language_id', $language->id)
                    ->where('name', 'LIKE', '%' . $cityName . '%')->first();
            }
            if ($city) {
                $cityId = $city->city_id;
                $cityRecord = City::find($cityId);
                if ($cityRecord) {
                    if ($stateId === null && $cityRecord->state_id) {
                        $stateId = $cityRecord->state_id;
                    }
                    if ($countryId === null && $cityRecord->country_id) {
                        $countryId = $cityRecord->country_id;
                    }
                }
            }
        }

        $locationLike = null;
        if (! empty($filters['city']) && $cityId === null) {
            $locationLike = trim((string) $filters['city']);
        }
        if (! empty($filters['state']) && $stateId === null && ! $locationLike) {
            $locationLike = trim((string) $filters['state']);
        }
        if (! empty($filters['country']) && $countryId === null && ! $locationLike) {
            $locationLike = trim((string) $filters['country']);
        }

        $min = isset($filters['min_price']) && $filters['min_price'] !== '' ? (int) $filters['min_price'] : null;
        $max = isset($filters['max_price']) && $filters['max_price'] !== '' ? (int) $filters['max_price'] : null;
        $beds = isset($filters['beds']) && $filters['beds'] !== '' ? (int) $filters['beds'] : null;
        $baths = isset($filters['baths']) && $filters['baths'] !== '' ? (int) $filters['baths'] : null;
        $type = ! empty($filters['type']) && in_array($filters['type'], ['residential', 'commercial']) ? $filters['type'] : null;
        $purpose = ! empty($filters['purpose']) && in_array($filters['purpose'], ['sale', 'rent']) ? $filters['purpose'] : null;

        $query = Property::where([['properties.status', 1], ['properties.approve_status', 1]])
            ->join('property_contents', 'properties.id', 'property_contents.property_id')
            ->where('property_contents.language_id', $language->id)
            ->leftJoin('vendors', 'properties.vendor_id', '=', 'vendors.id')
            ->leftJoin('memberships', function ($join) {
                $join->on('properties.vendor_id', '=', 'memberships.vendor_id')
                    ->where('memberships.status', 1)
                    ->where('memberships.start_date', '<=', Carbon::now()->format('Y-m-d'))
                    ->where('memberships.expire_date', '>=', Carbon::now()->format('Y-m-d'));
            })
            ->where(function ($q) {
                $q->where('properties.vendor_id', 0)
                    ->orWhere(function ($q) {
                        $q->where('vendors.status', 1)->whereNotNull('memberships.id');
                    });
            })
            ->when($type, fn ($q) => $q->where('properties.type', $type))
            ->when($purpose, fn ($q) => $q->where('properties.purpose', $purpose))
            ->when($countryId, fn ($q) => $q->where('properties.country_id', $countryId))
            ->when($stateId, fn ($q) => $q->where('properties.state_id', $stateId))
            ->when($cityId, fn ($q) => $q->where('properties.city_id', $cityId))
            ->when($locationLike, fn ($q) => $q->where('property_contents.address', 'LIKE', '%' . $locationLike . '%'))
            ->when($beds !== null, fn ($q) => $q->where('properties.beds', $beds))
            ->when($baths !== null, fn ($q) => $q->where('properties.bath', $baths))
            ->when($min !== null && $max !== null, fn ($q) => $q->whereBetween('properties.price', [$min, $max]))
            ->when($min !== null && $max === null, fn ($q) => $q->where('properties.price', '>=', $min))
            ->when($max !== null && $min === null, fn ($q) => $q->where('properties.price', '<=', $max))
            ->select('properties.id', 'properties.price', 'properties.featured_image', 'properties.vendor_id', 'properties.agent_id', 'property_contents.title', 'property_contents.slug', 'property_contents.address', 'property_contents.description')
            ->groupBy('properties.id', 'properties.price', 'properties.featured_image', 'properties.vendor_id', 'properties.agent_id', 'property_contents.title', 'property_contents.slug', 'property_contents.address', 'property_contents.description')
            ->orderBy('properties.id', 'desc')
            ->limit($limit);

        $rows = $query->get();

        // If no results with strict filters, try again with relaxed filters (location/price only) to show some listings
        if ($rows->isEmpty() && ($cityId || $countryId || $stateId || $locationLike || $min !== null || $max !== null)) {
            $fallback = Property::where([['properties.status', 1], ['properties.approve_status', 1]])
                ->join('property_contents', 'properties.id', 'property_contents.property_id')
                ->where('property_contents.language_id', $language->id)
                ->leftJoin('vendors', 'properties.vendor_id', '=', 'vendors.id')
                ->leftJoin('memberships', function ($join) {
                    $join->on('properties.vendor_id', '=', 'memberships.vendor_id')
                        ->where('memberships.status', 1)
                        ->where('memberships.start_date', '<=', Carbon::now()->format('Y-m-d'))
                        ->where('memberships.expire_date', '>=', Carbon::now()->format('Y-m-d'));
                })
                ->where(function ($q) {
                    $q->where('properties.vendor_id', 0)
                        ->orWhere(function ($q) {
                            $q->where('vendors.status', 1)->whereNotNull('memberships.id');
                        });
                })
                ->when($countryId, fn ($q) => $q->where('properties.country_id', $countryId))
                ->when($stateId, fn ($q) => $q->where('properties.state_id', $stateId))
                ->when($cityId, fn ($q) => $q->where('properties.city_id', $cityId))
                ->when($locationLike, fn ($q) => $q->where('property_contents.address', 'LIKE', '%' . $locationLike . '%'))
                ->when($max !== null && $min === null, fn ($q) => $q->where('properties.price', '<=', $max))
                ->when($min !== null && $max === null, fn ($q) => $q->where('properties.price', '>=', $min))
                ->when($min !== null && $max !== null, fn ($q) => $q->whereBetween('properties.price', [$min, $max]))
                ->select('properties.id', 'properties.price', 'properties.featured_image', 'properties.vendor_id', 'properties.agent_id', 'property_contents.title', 'property_contents.slug', 'property_contents.address', 'property_contents.description')
                ->orderBy('properties.id', 'desc')
                ->limit($limit);
            $rows = $fallback->get();
        }

        $baseUrl = rtrim(config('app.url'), '/');
        $properties = [];
        $placeholderImage = asset('assets/front/images/placeholder.png');
        foreach ($rows as $row) {
            $desc = isset($row->description) ? trim(strip_tags($row->description)) : '';
            if (mb_strlen($desc) > 120) {
                $desc = mb_substr($desc, 0, 117) . '...';
            }
            $imgPath = trim((string) ($row->featured_image ?? ''));
            $imageUrl = $imgPath !== ''
                ? asset('assets/img/property/featureds/' . $imgPath)
                : $placeholderImage;
            $properties[] = [
                'id' => $row->id,
                'title' => $row->title ?? '',
                'slug' => $row->slug ?? '',
                'price' => $row->price,
                'address' => $row->address ?? '',
                'description' => $desc,
                'vendor_id' => $row->vendor_id ?? 0,
                'agent_id' => $row->agent_id ?? null,
                'image' => $imageUrl,
                'url' => route('frontend.property.details', ['slug' => $row->slug]),
            ];
        }

        return $properties;
    }

    /**
     * Whether the user explicitly asked for type (residential/commercial) or purpose (sale/rent).
     * If not, we avoid adding type/purpose to the search URL and DB query to prevent over-filtering.
     */
    protected function userExplicitlyAskedTypeOrPurpose(string $message): bool
    {
        $lower = mb_strtolower($message);
        $typePurposeWords = [
            'residential', 'commercial', 'for sale', 'for rent', 'to buy', 'to rent',
            'sale', 'rent', 'buy', 'rental', 'selling', 'renting',
        ];
        foreach ($typePurposeWords as $word) {
            if (str_contains($lower, $word)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Heuristic: does the message look like a property/search request so we should query the database?
     * Triggers for: "Dubai property", "property in X", "agents", "vendors", "projects", etc.
     * When history is not empty, also accepts short refinements like "cheaper ones", "3 bed".
     */
    protected function looksLikePropertySearch(string $message, array $history = []): bool
    {
        $lower = mb_strtolower(trim($message));
        $len = strlen($lower);
        $patterns = [
            'find', 'search', 'looking for', 'show me', 'properties with', 'bed', 'beds', 'bath',
            'under', 'above', 'rent', 'sale', 'buy', 'budget', 'max price', 'min price',
            'in the city', 'near', 'location',
            'property', 'properties', 'project', 'projects', 'listing', 'listings',
            'agent', 'agents', 'vendor', 'vendors', 'apartment', 'villa', 'house', 'houses',
        ];
        $followUpPatterns = ['cheaper', 'lower', 'higher', 'more', 'less', 'same', 'instead', 'another', 'other', 'what about', 'how about', 'any', 'with pool', 'with garden', 'studio'];
        $minLength = empty($history) ? 5 : 3;
        if ($len < $minLength) {
            return false;
        }
        foreach ($patterns as $p) {
            if (str_contains($lower, $p)) {
                return true;
            }
        }
        if (! empty($history)) {
            foreach ($followUpPatterns as $p) {
                if (str_contains($lower, $p)) {
                    return true;
                }
            }
        }
        return false;
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
            if (Auth::guard('vendor')->check() && ! $this->vendorPackageHasAi()) {
                return response()->json(['success' => false, 'error' => 'Your package does not include AI features.'], 403);
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
        if (Auth::guard('vendor')->check() && ! $this->vendorPackageHasAi()) {
            return response()->json(['success' => false, 'error' => 'Your package does not include AI features.'], 403);
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
        if (Auth::guard('vendor')->check() && ! $this->vendorPackageHasAi()) {
            return response()->json(['success' => false, 'error' => 'Your package does not include AI features.'], 403);
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

    /**
     * Suggest a professional reply to an inquiry (A-2). Vendor, agent, or admin only.
     */
    public function suggestReply(Request $request): JsonResponse
    {
        if (! Auth::guard('vendor')->check() && ! Auth::guard('agent')->check() && ! Auth::guard('admin')->check()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.'], 401);
        }
        if (Auth::guard('vendor')->check() && ! $this->vendorPackageHasAi()) {
            return response()->json(['success' => false, 'error' => 'Your package does not include AI features.'], 403);
        }
        if (! $this->aiService->isAvailable()) {
            return response()->json(['success' => false, 'error' => 'AI assistant is not available.'], 503);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:3000',
            'name' => 'nullable|string|max:255',
            'property_id' => 'nullable|integer|exists:properties,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }

        $propertyContext = null;
        if ($request->filled('property_id')) {
            $property = Property::with('propertyContent')->find($request->property_id);
            if ($property) {
                $content = $property->propertyContent ?? $property->propertyContents()->first();
                if ($content) {
                    $propertyContext = ($content->title ?? '') . (trim((string) ($content->address ?? '')) !== '' ? ' – ' . $content->address : '');
                }
                if (empty($propertyContext)) {
                    $propertyContext = 'Property #' . $property->id;
                }
            }
        }

        $result = $this->aiService->suggestReply(
            $request->input('message'),
            $request->input('name'),
            $propertyContext
        );

        if (! $result['success']) {
            return response()->json(['success' => false, 'error' => $result['error'] ?? 'Could not generate reply.'], 400);
        }

        return response()->json([
            'success' => true,
            'suggested_reply' => $result['suggested_reply'] ?? '',
        ]);
    }

    /**
     * Fair Housing / compliance check on property description (A-3). Vendor or admin only.
     */
    public function checkCompliance(Request $request): JsonResponse
    {
        if (! Auth::guard('vendor')->check() && ! Auth::guard('admin')->check()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.'], 401);
        }
        if (Auth::guard('vendor')->check() && ! $this->vendorPackageHasAi()) {
            return response()->json(['success' => false, 'error' => 'Your package does not include AI features.'], 403);
        }
        if (! $this->aiService->isAvailable()) {
            return response()->json(['success' => false, 'error' => 'AI assistant is not available.'], 503);
        }

        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:15000',
        ], [
            'description.required' => 'Please enter or paste the description text to check.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }

        $result = $this->aiService->checkCompliance($request->input('description'));

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Compliance check failed.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'compliant' => $result['compliant'] ?? true,
            'warnings' => $result['warnings'] ?? [],
            'summary' => $result['summary'] ?? '',
        ]);
    }

    /**
     * Generate social / ad copy for a property listing (B-1). Vendor or admin only.
     */
    public function generateSocialCopy(Request $request): JsonResponse
    {
        if (! Auth::guard('vendor')->check() && ! Auth::guard('admin')->check()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.'], 401);
        }
        if (Auth::guard('vendor')->check() && ! $this->vendorPackageHasAi()) {
            return response()->json(['success' => false, 'error' => 'Your package does not include AI features.'], 403);
        }
        if (! $this->aiService->isAvailable()) {
            return response()->json(['success' => false, 'error' => 'AI assistant is not available.'], 503);
        }

        $validator = Validator::make($request->all(), [
            'property_id' => 'required|integer|exists:properties,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }

        $property = Property::with('propertyContents')->find($request->property_id);
        if (! $property) {
            return response()->json(['success' => false, 'error' => 'Property not found.'], 404);
        }
        // Only check ownership when user is vendor (admin can generate for any property)
        if (Auth::guard('vendor')->check() && ! Auth::guard('admin')->check() && (int) $property->vendor_id !== (int) Auth::guard('vendor')->id()) {
            return response()->json(['success' => false, 'error' => 'Access denied.'], 403);
        }

        $defaultLang = \App\Models\Language::where('is_default', 1)->first();
        $content = $defaultLang
            ? $property->propertyContents->where('language_id', $defaultLang->id)->first()
            : $property->propertyContents->first();

        $title = $content ? trim(strip_tags((string) $content->title)) : '';
        $description = $content ? trim(strip_tags((string) $content->description)) : '';
        if (mb_strlen($description) > 3000) {
            $description = mb_substr($description, 0, 3000);
        }
        if ($title === '') {
            return response()->json(['success' => false, 'error' => __('Property title is required to generate social copy.')], 422);
        }

        $context = [];
        if ($property->price !== null && $property->price !== '') {
            $context['price'] = $property->price;
        }
        if ($content && trim((string) $content->address) !== '') {
            $context['address'] = trim($content->address);
        }
        if ($property->type) {
            $context['type'] = $property->type;
        }
        if ($property->purpose) {
            $context['purpose'] = $property->purpose;
        }
        if ($property->beds !== null && $property->beds !== '') {
            $context['beds'] = $property->beds;
        }
        if ($property->bath !== null && $property->bath !== '') {
            $context['bath'] = $property->bath;
        }
        if ($property->area !== null && $property->area !== '') {
            $context['area'] = $property->area;
        }

        $result = $this->aiService->generateSocialCopy($title, $description, $context);
        if (! $result['success']) {
            return response()->json(['success' => false, 'error' => $result['error'] ?? 'Generation failed.'], 400);
        }

        return response()->json([
            'success' => true,
            'facebook' => $result['facebook'] ?? '',
            'instagram' => $result['instagram'] ?? '',
            'linkedin' => $result['linkedin'] ?? '',
            'twitter' => $result['twitter'] ?? '',
            'hashtags' => $result['hashtags'] ?? '',
        ]);
    }

    /**
     * Post generated copy to a connected social platform (Facebook or LinkedIn). Admin, vendor, or agent.
     */
    public function postToSocial(Request $request): JsonResponse
    {
        if (! Auth::guard('vendor')->check() && ! Auth::guard('admin')->check() && ! Auth::guard('agent')->check()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'platform' => 'required|string|in:facebook,linkedin,instagram,twitter',
            'text' => 'required|string|max:10000',
            'image_url' => 'nullable|string|url|max:2000',
            'hashtags' => 'nullable|string|max:1000',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }

        $connectable = $this->currentConnectable();
        if (! $connectable) {
            return response()->json(['success' => false, 'error' => 'User not found.'], 403);
        }

        $connection = SocialConnection::where('connectable_type', $connectable['type'])
            ->where('connectable_id', $connectable['id'])
            ->where('platform', $request->platform)
            ->first();

        if (! $connection || $connection->isExpired()) {
            return response()->json(['success' => false, 'error' => __(':platform is not connected or token expired. Connect it in Settings.', ['platform' => ucfirst($request->platform)])], 400);
        }

        $text = $request->text;
        if ($request->filled('hashtags') && trim($request->hashtags) !== '') {
            $text = trim($text) . ' ' . trim($request->hashtags);
        }
        $imageUrl = $request->input('image_url', '');

        $result = match ($request->platform) {
            'facebook' => $this->socialPostingService->postToFacebook($connection, $text, $imageUrl),
            'linkedin' => $this->socialPostingService->postToLinkedIn($connection, $text),
            'instagram' => $this->socialPostingService->postToInstagram($connection, $text, $imageUrl),
            'twitter' => $this->socialPostingService->postToTwitter($connection, $text),
            default => ['success' => false, 'error' => 'Unsupported platform.'],
        };

        if (! $result['success']) {
            return response()->json(['success' => false, 'error' => $result['error'] ?? 'Post failed.'], 400);
        }
        return response()->json(['success' => true, 'message' => __("Posted to :platform successfully.", ['platform' => ucfirst($request->platform)])]);
    }

    protected function currentConnectable(): ?array
    {
        if (Auth::guard('admin')->check()) {
            $id = Auth::guard('admin')->id();
            return $id ? ['type' => Admin::class, 'id' => $id] : null;
        }
        if (Auth::guard('vendor')->check()) {
            $id = Auth::guard('vendor')->id();
            return $id ? ['type' => Vendor::class, 'id' => $id] : null;
        }
        if (Auth::guard('agent')->check()) {
            $id = Auth::guard('agent')->id();
            return $id ? ['type' => Agent::class, 'id' => $id] : null;
        }
        return null;
    }

    /**
     * Bulk generate descriptions for selected properties (B-2). Queue one job per property with delay to throttle API.
     * Vendor or admin only; vendor must have has_ai_features.
     */
    public function bulkGenerateDescription(Request $request): JsonResponse
    {
        if (! Auth::guard('vendor')->check() && ! Auth::guard('admin')->check()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.'], 401);
        }
        if (Auth::guard('vendor')->check() && ! $this->vendorPackageHasAi()) {
            return response()->json(['success' => false, 'error' => 'Your package does not include AI features.'], 403);
        }
        if (! $this->aiService->isAvailable()) {
            return response()->json(['success' => false, 'error' => 'AI assistant is not available.'], 503);
        }

        // Accept property_ids (JSON) or ids (form-style), so both admin and vendor flows work
        $rawIds = $request->input('property_ids', $request->input('ids', []));
        if (! is_array($rawIds)) {
            $rawIds = [];
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $rawIds))));
        if (count($ids) === 0) {
            return response()->json(['success' => false, 'error' => 'Select at least one property.'], 422);
        }
        $validator = Validator::make(['property_ids' => $ids], [
            'property_ids' => 'required|array',
            'property_ids.*' => 'integer|exists:properties,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }

        // Only restrict to vendor's properties when user is vendor (not admin)
        if (Auth::guard('vendor')->check() && ! Auth::guard('admin')->check()) {
            $vendorId = (int) Auth::guard('vendor')->id();
            $allowed = Property::whereIn('id', $ids)->where('vendor_id', $vendorId)->pluck('id')->all();
            $ids = array_values(array_intersect($ids, $allowed));
            if (count($ids) === 0) {
                return response()->json(['success' => false, 'error' => 'No allowed properties found. You can only generate descriptions for your own properties.'], 403);
            }
        }

        $delaySeconds = 15;
        foreach ($ids as $index => $propertyId) {
            BulkGenerateDescriptionJob::dispatch($propertyId)
                ->delay(now()->addSeconds($index * $delaySeconds));
        }

        return response()->json([
            'success' => true,
            'message' => __('Queued :count property(ies) for description generation. They will be processed in the background.', ['count' => count($ids)]),
            'queued_count' => count($ids),
        ]);
    }

    /**
     * Whether the current vendor's package includes AI features. Admin always has access.
     */
    protected function vendorPackageHasAi(): bool
    {
        if (Auth::guard('admin')->check()) {
            return true;
        }
        $vendorId = Auth::guard('vendor')->id();
        if (! $vendorId) {
            return false;
        }
        $package = VendorPermissionHelper::currentPackagePermission($vendorId);

        return $package && $package->has_ai_features;
    }
}
