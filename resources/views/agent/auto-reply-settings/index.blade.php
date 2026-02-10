@extends('agent.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Auto-Reply Settings') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('agent.dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Auto-Reply Settings') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card-title">{{ __('Inquiry auto-reply') }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8">
                            <p class="text-muted mb-4">
                                {{ __('If you do not reply to a customer inquiry within the set time, an automatic reply email will be sent to the customer.') }}
                            </p>
                            <form action="{{ route('agent.auto_reply_settings.update') }}" method="post">
                                @csrf
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="auto_reply_enabled"
                                            name="auto_reply_enabled" value="1" {{ old('auto_reply_enabled', $agent->auto_reply_enabled ?? 0) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="auto_reply_enabled">
                                            {{ __('Enable auto-reply') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="auto_reply_after_hours">{{ __('Send auto-reply after (hours)') }} <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="auto_reply_after_hours"
                                        name="auto_reply_after_hours" min="1" max="168" step="1"
                                        value="{{ old('auto_reply_after_hours', $agent->auto_reply_after_hours ?? 2) }}"
                                        placeholder="2">
                                    <small class="form-text text-muted">{{ __('If you do not reply within this many hours, the auto-reply will be sent. (1–168 hours, e.g. 2 = 2 hours)') }}</small>
                                    @error('auto_reply_after_hours')
                                        <p class="text-danger small mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="auto_reply_message">{{ __('Auto-reply message (optional)') }}</label>
                                    <textarea class="form-control" id="auto_reply_message" name="auto_reply_message" rows="5"
                                        placeholder="{{ __('Leave blank to use default: "Thank you for your inquiry. We have received your message and will get back to you as soon as possible."') }}">{{ old('auto_reply_message', $agent->auto_reply_message ?? '') }}</textarea>
                                    <small class="form-text text-muted">{{ __('This message will be sent to the customer. If empty, a default message is used.') }}</small>
                                    @error('auto_reply_message')
                                        <p class="text-danger small mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary">{{ __('Save settings') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
