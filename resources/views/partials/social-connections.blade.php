{{-- Include in edit-profile views. Pass: social_redirect_route, social_disconnect_route, social_connections --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">{{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
@endif
<div class="card mt-3">
    <div class="card-header">
        <div class="card-title">{{ __('Social Media Connections') }}</div>
        <p class="mb-0 small text-muted">{{ __('Connect accounts to post generated social copy (e.g. from "Generate social copy" on property) directly to your pages.') }}</p>
    </div>
    <div class="card-body">
        @php
            $connected = $social_connections->keyBy('platform');
        @endphp
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center justify-content-between border rounded p-3">
                    <div>
                        <span class="fab fa-facebook fa-2x text-primary mr-2"></span>
                        <strong>Facebook</strong>
                        @if($connected->has('facebook'))
                            <br><small class="text-muted">{{ $connected->get('facebook')->platform_username }}</small>
                            @if($connected->get('facebook')->isExpired())
                                <span class="badge badge-warning">{{ __('Token expired') }}</span>
                            @endif
                        @endif
                    </div>
                    @if($connected->has('facebook') && !$connected->get('facebook')->isExpired())
                        <form method="post" action="{{ route($social_disconnect_route, ['platform' => 'facebook']) }}" class="d-inline" onsubmit="return confirm('{{ __('Disconnect Facebook?') }}');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Disconnect') }}</button>
                        </form>
                    @else
                        <a href="{{ route($social_redirect_route, ['driver' => 'facebook']) }}" class="btn btn-sm btn-primary">{{ __('Connect') }}</a>
                    @endif
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center justify-content-between border rounded p-3">
                    <div>
                        <span class="fab fa-linkedin fa-2x text-primary mr-2"></span>
                        <strong>LinkedIn</strong>
                        @if($connected->has('linkedin'))
                            <br><small class="text-muted">{{ $connected->get('linkedin')->platform_username }}</small>
                            @if($connected->get('linkedin')->isExpired())
                                <span class="badge badge-warning">{{ __('Token expired') }}</span>
                            @endif
                        @endif
                    </div>
                    @if($connected->has('linkedin') && !$connected->get('linkedin')->isExpired())
                        <form method="post" action="{{ route($social_disconnect_route, ['platform' => 'linkedin']) }}" class="d-inline" onsubmit="return confirm('{{ __('Disconnect LinkedIn?') }}');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Disconnect') }}</button>
                        </form>
                    @else
                        <a href="{{ route($social_redirect_route, ['driver' => 'linkedin']) }}" class="btn btn-sm btn-primary">{{ __('Connect') }}</a>
                    @endif
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center justify-content-between border rounded p-3">
                    <div>
                        <span class="fab fa-instagram fa-2x text-danger mr-2"></span>
                        <strong>Instagram</strong>
                        @if($connected->has('instagram'))
                            <br><small class="text-muted">{{ $connected->get('instagram')->platform_username }}</small>
                            @if($connected->get('instagram')->isExpired())
                                <span class="badge badge-warning">{{ __('Token expired') }}</span>
                            @endif
                        @endif
                    </div>
                    @if($connected->has('instagram') && !$connected->get('instagram')->isExpired())
                        <form method="post" action="{{ route($social_disconnect_route, ['platform' => 'instagram']) }}" class="d-inline" onsubmit="return confirm('{{ __('Disconnect Instagram?') }}');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Disconnect') }}</button>
                        </form>
                    @else
                        <a href="{{ route($social_redirect_route, ['driver' => 'instagram']) }}" class="btn btn-sm btn-primary">{{ __('Connect') }}</a>
                    @endif
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center justify-content-between border rounded p-3">
                    <div>
                        <span class="fab fa-tiktok fa-2x text-dark mr-2"></span>
                        <strong>TikTok</strong>
                        @if($connected->has('tiktok'))
                            <br><small class="text-muted">{{ $connected->get('tiktok')->platform_username }}</small>
                            @if($connected->get('tiktok')->isExpired())
                                <span class="badge badge-warning">{{ __('Token expired') }}</span>
                            @endif
                        @endif
                    </div>
                    @if($connected->has('tiktok') && !$connected->get('tiktok')->isExpired())
                        <form method="post" action="{{ route($social_disconnect_route, ['platform' => 'tiktok']) }}" class="d-inline" onsubmit="return confirm('{{ __('Disconnect TikTok?') }}');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Disconnect') }}</button>
                        </form>
                    @else
                        <a href="{{ route($social_redirect_route, ['driver' => 'tiktok']) }}" class="btn btn-sm btn-primary">{{ __('Connect') }}</a>
                    @endif
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center justify-content-between border rounded p-3">
                    <div>
                        <span class="fab fa-twitter fa-2x text-info mr-2"></span>
                        <strong>Twitter | X</strong>
                        @if($connected->has('twitter'))
                            <br><small class="text-muted">{{ $connected->get('twitter')->platform_username }}</small>
                            @if($connected->get('twitter')->isExpired())
                                <span class="badge badge-warning">{{ __('Token expired') }}</span>
                            @endif
                        @endif
                    </div>
                    @if($connected->has('twitter') && !$connected->get('twitter')->isExpired())
                        <form method="post" action="{{ route($social_disconnect_route, ['platform' => 'twitter']) }}" class="d-inline" onsubmit="return confirm('{{ __('Disconnect Twitter / X?') }}');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Disconnect') }}</button>
                        </form>
                    @else
                        <a href="{{ route($social_redirect_route, ['driver' => 'twitter']) }}" class="btn btn-sm btn-primary">{{ __('Connect') }}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
