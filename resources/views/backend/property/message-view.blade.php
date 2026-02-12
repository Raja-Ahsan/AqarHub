<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Message Details') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">  
                <div class="row no-gutters">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">{{ __('Name') }}</label>
                            <input type="text" id="in_name" class="form-control" readonly>
                            <p id="editErr_username" class="mt-2 mb-0 text-danger em"></p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">{{ __('Phone') }}</label>
                            <input type="text" id="in_phone" class="form-control" readonly>
                            <p id="editErr_first_name" class="mt-2 mb-0 text-danger em"></p>
                        </div>
                    </div>

                </div>

                <div class="row no-gutters">


                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="">{{ __('Email') }}</label>
                            <input type="email" id="in_email" class="form-control" name="email" readonly>
                            <p id="editErr_email" class="mt-2 mb-0 text-danger em"></p>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="">{{ __('Message') }}</label>
                            <textarea rows="4" readonly class="form-control" id="in_message"></textarea>

                        </div>
                    </div>
                    @if (!empty($hasWhatsAppApi))
                    <div class="col-lg-12 mt-2">
                        <div class="form-group">
                            <label for="in_whatsapp_reply">{{ __('Reply via WhatsApp') }}</label>
                            <textarea rows="3" class="form-control" id="in_whatsapp_reply" placeholder="{{ __('Type your reply to send via WhatsApp.') }}"></textarea>
                        </div>
                    </div>
                    @endif
                </div> 
            </div>

            <div class="modal-footer">
                <input type="hidden" id="in_whatsappWaId" value="">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    {{ __('Close') }}
                </button>
                @if (!empty($hasWhatsAppApi))
                <button type="button" id="sendWhatsAppReplyBtn" class="btn btn-success btn-sm" title="{{ __('Reply via WhatsApp (contact must have messaged via WhatsApp)') }}">
                    <i class="fab fa-whatsapp"></i> {{ __('Reply via WhatsApp') }}
                </button>
                <span id="sendWhatsAppReplyStatus" class="ml-2 small"></span>
                @endif
            </div>
        </div>
    </div>
</div>
