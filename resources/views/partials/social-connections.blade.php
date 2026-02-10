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
        </div>
    </div>
</div>
