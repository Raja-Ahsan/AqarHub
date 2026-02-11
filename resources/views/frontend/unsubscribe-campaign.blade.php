<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Unsubscribe') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/front/css/style.css') }}">
</head>
<body>
    <div class="error-area">
        <div>
            <div class="text-center py-5">
                @if(!empty($success))
                    <p class="text-success font-weight-bold">{{ __('You have been unsubscribed.') }}</p>
                @else
                    <p class="text-muted">{{ __('Invalid or expired unsubscribe link.') }}</p>
                @endif
                @if(!empty($message))
                    <p class="mt-2">{{ $message }}</p>
                @endif
                <a href="{{ url('/') }}" class="btn btn-primary mt-3">{{ __('Back to home') }}</a>
            </div>
        </div>
    </div>
</body>
</html>
