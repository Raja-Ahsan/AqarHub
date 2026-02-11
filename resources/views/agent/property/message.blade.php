@extends('agent.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Property Messages') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('vendor.dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>


            <li class="nav-item">
                <a href="#">{{ __('Property Messages') }}</a>
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

                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (isset($intentCounts) && count($intentCounts) > 0)
                                <form method="get" action="{{ route('agent.property_message.index') }}" class="mb-3 form-inline">
                                    <label class="mr-2">{{ __('Filter by intent') }}:</label>
                                    <select name="intent" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                                        <option value="">{{ __('All') }}</option>
                                        @foreach (['ready_to_buy' => __('Ready to buy'), 'interested' => __('Interested'), 'browsing' => __('Browsing'), 'question' => __('Question'), 'other' => __('Other')] as $val => $label)
                                            <option value="{{ $val }}" {{ request('intent') === $val ? 'selected' : '' }}>{{ $label }} ({{ $intentCounts[$val] ?? 0 }})</option>
                                        @endforeach
                                    </select>
                                </form>
                            @endif
                            @if (!empty($showCampaignUi))
                                <div class="mb-3">
                                    <button type="button" class="btn btn-primary btn-sm" id="sendCampaignOpenBtn" disabled>{{ __('Send update (e.g. price drop)') }}</button>
                                    <span class="ml-2 text-muted small" id="campaignSelectedCount">0 {{ __('selected') }}</span>
                                </div>
                            @endif
                            @if (count($messages) == 0)
                                <h3 class="text-center mt-2">{{ __('NO MESSAGE FOUND') . '!' }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3" id="basic-datatables">
                                        <thead>
                                            <tr>
                                                @if (!empty($showCampaignUi))
                                                    <th scope="col"><input type="checkbox" id="campaignSelectAll" title="{{ __('Select all') }}"></th>
                                                @endif
                                                <th scope="col">#</th>
                                                <th scope="col">{{ __('Property') }}</th>
                                                <th scope="col">{{ __('Name') }}</th>
                                                <th scope="col">{{ __('Email ID') }}</th>
                                                <th scope="col">{{ __('Phone') }}</th>
                                                @if (isset($intentCounts))
                                                    <th scope="col">{{ __('Intent') }}</th>
                                                    <th scope="col">{{ __('Score') }}</th>
                                                @endif
                                                @if (!empty($showReplySentColumn))
                                                    <th scope="col">{{ __('Reply sent') }}</th>
                                                @endif
                                                <th scope="col">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($messages as $message)
                                                <tr>
                                                    @if (!empty($showCampaignUi))
                                                        <td>
                                                            @if(empty($message->unsubscribed_at ?? null))
                                                                <input type="checkbox" class="campaign-lead-cb" value="{{ $message->id }}" data-email="{{ $message->email }}">
                                                            @else
                                                                <span class="text-muted" title="{{ __('Unsubscribed') }}">—</span>
                                                            @endif
                                                        </td>
                                                    @endif
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td class="table-title">
                                                        @php
                                                            $property_content = $message->property?->propertyContent;

                                                        @endphp
                                                        @if (!empty($property_content))
                                                            <a href="{{ route('frontend.property.details', ['slug' => $property_content->slug]) }}"
                                                                target="_blank">
                                                                {{ strlen(@$property_content->title) > 100 ? mb_substr(@$property_content->title, 0, 100, 'utf-8') . '...' : @$property_content->title }}
                                                            </a>
                                                        @endif
                                                    </td>

                                                    <td>{{ $message->name }}</td>
                                                    <td><a href="mailto:{{ $message->email }}">{{ $message->email }}</a>
                                                    </td>
                                                    <td> <a href="tel:{{ $message->phone }}">{{ $message->phone }}</a>
                                                    </td>
                                                    @if (isset($intentCounts))
                                                        <td>
                                                            @if (!empty($message->intent))
                                                                <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $message->intent)) }}</span>
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (isset($message->lead_score))
                                                                <span class="badge badge-{{ $message->lead_score >= 8 ? 'success' : ($message->lead_score >= 5 ? 'primary' : 'secondary') }}">{{ $message->lead_score }}/10</span>
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </td>
                                                    @endif
                                                    @if (!empty($showReplySentColumn))
                                                        <td>
                                                            @if (!empty($message->reply_email_sent) || !empty($message->reply_sent_at))
                                                                <span class="badge badge-success"><i class="fas fa-check"></i> {{ __('Yes') }}</span>
                                                            @else
                                                                <span class="text-muted">—</span>
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
                                                                    data-property-id="{{ $message->property_id ?? '' }}">
                                                                    <span class="btn-label">
                                                                        <i class="fas fa-eye"></i> {{ __('View') }}
                                                                    </span>
                                                                </a>

                                                                <form class="deleteForm d-inline-block dropdown-item"
                                                                    action="{{ route('agent.property_message.delete') }}"
                                                                    method="post">
                                                                    @csrf
                                                                    <input type="hidden" name="message_id"
                                                                        value="{{ $message->id }}">

                                                                    <button type="submit" class="p-0 deleteBtn">
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

    @include('agent.property.message-view')

    @if (!empty($showCampaignUi))
    <div class="modal fade" id="sendCampaignModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Send update to leads') }}</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ __('Update type') }}</label>
                        <select class="form-control" id="campaignType">
                            <option value="price_drop">{{ __('Price drop') }}</option>
                            <option value="new_listing">{{ __('New listing') }}</option>
                            <option value="general_update">{{ __('General update') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ __('Property (optional)') }}</label>
                        <select class="form-control" id="campaignPropertyId">
                            <option value="">{{ __('— None / general —') }}</option>
                            @foreach ($agentProperties ?? [] as $p)
                                @php $cont = $p->propertyContents->first(); @endphp
                                <option value="{{ $p->id }}">{{ $cont ? \Illuminate\Support\Str::limit($cont->title, 50) : __('Property') }} #{{ $p->id }}</option>
                            @endforeach
                        </select>
                    </div>
                    <p class="text-muted small mb-0">{{ __('AI will generate a personalized email per lead. Emails are queued and sent shortly. Unsubscribed leads are excluded.') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="sendCampaignSubmitBtn">{{ __('Send to selected leads') }}</button>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@section('script')
@if (!empty($showCampaignUi))
<script>
document.addEventListener('DOMContentLoaded', function() {
    var campaignSelectAll = document.getElementById('campaignSelectAll');
    var campaignLeadCbs = document.querySelectorAll('.campaign-lead-cb');
    var campaignSelectedCount = document.getElementById('campaignSelectedCount');
    var sendCampaignOpenBtn = document.getElementById('sendCampaignOpenBtn');
    function updateCampaignSelection() {
        var n = document.querySelectorAll('.campaign-lead-cb:checked').length;
        if (campaignSelectedCount) campaignSelectedCount.textContent = n + ' {{ __("selected") }}';
        if (sendCampaignOpenBtn) sendCampaignOpenBtn.disabled = n === 0;
        if (campaignSelectAll) campaignSelectAll.checked = n > 0 && n === campaignLeadCbs.length;
    }
    if (campaignSelectAll) {
        campaignSelectAll.addEventListener('change', function() {
            campaignLeadCbs.forEach(function(cb) { cb.checked = campaignSelectAll.checked; });
            updateCampaignSelection();
        });
    }
    campaignLeadCbs.forEach(function(cb) {
        cb.addEventListener('change', updateCampaignSelection);
    });
    if (sendCampaignOpenBtn) {
        sendCampaignOpenBtn.addEventListener('click', function() {
            $('#sendCampaignModal').modal('show');
        });
    }
    var sendCampaignSubmitBtn = document.getElementById('sendCampaignSubmitBtn');
    if (sendCampaignSubmitBtn) {
        sendCampaignSubmitBtn.addEventListener('click', function() {
            var ids = [];
            document.querySelectorAll('.campaign-lead-cb:checked').forEach(function(cb) { ids.push(parseInt(cb.value, 10)); });
            if (ids.length === 0) return;
            var campaignType = (document.getElementById('campaignType') || {}).value || 'general_update';
            var propertyIdEl = document.getElementById('campaignPropertyId');
            var propertyId = propertyIdEl && propertyIdEl.value ? parseInt(propertyIdEl.value, 10) : null;
            sendCampaignSubmitBtn.disabled = true;
            fetch('{{ route("ai.assistant.send_campaign") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                body: JSON.stringify({ lead_ids: ids, campaign_type: campaignType, property_id: propertyId || undefined })
            }).then(function(r) { return r.json(); }).then(function(data) {
                sendCampaignSubmitBtn.disabled = false;
                if (data.success) {
                    $('#sendCampaignModal').modal('hide');
                    if (typeof $.notify === 'function') $.notify({ message: data.message || '{{ __("Campaign queued.") }}', title: '', icon: 'fa fa-check' }, { type: 'success' });
                    document.querySelectorAll('.campaign-lead-cb:checked').forEach(function(cb) { cb.checked = false; });
                    updateCampaignSelection();
                } else {
                    if (typeof $.notify === 'function') $.notify({ message: data.error || '{{ __("Failed.") }}', title: '', icon: 'fa fa-exclamation' }, { type: 'warning' });
                }
            }).catch(function() {
                sendCampaignSubmitBtn.disabled = false;
                if (typeof $.notify === 'function') $.notify({ message: '{{ __("Request failed.") }}', title: '', icon: 'fa fa-exclamation' }, { type: 'danger' });
            });
        });
    }
});
</script>
@endif
@if (config('ai.enabled', false))
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('editModal');
    if (modal) {
        $(modal).on('show.bs.modal', function() {
            var ta = document.getElementById('in_suggested_reply');
            var statusEl = document.getElementById('aiSuggestReplyStatus');
            var sendStatus = document.getElementById('sendReplyStatus');
            if (ta) ta.value = '';
            if (statusEl) statusEl.textContent = '';
            if (sendStatus) sendStatus.textContent = '';
        });
    }
    var sendReplyBtn = document.getElementById('sendReplyBtn');
    var sendReplyStatus = document.getElementById('sendReplyStatus');
    if (sendReplyBtn) {
        sendReplyBtn.addEventListener('click', function() {
            var ta = document.getElementById('in_suggested_reply');
            var messageIdEl = document.getElementById('in_id');
            if (!ta || !ta.value.trim()) {
                if (sendReplyStatus) { sendReplyStatus.textContent = '{{ __("Generate a reply first.") }}'; sendReplyStatus.style.color = '#856404'; }
                return;
            }
            if (!messageIdEl || !messageIdEl.value) {
                if (sendReplyStatus) { sendReplyStatus.textContent = '{{ __("Message not found.") }}'; sendReplyStatus.style.color = '#856404'; }
                return;
            }
            sendReplyBtn.disabled = true;
            if (sendReplyStatus) { sendReplyStatus.textContent = '{{ __("Sending...") }}'; sendReplyStatus.style.color = ''; }
            var formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('message_id', messageIdEl.value);
            formData.append('reply_text', ta.value);
            fetch('{{ route("agent.send_inquiry_reply") }}', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            }).then(function(r) { return r.json(); }).then(function(data) {
                sendReplyBtn.disabled = false;
                if (data.success) {
                    if (sendReplyStatus) { sendReplyStatus.textContent = data.message || '{{ __("Email sent successfully.") }}'; sendReplyStatus.style.color = '#155724'; }
                    var content = { message: data.message || '{{ __("Email sent successfully.") }}', title: 'Success', icon: 'fa fa-check' };
                    if (typeof $.notify === 'function') {
                        $.notify(content, { type: 'success', placement: { from: 'top', align: 'right' }, showProgressbar: true, time: 1000, delay: 4000 });
                    }
                } else {
                    if (sendReplyStatus) { sendReplyStatus.textContent = data.error || '{{ __("Failed to send.") }}'; sendReplyStatus.style.color = '#721c24'; }
                }
            }).catch(function() {
                sendReplyBtn.disabled = false;
                if (sendReplyStatus) { sendReplyStatus.textContent = '{{ __("Request failed.") }}'; sendReplyStatus.style.color = '#721c24'; }
            });
        });
    }
    var btn = document.getElementById('aiSuggestReplyBtn');
    var statusEl = document.getElementById('aiSuggestReplyStatus');
    if (!btn) return;
    btn.addEventListener('click', function() {
        var message = (document.getElementById('in_message') || {}).value || '';
        var name = (document.getElementById('in_name') || {}).value || '';
        var propertyId = (document.getElementById('in_propertyId') || {}).value || '';
        if (!message.trim()) {
            if (statusEl) { statusEl.textContent = '{{ __("Please view a message first.") }}'; }
            return;
        }
        btn.disabled = true;
        if (statusEl) statusEl.textContent = '{{ __("Generating...") }}';
        var formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        formData.append('message', message);
        formData.append('name', name);
        if (propertyId) formData.append('property_id', propertyId);
        fetch('{{ route("ai.assistant.suggest_reply") }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function(r) { return r.json(); }).then(function(data) {
            btn.disabled = false;
            if (statusEl) statusEl.textContent = '';
            if (data.success && data.suggested_reply) {
                var ta = document.getElementById('in_suggested_reply');
                if (ta) ta.value = data.suggested_reply;
            } else {
                if (statusEl) statusEl.textContent = data.error || '{{ __("Could not generate reply.") }}';
            }
        }).catch(function() {
            btn.disabled = false;
            if (statusEl) statusEl.textContent = '{{ __("Request failed.") }}';
        });
    });
});
</script>
@endif
@endsection
