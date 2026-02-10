<?php

namespace App\Services;

use App\Models\SocialConnection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SocialPostingService
{
    /**
     * Post text to Facebook (Page or user feed). Uses connection's token or page token from meta.
     */
    public function postToFacebook(SocialConnection $connection, string $message): array
    {
        $token = $connection->getTokenForPosting();
        if (! $token) {
            return ['success' => false, 'error' => 'No valid Facebook token.'];
        }

        $pageId = is_array($connection->meta) ? ($connection->meta['facebook_page_id'] ?? null) : null;
        $url = $pageId
            ? "https://graph.facebook.com/v18.0/{$pageId}/feed"
            : 'https://graph.facebook.com/v18.0/me/feed';

        $params = ['message' => $message];
        if ($pageId) {
            $params['access_token'] = $token;
        }

        try {
            $response = $pageId
                ? Http::asForm()->post($url, $params)
                : Http::withToken($token)->post($url, ['message' => $message]);

            if ($response->successful()) {
                return ['success' => true, 'id' => $response->json('id')];
            }
            $body = $response->json();
            $error = $body['error']['message'] ?? $response->body();
            Log::warning('Facebook post failed: ' . $error);
            return ['success' => false, 'error' => $error];
        } catch (\Throwable $e) {
            Log::error('Facebook post exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Post text to LinkedIn (UGC post). Requires w_member_social scope.
     */
    public function postToLinkedIn(SocialConnection $connection, string $message): array
    {
        $token = $connection->access_token;
        if (! $token) {
            return ['success' => false, 'error' => 'No valid LinkedIn token.'];
        }

        try {
            $response = Http::withToken($token)
                ->withHeaders(['X-Restli-Protocol-Version' => '2.0.0'])
                ->post('https://api.linkedin.com/v2/ugcPosts', [
                    'author' => 'urn:li:person:' . $connection->platform_user_id,
                    'lifecycleState' => 'PUBLISHED',
                    'specificContent' => [
                        'com.linkedin.ugc.ShareContent' => [
                            'shareCommentary' => ['text' => $message],
                            'shareMediaCategory' => 'NONE',
                        ],
                    ],
                    'visibility' => ['com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC'],
                ]);

            if ($response->successful()) {
                return ['success' => true, 'id' => $response->header('X-RestLi-Id')];
            }
            $body = $response->json();
            $error = $body['message'] ?? $body['status'] ?? $response->body();
            Log::warning('LinkedIn post failed: ' . $error);
            return ['success' => false, 'error' => $error];
        } catch (\Throwable $e) {
            Log::error('LinkedIn post exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Fetch Facebook Pages for the user and store first page's token in connection meta (for page posting).
     */
    public function fetchAndStoreFacebookPageToken(SocialConnection $connection): bool
    {
        $token = $connection->access_token;
        if (! $token) {
            return false;
        }
        try {
            $response = Http::withToken($token)->get('https://graph.facebook.com/v18.0/me/accounts', [
                'fields' => 'id,name,access_token',
            ]);
            if (! $response->successful()) {
                return false;
            }
            $data = $response->json('data');
            if (empty($data) || ! isset($data[0]['id'], $data[0]['access_token'])) {
                return false;
            }
            $page = $data[0];
            $meta = $connection->meta ?? [];
            $meta['facebook_page_id'] = $page['id'];
            $meta['facebook_page_name'] = $page['name'] ?? '';
            $meta['page_access_token'] = $page['access_token'];
            $connection->meta = $meta;
            $connection->save();
            return true;
        } catch (\Throwable $e) {
            Log::error('Facebook fetch pages: ' . $e->getMessage());
            return false;
        }
    }
}
