{{-- Per-user social app credentials (Admin, Vendor, Agent). Pass: social_credentials (model or null), social_credentials_update_route --}}
<div class="card mt-3">
    <div class="card-header">
        <div class="card-title">{{ __('Social platform app credentials') }}</div>
        <p class="mb-0 small text-muted">{{ __('Save your own app credentials here to connect your accounts. Use the toggles below to see how to connect and get the keys for each platform.') }}</p>
    </div>
    <div class="card-body">
        <form action="{{ route($social_credentials_update_route) }}" method="post">
            @csrf
            @php
                $c = $social_credentials ?? null;
            @endphp

            {{-- Facebook (and Instagram) --}}
            <div class="border rounded mb-3 p-2">
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <h6 class="text-primary mb-0">{{ __('Facebook (and Instagram)') }}</h6>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="collapse" data-target="#socialHelpFacebook" aria-expanded="false" aria-controls="socialHelpFacebook">
                        <span class="fa fa-question-circle mr-1"></span> {{ __('How to connect & get keys') }}
                    </button>
                </div>
                <div class="collapse mt-2" id="socialHelpFacebook">
                    <div class="small text-muted bg-light p-3 rounded">
                        <strong>{{ __('How to connect and get keys:') }}</strong>
                        <ol class="mb-0 pl-3">
                            <li>{{ __('Go to') }} <a href="https://developers.facebook.com" target="_blank" rel="noopener">developers.facebook.com</a> {{ __('and create an app (e.g. Business).') }}</li>
                            <li>{{ __('Add product Facebook Login (Custom OAuth). In App settings → Basic, copy App ID and App Secret.') }}</li>
                            <li>{{ __('In Facebook Login → Settings, set Valid OAuth Redirect URI to:') }} <code>{{ rtrim(config('app.url'), '/') }}/auth/social/callback/facebook</code></li>
                            <li>{{ __('For Instagram: add Instagram Graph API to the same app and set redirect to:') }} <code>{{ rtrim(config('app.url'), '/') }}/auth/social/callback/instagram</code></li>
                            <li>{{ __('Paste App ID and App Secret in the fields below, then click Save credentials.') }}</li>
                        </ol>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6 mb-2">
                        <label class="font-weight-bold">{{ __('Facebook App ID') }}</label>
                        <input type="text" class="form-control" name="facebook_app_id" value="{{ old('facebook_app_id', $c ? $c->facebook_app_id : '') }}" placeholder="App ID" maxlength="500" autocomplete="off">
                        @error('facebook_app_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="font-weight-bold">{{ __('Facebook App Secret') }}</label>
                        <input type="password" class="form-control" name="facebook_app_secret" value="{{ old('facebook_app_secret') }}" placeholder="{{ $c && $c->facebook_app_secret ? '•••••••• (leave blank to keep)' : 'App Secret' }}" maxlength="500" autocomplete="new-password">
                        @error('facebook_app_secret')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>
            </div>

            {{-- LinkedIn --}}
            <div class="border rounded mb-3 p-2">
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <h6 class="text-primary mb-0">{{ __('LinkedIn') }}</h6>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="collapse" data-target="#socialHelpLinkedin" aria-expanded="false" aria-controls="socialHelpLinkedin">
                        <span class="fa fa-question-circle mr-1"></span> {{ __('How to connect & get keys') }}
                    </button>
                </div>
                <div class="collapse mt-2" id="socialHelpLinkedin">
                    <div class="small text-muted bg-light p-3 rounded">
                        <strong>{{ __('How to connect and get keys:') }}</strong>
                        <ol class="mb-0 pl-3">
                            <li>{{ __('Go to') }} <a href="https://www.linkedin.com/developers" target="_blank" rel="noopener">linkedin.com/developers</a> {{ __('and create an app.') }}</li>
                            <li>{{ __('In the app, open Auth and add Redirect URL:') }} <code>{{ rtrim(config('app.url'), '/') }}/auth/social/callback/linkedin</code></li>
                            <li>{{ __('Under Products, request Share on LinkedIn (adds w_member_social).') }}</li>
                            <li>{{ __('Copy Client ID and Client Secret from the app, paste below and click Save credentials.') }}</li>
                        </ol>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6 mb-2">
                        <label class="font-weight-bold">{{ __('LinkedIn Client ID') }}</label>
                        <input type="text" class="form-control" name="linkedin_client_id" value="{{ old('linkedin_client_id', $c ? $c->linkedin_client_id : '') }}" placeholder="Client ID" maxlength="500" autocomplete="off">
                        @error('linkedin_client_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="font-weight-bold">{{ __('LinkedIn Client Secret') }}</label>
                        <input type="password" class="form-control" name="linkedin_client_secret" value="{{ old('linkedin_client_secret') }}" placeholder="{{ $c && $c->linkedin_client_secret ? '•••••••• (leave blank to keep)' : 'Client Secret' }}" maxlength="500" autocomplete="new-password">
                        @error('linkedin_client_secret')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>
            </div>

            {{-- TikTok --}}
            <div class="border rounded mb-3 p-2">
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <h6 class="text-primary mb-0">{{ __('TikTok') }}</h6>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="collapse" data-target="#socialHelpTiktok" aria-expanded="false" aria-controls="socialHelpTiktok">
                        <span class="fa fa-question-circle mr-1"></span> {{ __('How to connect & get keys') }}
                    </button>
                </div>
                <div class="collapse mt-2" id="socialHelpTiktok">
                    <div class="small text-muted bg-light p-3 rounded">
                        <strong>{{ __('How to connect and get keys:') }}</strong>
                        <ol class="mb-0 pl-3">
                            <li>{{ __('Go to') }} <a href="https://developers.tiktok.com" target="_blank" rel="noopener">developers.tiktok.com</a> {{ __('and create an app.') }}</li>
                            <li>{{ __('Add Redirect URI:') }} <code>{{ rtrim(config('app.url'), '/') }}/auth/social/callback/tiktok</code> {{ __('(use HTTPS in production).') }}</li>
                            <li>{{ __('Request Login Kit with scopes user.info.basic and video.list.') }}</li>
                            <li>{{ __('Copy Client Key and Client Secret from the app, paste below and click Save credentials.') }}</li>
                        </ol>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6 mb-2">
                        <label class="font-weight-bold">{{ __('TikTok Client Key') }}</label>
                        <input type="text" class="form-control" name="tiktok_client_key" value="{{ old('tiktok_client_key', $c ? $c->tiktok_client_key : '') }}" placeholder="Client Key" maxlength="500" autocomplete="off">
                        @error('tiktok_client_key')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="font-weight-bold">{{ __('TikTok Client Secret') }}</label>
                        <input type="password" class="form-control" name="tiktok_client_secret" value="{{ old('tiktok_client_secret') }}" placeholder="{{ $c && $c->tiktok_client_secret ? '•••••••• (leave blank to keep)' : 'Client Secret' }}" maxlength="500" autocomplete="new-password">
                        @error('tiktok_client_secret')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>
            </div>

            {{-- Twitter / X --}}
            <div class="border rounded mb-3 p-2">
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <h6 class="text-primary mb-0">{{ __('Twitter / X') }}</h6>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="collapse" data-target="#socialHelpTwitter" aria-expanded="false" aria-controls="socialHelpTwitter">
                        <span class="fa fa-question-circle mr-1"></span> {{ __('How to connect & get keys') }}
                    </button>
                </div>
                <div class="collapse mt-2" id="socialHelpTwitter">
                    <div class="small text-muted bg-light p-3 rounded">
                        <strong>{{ __('How to connect and get keys:') }}</strong>
                        <ol class="mb-0 pl-3">
                            <li>{{ __('Go to') }} <a href="https://developer.x.com" target="_blank" rel="noopener">developer.x.com</a> {{ __('(Twitter Developer Portal) and create a Project and App.') }}</li>
                            <li>{{ __('Enable OAuth 2.0 and set Callback URI to:') }} <code>{{ rtrim(config('app.url'), '/') }}/auth/social/callback/twitter</code></li>
                            <li>{{ __('Request scopes: tweet.read, tweet.write, users.read.') }}</li>
                            <li>{{ __('Copy OAuth 2.0 Client ID and Client Secret, paste below and click Save credentials.') }}</li>
                        </ol>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6 mb-2">
                        <label class="font-weight-bold">{{ __('Twitter / X Client ID') }}</label>
                        <input type="text" class="form-control" name="twitter_client_id" value="{{ old('twitter_client_id', $c ? $c->twitter_client_id : '') }}" placeholder="Client ID" maxlength="500" autocomplete="off">
                        @error('twitter_client_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="font-weight-bold">{{ __('Twitter / X Client Secret') }}</label>
                        <input type="password" class="form-control" name="twitter_client_secret" value="{{ old('twitter_client_secret') }}" placeholder="{{ $c && $c->twitter_client_secret ? '•••••••• (leave blank to keep)' : 'Client Secret' }}" maxlength="500" autocomplete="new-password">
                        @error('twitter_client_secret')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">{{ __('Save credentials') }}</button>
        </form>
    </div>
</div>
