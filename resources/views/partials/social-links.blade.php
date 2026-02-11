{{-- Social profile links (URLs) – optional per-user. Pass: social_link (model or null), social_links_update_route (e.g. admin.social_links.update) --}}
<div class="card mt-3">
    <div class="card-header">
        <div class="card-title">{{ __('Social Profile Links') }}</div>
        <p class="mb-0 small text-muted">{{ __('Optional links to your public profiles. These can be shown on your profile or listings.') }}</p>
    </div>
    <div class="card-body">
        <form action="{{ route($social_links_update_route) }}" method="post">
            @csrf
            @php
                $link = $social_link ?? null;
            @endphp
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold"><span class="fab fa-facebook text-primary mr-1"></span> {{ __('Facebook URL') }}</label>
                    <input type="url" class="form-control" name="facebook_url" value="{{ old('facebook_url', $link ? $link->facebook_url : '') }}" placeholder="https://facebook.com/yourpage" maxlength="500">
                    @error('facebook_url')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold"><span class="fab fa-linkedin text-primary mr-1"></span> {{ __('LinkedIn URL') }}</label>
                    <input type="url" class="form-control" name="linkedin_url" value="{{ old('linkedin_url', $link ? $link->linkedin_url : '') }}" placeholder="https://linkedin.com/in/yourprofile" maxlength="500">
                    @error('linkedin_url')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold"><span class="fab fa-instagram text-danger mr-1"></span> {{ __('Instagram URL') }}</label>
                    <input type="url" class="form-control" name="instagram_url" value="{{ old('instagram_url', $link ? $link->instagram_url : '') }}" placeholder="https://instagram.com/yourprofile" maxlength="500">
                    @error('instagram_url')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold"><span class="fab fa-tiktok text-dark mr-1"></span> {{ __('TikTok URL') }}</label>
                    <input type="url" class="form-control" name="tiktok_url" value="{{ old('tiktok_url', $link ? $link->tiktok_url : '') }}" placeholder="https://tiktok.com/@yourprofile" maxlength="500">
                    @error('tiktok_url')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold"><span class="fab fa-twitter text-info mr-1"></span> {{ __('Twitter / X URL') }}</label>
                    <input type="url" class="form-control" name="twitter_url" value="{{ old('twitter_url', $link ? $link->twitter_url : '') }}" placeholder="https://x.com/yourprofile" maxlength="500">
                    @error('twitter_url')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Save social links') }}</button>
        </form>
    </div>
</div>
