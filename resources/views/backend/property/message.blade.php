@extends('backend.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Messages') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>


            <li class="nav-item">
                <a href="#">{{ __('Messages') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card-title d-inline-block">{{ __('All Message') }}</div>
                        </div>
                        @if (!empty($whatsappBroadcastAvailable))
                        <div class="col-lg-8 text-right">
                            <a href="{{ route('admin.whatsapp_broadcast.form') }}" class="btn btn-success btn-sm"><i class="fab fa-whatsapp"></i> {{ __('WhatsApp broadcast') }}</a>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($messages) == 0)
                                <h3 class="text-center mt-2">{{ __('NO MESSAGE FOUND') . '!' }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3" id="basic-datatables">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">{{ __('Property') }}</th>
                                                <th scope="col">{{ __('Name') }}</th>
                                                <th scope="col">{{ __('Email ID') }}</th>

                                                <th scope="col">{{ __('Phone') }}</th>
                                                @if (\Illuminate\Support\Facades\Schema::hasColumn('property_contacts', 'source'))
                                                <th scope="col">{{ __('Source') }}</th>
                                                @endif
                                                <th scope="col">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($messages as $message)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>

                                                    <td class="table-title">
                                                        @php
                                                            $property_content = $message->property->propertyContent;
                                                            if (is_null($property_content)) {
                                                                $property_content = $property
                                                                    ->propertyContents()
                                                                    ->first();
                                                            }
                                                        @endphp
                                                        @if (!empty($property_content))
                                                            <a href="{{ route('frontend.property.details', ['slug' => $property_content->slug]) }}"
                                                                target="_blank">
                                                                {{ strlen(@$property_content->title) > 100 ? mb_substr(@$property_content->title, 0, 1000, 'utf-8') . '...' : @$property_content->title }}
                                                            </a>
                                                        @endif
                                                    </td>

                                                    <td>{{ $message->name }}</td>
                                                    <td><a href="mailto:{{ $message->email }}">{{ $message->email }}</a>
                                                    </td>
                                                    <td> <a href="tel:{{ $message->phone }}">{{ $message->phone }}</a>
                                                    </td>
                                                    @if (\Illuminate\Support\Facades\Schema::hasColumn('property_contacts', 'source'))
                                                    <td>
                                                        @if (($message->source ?? '') === 'whatsapp')
                                                            <span class="badge badge-success"><i class="fab fa-whatsapp"></i> WhatsApp</span>
                                                        @else
                                                            <span class="badge badge-secondary">{{ __('Website') }}</span>
                                                        @endif
                                                    </td>
                                                    @endif
                                                    <td>
                                                        <div class="dropdown">
                                                            <button class="btn btn-secondary dropdown-toggle btn-sm"
                                                                type="button" id="dropdownMenuButton"
                                                                data-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false">
                                                                {{ __('Select') }}
                                                            </button>

                                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                                                                <a class="dropdown-item editBtn" href="#"
                                                                    data-toggle="modal" data-target="#editModal"
                                                                    data-id="{{ $message->id }}"
                                                                    data-name="{{ $message->name }}"
                                                                    data-phone="{{ $message->phone }}"
                                                                    data-message="{{ $message->message }}"
                                                                    data-email="{{ $message->email }}"
                                                                    data-whatsapp-wa-id="{{ $message->whatsapp_wa_id ?? '' }}">
                                                                    <span class="btn-label">
                                                                        <i class="fas fa-eye"></i> {{ __('View') }}
                                                                    </span>
                                                                </a>
                                                                <form class="deleteForm d-inline-block dropdown-item p-0"
                                                                    action="{{ route('admin.property_message.destroy') }}"
                                                                    method="post">
                                                                    @csrf
                                                                    <input type="hidden" name="message_id"
                                                                        value="{{ $message->id }}">

                                                                    <button type="submit" class=" deleteBtn">
                                                                        <span class="btn-label">
                                                                            <i class="fas fa-trash-alt"></i>
                                                                            {{ __('Delete') }}
                                                                        </span>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-footer"></div>
            </div>
        </div>
    </div>

    {{-- create modal --}}

    {{-- edit modal --}}
    @include('backend.property.message-view')
@endsection

@if (!empty($hasWhatsAppApi))
@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var sendWhatsAppReplyBtn = document.getElementById('sendWhatsAppReplyBtn');
    var sendWhatsAppReplyStatus = document.getElementById('sendWhatsAppReplyStatus');
    if (sendWhatsAppReplyBtn) {
        sendWhatsAppReplyBtn.addEventListener('click', function() {
            var messageIdEl = document.getElementById('in_id');
            var replyTa = document.getElementById('in_whatsapp_reply');
            var waIdEl = document.getElementById('in_whatsappWaId');
            var replyText = replyTa && replyTa.value ? replyTa.value.trim() : '';
            if (!replyText) {
                if (sendWhatsAppReplyStatus) { sendWhatsAppReplyStatus.textContent = '{{ __("Enter a reply message first.") }}'; sendWhatsAppReplyStatus.style.color = '#856404'; }
                return;
            }
            if (!messageIdEl || !messageIdEl.value) {
                if (sendWhatsAppReplyStatus) { sendWhatsAppReplyStatus.textContent = '{{ __("Message not found.") }}'; sendWhatsAppReplyStatus.style.color = '#856404'; }
                return;
            }
            if (!waIdEl || !waIdEl.value) {
                if (sendWhatsAppReplyStatus) { sendWhatsAppReplyStatus.textContent = '{{ __("This contact was not reached via WhatsApp.") }}'; sendWhatsAppReplyStatus.style.color = '#856404'; }
                return;
            }
            sendWhatsAppReplyBtn.disabled = true;
            if (sendWhatsAppReplyStatus) { sendWhatsAppReplyStatus.textContent = '{{ __("Sending...") }}'; sendWhatsAppReplyStatus.style.color = ''; }
            var formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('message_id', messageIdEl.value);
            formData.append('reply_text', replyText);
            fetch('{{ route("admin.send_whatsapp_reply") }}', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            }).then(function(r) { return r.json(); }).then(function(data) {
                sendWhatsAppReplyBtn.disabled = false;
                if (data.success) {
                    if (sendWhatsAppReplyStatus) { sendWhatsAppReplyStatus.textContent = data.message || '{{ __("WhatsApp message sent.") }}'; sendWhatsAppReplyStatus.style.color = '#155724'; }
                    if (typeof $.notify === 'function') $.notify({ message: data.message || '{{ __("WhatsApp message sent.") }}', title: '', icon: 'fa fa-check' }, { type: 'success' });
                } else {
                    if (sendWhatsAppReplyStatus) { sendWhatsAppReplyStatus.textContent = data.error || '{{ __("Failed to send.") }}'; sendWhatsAppReplyStatus.style.color = '#721c24'; }
                }
            }).catch(function() {
                sendWhatsAppReplyBtn.disabled = false;
                if (sendWhatsAppReplyStatus) { sendWhatsAppReplyStatus.textContent = '{{ __("Request failed.") }}'; sendWhatsAppReplyStatus.style.color = '#721c24'; }
            });
        });
    }
});
</script>
@endsection
@endif
