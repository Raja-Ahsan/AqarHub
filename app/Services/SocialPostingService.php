<?php

namespace App\Services;

use App\Models\SocialConnection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SocialPostingService
{
    /**
     * Post text (and optional image) to Facebook (Page or user feed). Uses connection's token or page token from meta.
     */
    public function postToFacebook(SocialConnection $connection, string $message, string $imageUrl = ''): array
    {
        $token = $connection->getTokenForPosting();
        if (! $token) {
            return ['success' => false, 'error' => 'No valid Facebook token.'];
        }

        $pageId = is_array($connection->meta) ? ($connection->meta['facebook_page_id'] ?? null) : null;
        $endpoint = $pageId ? "{$pageId}" : 'me';

        try {
            if ($imageUrl !== '') {
                $url = "https://graph.facebook.com/v18.0/{$endpoint}/photos";
                $params = ['url' => $imageUrl, 'caption' => $message];
                if ($pageId) {
                    $params['access_token'] = $token;
                    $response = Http::asForm()->post($url, $params);
                } else {
                    $response = Http::withToken($token)->post($url, $params);
                }
            } else {
                $url = "https://graph.facebook.com/v18.0/{$endpoint}/feed";
                $params = ['message' => $message];
                if ($pageId) {
                    $params['access_token'] = $token;
                    $response = Http::asForm()->post($url, $params);
                } else {
                    $response = Http::withToken($token)->post($url, ['message' => $message]);
                }
            }

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
     * Post text to X (Twitter) via API v2. Tweet text is truncated to 280 characters.
     */
    public function postToTwitter(SocialConnection $connection, string $message): array
    {
        $token = $connection->access_token;
        if (! $token) {
            return ['success' => false, 'error' => 'No valid X (Twitter) token.'];
        }
        $text = mb_strlen($message) > 280 ? mb_substr($message, 0, 277) . '...' : $message;

        try {
            $response = Http::withToken($token)
                ->asJson()
                ->post('https://api.twitter.com/2/tweets', [
                    'text' => $text,
                ]);

            if ($response->successful()) {
                return ['success' => true, 'id' => $response->json('data.id')];
            }
            $body = $response->json();
            $error = $body['errors'][0]['message'] ?? $body['detail'] ?? $response->body();
            Log::warning('X (Twitter) post failed: ' . $error);
            return ['success' => false, 'error' => $error];
        } catch (\Throwable $e) {
            Log::error('X (Twitter) post exception: ' . $e->getMessage());
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

    /**
     * Fetch Instagram Business Account linked to the user's Facebook Page and store in connection meta.
     */
    public function fetchAndStoreInstagramAccount(SocialConnection $connection): bool
    {
        $token = $connection->access_token;
        if (! $token) {
            return false;
        }
        try {
            $response = Http::withToken($token)->get('https://graph.facebook.com/v18.0/me/accounts', [
                'fields' => 'id,name,access_token,instagram_business_account',
            ]);
            if (! $response->successful()) {
                return false;
            }
            $data = $response->json('data');
            foreach ($data ?? [] as $page) {
                $igAccount = $page['instagram_business_account'] ?? null;
                if (! empty($igAccount['id'])) {
                    $meta = $connection->meta ?? [];
                    $meta['instagram_business_account_id'] = $igAccount['id'];
                    $meta['page_access_token'] = $page['access_token'];
                    $meta['facebook_page_id'] = $page['id'];
                    $connection->meta = $meta;
                    $connection->save();
                    return true;
                }
            }
            return false;
        } catch (\Throwable $e) {
            Log::error('Instagram fetch account: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Post to Instagram (requires image). Uses caption + image_url; creates media container then publishes.
     */
    public function postToInstagram(SocialConnection $connection, string $caption, string $imageUrl): array
    {
        $igId = is_array($connection->meta) ? ($connection->meta['instagram_business_account_id'] ?? null) : null;
        $token = is_array($connection->meta) ? ($connection->meta['page_access_token'] ?? null) : null;
        if (! $igId || ! $token) {
            return ['success' => false, 'error' => 'No valid Instagram account connected.'];
        }
        if (empty($imageUrl)) {
            return ['success' => false, 'error' => 'Instagram requires an image. Use a property image or add one.'];
        }
        try {
            $createUrl = "https://graph.facebook.com/v18.0/{$igId}/media";
            $response = Http::withToken($token)->post($createUrl, [
                'image_url' => $imageUrl,
                'caption' => $caption,
            ]);
            if (! $response->successful()) {
                $body = $response->json();
                $error = $body['error']['message'] ?? $response->body();
                Log::warning('Instagram media create failed: ' . $error);
                return ['success' => false, 'error' => $error];
            }
            $containerId = $response->json('id');
            if (! $containerId) {
                return ['success' => false, 'error' => 'No container ID returned.'];
            }
            $publishUrl = "https://graph.facebook.com/v18.0/{$igId}/media_publish";
            $publishResponse = Http::withToken($token)->post($publishUrl, ['creation_id' => $containerId]);
            if (! $publishResponse->successful()) {
                $body = $publishResponse->json();
                $error = $body['error']['message'] ?? $publishResponse->body();
                Log::warning('Instagram publish failed: ' . $error);
                return ['success' => false, 'error' => $error];
            }
            return ['success' => true, 'id' => $publishResponse->json('id')];
        } catch (\Throwable $e) {
            Log::error('Instagram post exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
