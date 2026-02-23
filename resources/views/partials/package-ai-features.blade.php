{{-- Point-wise AI features: show only when admin has enabled AI for this package (has_ai_features). --}}
@if (config('ai.enabled', false) && isset($package) && ($package->has_ai_features ?? false))
    @php $features = config('ai.package_features', []); @endphp
    @if (!empty($features))
        @foreach ($features as $key => $label)
            <li><i class="fal fa-check"></i> {{ __($label) }}</li>
        @endforeach
    @endif
@endif
