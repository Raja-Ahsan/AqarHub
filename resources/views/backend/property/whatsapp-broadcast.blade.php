@extends('backend.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('WhatsApp Broadcast') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item">
                <a href="{{ route('admin.property_message.index') }}">{{ __('Messages') }}</a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item">{{ __('WhatsApp Broadcast') }}</li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">{{ __('Send message to all leads (opted in for WhatsApp)') }}</div>
                </div>
                <div class="card-body">
                    <p class="text-muted">{{ __('This will send your message to :count contact(s) who have a phone number and have opted in for WhatsApp updates.', ['count' => $contactsCount]) }}</p>
                    <form action="{{ route('admin.whatsapp_broadcast.send') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="message">{{ __('Message') }} *</label>
                            <textarea name="message" id="message" class="form-control" rows="5" maxlength="2000" required placeholder="{{ __('Type your broadcast message...') }}">{{ old('message') }}</textarea>
                            <small class="text-muted">{{ __('Max 2000 characters. Long messages may be truncated to 1000 for WhatsApp.') }}</small>
                            @error('message')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary">{{ __('Send broadcast') }}</button>
                        <a href="{{ route('admin.property_message.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
