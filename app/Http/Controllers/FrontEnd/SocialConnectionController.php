<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Agent;
use App\Models\SocialConnection;
use App\Models\SocialLink;
use App\Models\UserSocialCredentials;
use App\Models\Vendor;
use Illuminate\Support\Facades\Validator;
use App\Services\SocialPostingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialConnectionController extends Controller
{
    public function __construct(
        protected SocialPostingService $postingService
    ) {}

    /**
     * Resolve current user model and guard from auth.
     */
    protected function currentUser(): ?array
    {
        if (Auth::guard('admin')->check()) {
            return ['guard' => 'admin', 'model' => Admin::find(Auth::guard('admin')->id()), 'type' => Admin::class];
        }
        if (Auth::guard('vendor')->check()) {
            return ['guard' => 'vendor', 'model' => Vendor::find(Auth::guard('vendor')->id()), 'type' => Vendor::class];
        }
        if (Auth::guard('agent')->check()) {
            return ['guard' => 'agent', 'model' => Agent::find(Auth::guard('agent')->id()), 'type' => Agent::class];
        }
        return null;
    }

    /**
     * Redirect to provider (Facebook, LinkedIn, Instagram via Facebook, TikTok, Twitter). Uses current user's credentials from DB.
     */
    public function redirectToProvider(Request $request, string $driver): RedirectResponse
    {
        $user = $this->currentUser();
        if (! $user || ! in_array($driver, ['facebook', 'linkedin', 'instagram', 'tiktok', 'twitter'], true)) {
            return $this->redirectBack($request, $user, 'invalid');
        }
        $creds = $user['model']->socialCredentials;
        if (! $creds) {
            return $this->redirectBack($request, $user, __('Add your social app credentials in Edit Profile first.'));
        }
        session([
            'social_connect_guard' => $user['guard'],
            'social_connect_id' => $user['model']->id,
            'social_connect_platform' => $driver,
        ]);
        if ($driver === 'twitter') {
            if (! $creds->hasTwitter()) {
                return $this->redirectBack($request, $user, __('Add your Twitter/X app credentials in Edit Profile first.'));
            }
            config(['services.x' => $creds->getTwitterConfig()]);
            return Socialite::driver('x')
                ->scopes(['users.read', 'tweet.read', 'tweet.write'])
                ->redirect();
        }
        if ($driver === 'facebook') {
            if (! $creds->hasFacebook()) {
                return $this->redirectBack($request, $user, __('Add your Facebook app credentials in Edit Profile first.'));
            }
            config(['services.facebook' => $creds->getFacebookConfig()]);
            return Socialite::driver('facebook')
                ->scopes(['pages_show_list', 'pages_read_engagement', 'pages_manage_posts'])
                ->redirect();
        }
        if ($driver === 'linkedin') {
            if (! $creds->hasLinkedIn()) {
                return $this->redirectBack($request, $user, __('Add your LinkedIn app credentials in Edit Profile first.'));
            }
            config(['services.linkedin' => $creds->getLinkedInConfig()]);
            return Socialite::driver('linkedin')
                ->scopes(['w_member_social'])
                ->redirect();
        }
        if ($driver === 'instagram') {
            if (! $creds->hasFacebook()) {
                return $this->redirectBack($request, $user, __('Instagram uses your Facebook app. Add your Facebook app credentials in Edit Profile first.'));
            }
            config(['services.facebook' => $creds->getFacebookConfig()]);
            $redirectUrl = rtrim(config('app.url'), '/') . '/auth/social/callback/instagram';
            return Socialite::driver('facebook')
                ->redirectUrl($redirectUrl)
                ->scopes(['pages_show_list', 'instagram_basic', 'instagram_content_publish'])
                ->redirect();
        }
        if ($driver === 'tiktok') {
            if (! $creds->hasTiktok()) {
                return $this->redirectBack($request, $user, __('Add your TikTok app credentials in Edit Profile first.'));
            }
            $cfg = $creds->getTiktokConfig();
            $state = Str::random(32);
            session(['social_tiktok_state' => $state]);
            $authUrl = 'https://www.tiktok.com/v2/auth/authorize/?' . http_build_query([
                'client_key' => $cfg['client_key'],
                'scope' => 'user.info.basic,video.list',
                'response_type' => 'code',
                'redirect_uri' => $cfg['redirect'],
                'state' => $state,
            ]);
            return redirect()->away($authUrl);
        }
        return $this->redirectBack($request, $user, 'invalid');
    }

    /**
     * OAuth callback. No auth required; we use session to attach to the user who started the flow.
     */
    public function handleProviderCallback(Request $request, string $driver): RedirectResponse
    {
        $guard = session('social_connect_guard');
        $id = session('social_connect_id');
        $platform = session('social_connect_platform', $driver);
        $tiktokState = session('social_tiktok_state');
        session()->forget(['social_connect_guard', 'social_connect_id', 'social_connect_platform', 'social_tiktok_state']);
        $redirectUrl = $this->dashboardUrlForGuard($guard);

        if (! $guard || ! $id || ! in_array($driver, ['facebook', 'linkedin', 'instagram', 'tiktok', 'twitter'], true)) {
            return redirect($redirectUrl)->with('error', __('Social connection was cancelled or session expired.'));
        }

        $model = match ($guard) {
            'admin' => Admin::find($id),
            'vendor' => Vendor::find($id),
            'agent' => Agent::find($id),
            default => null,
        };
        if (! $model) {
            return redirect($redirectUrl)->with('error', __('User not found.'));
        }

        if ($driver === 'tiktok') {
            return $this->handleTiktokCallback($request, $model, $redirectUrl, $tiktokState);
        }

        $creds = $model->socialCredentials;
        if (! $creds) {
            return redirect($redirectUrl)->with('error', __('Social app credentials not found. Add them in Edit Profile.'));
        }
        if ($driver === 'twitter') {
            config(['services.x' => $creds->getTwitterConfig()]);
        } elseif ($driver === 'instagram') {
            config(['services.facebook' => $creds->getFacebookConfig()]);
        } elseif ($driver === 'facebook') {
            config(['services.facebook' => $creds->getFacebookConfig()]);
        } elseif ($driver === 'linkedin') {
            config(['services.linkedin' => $creds->getLinkedInConfig()]);
        }
        try {
            if ($driver === 'twitter') {
                $socialUser = Socialite::driver('x')->user();
                $savePlatform = 'twitter';
            } elseif ($driver === 'instagram') {
                $redirectUri = rtrim(config('app.url'), '/') . '/auth/social/callback/instagram';
                $socialUser = Socialite::driver('facebook')->redirectUrl($redirectUri)->user();
                $savePlatform = 'instagram';
            } else {
                $socialUser = Socialite::driver($driver)->user();
                $savePlatform = $driver;
            }
        } catch (\Throwable $e) {
            return redirect($redirectUrl)->with('error', __('Could not connect: ') . $e->getMessage());
        }
        $connection = SocialConnection::updateOrCreate(
            [
                'connectable_type' => get_class($model),
                'connectable_id' => $model->id,
                'platform' => $savePlatform,
            ],
            [
                'access_token' => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken ?? null,
                'expires_at' => isset($socialUser->expiresIn) ? now()->addSeconds($socialUser->expiresIn) : null,
                'platform_user_id' => $socialUser->getId(),
                'platform_username' => $socialUser->getName() ?? $socialUser->getEmail(),
            ]
        );

        if ($driver === 'facebook') {
            $this->postingService->fetchAndStoreFacebookPageToken($connection);
        }
        if ($driver === 'instagram') {
            $this->postingService->fetchAndStoreInstagramAccount($connection);
        }

        return redirect($redirectUrl)->with('success', __("Connected to :platform successfully.", ['platform' => ucfirst($savePlatform)]));
    }

    protected function handleTiktokCallback(Request $request, Admin|Vendor|Agent $model, string $redirectUrl, ?string $expectedState): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect($redirectUrl)->with('error', $request->get('error_description', $request->get('error', __('TikTok authorization was denied or failed.'))));
        }
        if (! $expectedState || $request->get('state') !== $expectedState) {
            return redirect($redirectUrl)->with('error', __('Invalid state.'));
        }
        $code = $request->get('code');
        if (! $code) {
            return redirect($redirectUrl)->with('error', __('No authorization code received.'));
        }
        $code = urldecode($code);
        $creds = $model->socialCredentials;
        if (! $creds || ! $creds->hasTiktok()) {
            return redirect($redirectUrl)->with('error', __('TikTok credentials not found. Add them in Edit Profile.'));
        }
        $cfg = $creds->getTiktokConfig();
        $response = Http::asForm()->post('https://open.tiktokapis.com/v2/oauth/token/', [
            'client_key' => $cfg['client_key'],
            'client_secret' => $cfg['client_secret'],
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $cfg['redirect'],
        ]);
        if (! $response->successful()) {
            $body = $response->json();
            return redirect($redirectUrl)->with('error', $body['error_description'] ?? $body['error'] ?? __('TikTok connection failed.'));
        }
        $data = $response->json();
        $expiresIn = (int) ($data['expires_in'] ?? 86400);
        $accessToken = $data['access_token'] ?? null;
        $openId = $data['open_id'] ?? null;
        $displayName = $this->fetchTiktokDisplayName($accessToken);
        SocialConnection::updateOrCreate(
            [
                'connectable_type' => get_class($model),
                'connectable_id' => $model->id,
                'platform' => 'tiktok',
            ],
            [
                'access_token' => $accessToken,
                'refresh_token' => $data['refresh_token'] ?? null,
                'expires_at' => now()->addSeconds($expiresIn),
                'platform_user_id' => $openId,
                'platform_username' => $displayName ?: $openId ?: 'TikTok',
                'meta' => ['scope' => $data['scope'] ?? null],
            ]
        );
        return redirect($redirectUrl)->with('success', __('Connected to TikTok successfully.'));
    }

    protected function fetchTiktokDisplayName(?string $accessToken): ?string
    {
        if (! $accessToken) {
            return null;
        }
        try {
            $response = Http::withToken($accessToken)
                ->get('https://open.tiktokapis.com/v2/user/info/', ['fields' => 'open_id,display_name']);
            if (! $response->successful()) {
                return null;
            }
            $data = $response->json('data.user');
            return is_array($data) && ! empty($data['display_name']) ? $data['display_name'] : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Disconnect a platform. Requires auth (admin/vendor/agent).
     */
    public function disconnect(Request $request, string $platform): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        $user = $this->currentUser();
        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => 'Unauthorized.'], 401);
            }
            return redirect()->back()->with('error', __('Unauthorized.'));
        }
        $connection = SocialConnection::where('connectable_type', $user['type'])
            ->where('connectable_id', $user['model']->id)
            ->where('platform', $platform)
            ->first();
        if ($connection && $platform === 'tiktok' && $connection->access_token) {
            $creds = $connection->connectable?->socialCredentials;
            if ($creds && $creds->hasTiktok()) {
                $cfg = $creds->getTiktokConfig();
                Http::asForm()->post('https://open.tiktokapis.com/v2/oauth/revoke/', [
                    'client_key' => $cfg['client_key'],
                    'client_secret' => $cfg['client_secret'],
                    'token' => $connection->access_token,
                ]);
            }
        }
        $deleted = $connection ? $connection->delete() : false;
        if ($request->expectsJson()) {
            return response()->json(['success' => (bool) $deleted]);
        }
        return redirect()->back()->with('success', $deleted ? __('Disconnected successfully.') : __('Connection not found.'));
    }

    /**
     * List connections for current user (for JSON).
     */
    public function listConnections(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $this->currentUser();
        if (! $user) {
            return response()->json(['success' => false, 'connections' => []], 401);
        }
        $connections = SocialConnection::where('connectable_type', $user['type'])
            ->where('connectable_id', $user['model']->id)
            ->get()
            ->map(fn ($c) => ['platform' => $c->platform, 'username' => $c->platform_username, 'expired' => $c->isExpired()]);
        return response()->json(['success' => true, 'connections' => $connections]);
    }

    protected function redirectBack(Request $request, ?array $user, string $message): RedirectResponse
    {
        $url = $user ? $this->dashboardUrlForGuard($user['guard']) : url('/');
        return redirect($url)->with('error', __($message));
    }

    protected function dashboardUrlForGuard(?string $guard): string
    {
        return match ($guard) {
            'admin' => route('admin.edit_profile'),
            'vendor' => route('vendor.edit.profile'),
            'agent' => route('agent.edit.profile'),
            default => url('/'),
        };
    }

    /**
     * Save social profile links (URLs) for the current user. Admin, Vendor, or Agent.
     */
    public function updateSocialLinks(Request $request): RedirectResponse
    {
        $user = $this->currentUser();
        if (! $user) {
            return redirect()->back()->with('error', __('Unauthorized.'));
        }
        $rules = [];
        foreach (SocialLink::urlKeys() as $key) {
            $rules[$key] = ['nullable', 'string', 'url', 'max:500'];
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $data = $validator->validated();
        $link = SocialLink::firstOrCreate(
            [
                'connectable_type' => $user['type'],
                'connectable_id' => $user['model']->id,
            ],
            array_fill_keys(SocialLink::urlKeys(), null)
        );
        $link->update($data);
        return redirect()->back()->with('success', __('Social links saved.'));
    }

    /**
     * Save social platform app credentials (per user). Admin, Vendor, Agent only.
     */
    public function updateSocialCredentials(Request $request): RedirectResponse
    {
        $user = $this->currentUser();
        if (! $user) {
            return redirect()->back()->with('error', __('Unauthorized.'));
        }
        $rules = [
            'facebook_app_id' => ['nullable', 'string', 'max:500'],
            'facebook_app_secret' => ['nullable', 'string', 'max:500'],
            'linkedin_client_id' => ['nullable', 'string', 'max:500'],
            'linkedin_client_secret' => ['nullable', 'string', 'max:500'],
            'tiktok_client_key' => ['nullable', 'string', 'max:500'],
            'tiktok_client_secret' => ['nullable', 'string', 'max:500'],
            'twitter_client_id' => ['nullable', 'string', 'max:500'],
            'twitter_client_secret' => ['nullable', 'string', 'max:500'],
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $data = $validator->validated();
        foreach (['facebook_app_secret', 'linkedin_client_secret', 'tiktok_client_secret', 'twitter_client_secret'] as $key) {
            if (($data[$key] ?? '') === '') {
                unset($data[$key]);
            }
        }
        $creds = UserSocialCredentials::firstOrCreate(
            [
                'connectable_type' => $user['type'],
                'connectable_id' => $user['model']->id,
            ],
            array_fill_keys(array_keys($rules), null)
        );
        $creds->update($data);
        return redirect()->back()->with('success', __('Social app credentials saved.'));
    }
}
