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

            {{-- WhatsApp (number + channel link; all settings in DB, no .env) --}}
            <div class="border rounded mb-3 p-2">
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <h6 class="text-success mb-0">{{ __('WhatsApp') }}</h6>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="collapse" data-target="#socialHelpWhatsApp" aria-expanded="false" aria-controls="socialHelpWhatsApp">
                        <span class="fa fa-question-circle mr-1"></span> {{ __('How to connect & get keys') }}
                    </button>
                </div>
                <div class="collapse mt-2" id="socialHelpWhatsApp">
                    <div class="small text-muted bg-light p-3 rounded">
                        <strong>{{ __('WhatsApp (Click to Chat & Channel):') }}</strong>
                        <ul class="mb-0 pl-3">
                            <li>{{ __('Phone number:') }} {{ __('Enter your WhatsApp Business number (with country code, e.g. 1234567890). Used for "Chat on WhatsApp" buttons on your listings and profile.') }}</li>
                            <li>{{ __('Channel link:') }} {{ __('Optional. Create a WhatsApp Channel in the WhatsApp app, then paste the channel invite link here.') }}</li>
                            <li><strong>{{ __('Receive & Reply (API):') }}</strong> {{ __('To receive messages and reply from the panel, add WhatsApp Business API credentials: go to') }} <a href="https://developers.facebook.com/docs/whatsapp/cloud-api" target="_blank" rel="noopener">WhatsApp Cloud API</a>, {{ __('create an app, add WhatsApp product, get Phone Number ID and Business Account ID from the app dashboard, and generate a permanent token. Set the webhook URL in Meta to your site') }} <code>{{ rtrim(config('app.url'), '/') }}/api/whatsapp/webhook</code>. {{ __('Admin must set Webhook Verify Token in Basic Settings.') }}</li>
                            <li>{{ __('All settings are saved in your profile (database); nothing is stored in .env.') }}</li>
                        </ul>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6 mb-2">
                        <label class="font-weight-bold">{{ __('WhatsApp phone number') }}</label>
                        <input type="text" class="form-control" name="whatsapp_phone_number" value="{{ old('whatsapp_phone_number', $c ? $c->whatsapp_phone_number : '') }}" placeholder="e.g. 1234567890 (with country code)" maxlength="50" autocomplete="off">
                        @error('whatsapp_phone_number')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="font-weight-bold">{{ __('WhatsApp Channel link') }}</label>
                        <input type="url" class="form-control" name="whatsapp_channel_link" value="{{ old('whatsapp_channel_link', $c ? $c->whatsapp_channel_link : '') }}" placeholder="https://whatsapp.com/channel/..." maxlength="500" autocomplete="off">
                        @error('whatsapp_channel_link')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>
                <p class="mb-1 mt-2 small text-muted">{{ __('Receive & Reply (optional):') }}</p>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="font-weight-bold">{{ __('Phone Number ID') }}</label>
                        <input type="text" class="form-control" name="whatsapp_phone_number_id" value="{{ old('whatsapp_phone_number_id', $c ? $c->whatsapp_phone_number_id : '') }}" placeholder="From Meta app dashboard" maxlength="100" autocomplete="off">
                        @error('whatsapp_phone_number_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="font-weight-bold">{{ __('Business Account ID') }}</label>
                        <input type="text" class="form-control" name="whatsapp_business_account_id" value="{{ old('whatsapp_business_account_id', $c ? $c->whatsapp_business_account_id : '') }}" placeholder="From Meta app dashboard" maxlength="100" autocomplete="off">
                        @error('whatsapp_business_account_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="font-weight-bold">{{ __('Access token') }}</label>
                        <input type="password" class="form-control" name="whatsapp_access_token" value="" placeholder="{{ $c && $c->whatsapp_access_token ? '•••••••• (leave blank to keep)' : 'Permanent token' }}" maxlength="2000" autocomplete="new-password">
                        @error('whatsapp_access_token')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>
                @if (\Illuminate\Support\Facades\Schema::hasColumn('user_social_credentials', 'whatsapp_alert_wa_id'))
                <div class="row mt-1">
                    <div class="col-md-12 mb-2">
                        <label class="font-weight-bold">{{ __('Alert WhatsApp number (new lead notifications)') }}</label>
                        <input type="text" class="form-control" name="whatsapp_alert_wa_id" value="{{ old('whatsapp_alert_wa_id', $c ? $c->whatsapp_alert_wa_id : '') }}" placeholder="{{ __('Your personal number to receive new lead alerts') }}" maxlength="50" autocomplete="off">
                        <small class="text-muted">{{ __('Optional. With country code, digits only. You will receive a WhatsApp when a new lead is created.') }}</small>
                        @error('whatsapp_alert_wa_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>
                @endif
            </div>

            <button type="submit" class="btn btn-primary">{{ __('Save credentials') }}</button>
        </form>
    </div>
</div>
