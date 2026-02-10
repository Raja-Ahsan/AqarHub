<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Agent;
use App\Models\SocialConnection;
use App\Models\Vendor;
use App\Services\SocialPostingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
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
     * Redirect to provider (Facebook/LinkedIn). Store guard in session so callback knows which user to attach.
     */
    public function redirectToProvider(Request $request, string $driver): RedirectResponse
    {
        $user = $this->currentUser();
        if (! $user || ! in_array($driver, ['facebook', 'linkedin'], true)) {
            return $this->redirectBack($request, $user, 'invalid');
        }
        session([
            'social_connect_guard' => $user['guard'],
            'social_connect_id' => $user['model']->id,
        ]);
        if ($driver === 'facebook') {
            return Socialite::driver('facebook')
                ->scopes(['pages_show_list', 'pages_read_engagement', 'pages_manage_posts'])
                ->redirect();
        }
        if ($driver === 'linkedin') {
            return Socialite::driver('linkedin')
                ->scopes(['w_member_social'])
                ->redirect();
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
        session()->forget(['social_connect_guard', 'social_connect_id']);
        $redirectUrl = $this->dashboardUrlForGuard($guard);

        if (! $guard || ! $id || ! in_array($driver, ['facebook', 'linkedin'], true)) {
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

        try {
            $socialUser = Socialite::driver($driver)->user();
        } catch (\Throwable $e) {
            return redirect($redirectUrl)->with('error', __('Could not connect: ') . $e->getMessage());
        }

        $connection = SocialConnection::updateOrCreate(
            [
                'connectable_type' => get_class($model),
                'connectable_id' => $model->id,
                'platform' => $driver,
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

        return redirect($redirectUrl)->with('success', __("Connected to :platform successfully.", ['platform' => ucfirst($driver)]));
    }

    /**
     * Disconnect a platform. Requires auth (admin/vendor/agent).
     */
    public function disconnect(Request $request, string $platform): \Illuminate\Http\JsonResponse
    {
        $user = $this->currentUser();
        if (! $user) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.'], 401);
        }
        $deleted = SocialConnection::where('connectable_type', $user['type'])
            ->where('connectable_id', $user['model']->id)
            ->where('platform', $platform)
            ->delete();
        return response()->json(['success' => (bool) $deleted]);
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
}
