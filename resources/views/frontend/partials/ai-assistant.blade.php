@if(config('ai.enabled') && (isset($basicInfo->ai_assistant_status) ? (int)$basicInfo->ai_assistant_status === 1 : true))
<div id="ai-assistant-widget" class="ai-assistant-widget">
  <button type="button" id="ai-assistant-toggle" class="ai-assistant-toggle" aria-label="Open AI Assistant">
    <span class="ai-assistant-icon" aria-hidden="true">💬</span>
  </button>
  <div id="ai-assistant-panel" class="ai-assistant-panel" hidden>
    <div class="ai-assistant-header">
      <span class="ai-assistant-title">AI Assistant</span>
      <button type="button" id="ai-assistant-close" class="ai-assistant-close" aria-label="Close">&times;</button>
    </div>
    <div class="ai-assistant-messages" id="ai-assistant-messages">
      <div class="ai-assistant-welcome">
        Hi! I'm your {{ config('app.name') }} assistant. Ask me about properties, search tips, or anything about the platform.
      </div>
    </div>
    <div class="ai-assistant-quick-search">
      <button type="button" id="ai-assistant-find-btn" class="ai-assistant-find-btn">🔍 {{ __('Find properties') }}</button>
      <div id="ai-assistant-search-wrap" class="ai-assistant-search-wrap" hidden>
        <input type="text" id="ai-assistant-search-input" class="ai-assistant-search-input" placeholder="e.g. 3 bed under 500k" maxlength="200">
        <button type="button" id="ai-assistant-search-go" class="ai-assistant-send">Go</button>
      </div>
    </div>
    <div class="ai-assistant-input-wrap">
      <textarea id="ai-assistant-input" class="ai-assistant-input" rows="2" placeholder="Type your question..." maxlength="2000"></textarea>
      <button type="button" id="ai-assistant-send" class="ai-assistant-send">Send</button>
    </div>
  </div>
</div>
<style>
.ai-assistant-widget { position: fixed; bottom: 24px; right: 24px; z-index: 9999; font-family: inherit; }
.ai-assistant-toggle { width: 56px; height: 56px; border-radius: 50%; border: none; background: var(--color-primary, #BDA588); color: #fff; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,.2); display: flex; align-items: center; justify-content: center; font-size: 24px; transition: transform .2s; }
.ai-assistant-toggle:hover { transform: scale(1.05); }
.ai-assistant-panel { position: absolute; bottom: 70px; right: 0; width: 380px; max-width: calc(100vw - 48px); height: 480px; max-height: 70vh; background: #fff; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,.15); display: flex; flex-direction: column; overflow: hidden; }
.ai-assistant-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; background: var(--color-primary, #BDA588); color: #fff; }
.ai-assistant-title { font-weight: 600; font-size: 1rem; }
.ai-assistant-close { background: none; border: none; color: inherit; font-size: 24px; cursor: pointer; line-height: 1; padding: 0 4px; opacity: .9; }
.ai-assistant-close:hover { opacity: 1; }
.ai-assistant-messages { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 12px; font-size: 14px; }
.ai-assistant-welcome { color: #666; padding: 10px; background: #f5f5f5; border-radius: 8px; }
.ai-assistant-msg { max-width: 85%; padding: 10px 12px; border-radius: 10px; }
.ai-assistant-msg.user { align-self: flex-end; background: var(--color-primary, #BDA588); color: #fff; }
.ai-assistant-msg.assistant { align-self: flex-start; background: #f0f0f0; color: #333; }
.ai-assistant-msg.error { align-self: flex-start; background: #fee; color: #c00; }
.ai-assistant-typing { align-self: flex-start; padding: 10px 12px; color: #666; font-style: italic; }
.ai-assistant-input-wrap { padding: 12px; border-top: 1px solid #eee; display: flex; gap: 8px; align-items: flex-end; }
.ai-assistant-input { flex: 1; resize: none; padding: 10px 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; font-family: inherit; }
.ai-assistant-send { padding: 10px 16px; background: var(--color-primary, #BDA588); color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 500; }
.ai-assistant-send:hover { opacity: .9; }
.ai-assistant-send:disabled { opacity: .6; cursor: not-allowed; }
.ai-assistant-quick-search { padding: 8px 16px; border-top: 1px solid #eee; }
.ai-assistant-find-btn { width: 100%; padding: 8px 12px; font-size: 13px; border: 1px dashed var(--color-primary, #BDA588); background: #fff; color: var(--color-primary, #BDA588); border-radius: 8px; cursor: pointer; }
.ai-assistant-find-btn:hover { background: #f9f9f9; }
.ai-assistant-search-wrap { display: flex; gap: 6px; margin-top: 8px; }
.ai-assistant-search-input { flex: 1; padding: 8px 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; }
</style>
<script>
(function() {
  var panel = document.getElementById('ai-assistant-panel');
  var toggle = document.getElementById('ai-assistant-toggle');
  var closeBtn = document.getElementById('ai-assistant-close');
  var messagesEl = document.getElementById('ai-assistant-messages');
  var input = document.getElementById('ai-assistant-input');
  var sendBtn = document.getElementById('ai-assistant-send');
  var chatUrl = '{{ route("ai.assistant.chat") }}';
  var csrf = '{{ csrf_token() }}';
  var history = [];

  function showPanel() { panel.removeAttribute('hidden'); }
  function hidePanel() { panel.setAttribute('hidden', ''); }
  function togglePanel() { panel.hasAttribute('hidden') ? showPanel() : hidePanel(); }

  toggle && toggle.addEventListener('click', togglePanel);
  closeBtn && closeBtn.addEventListener('click', hidePanel);

  var bannerOpenBtn = document.getElementById('banner-open-ai-assistant');
  if (bannerOpenBtn) bannerOpenBtn.addEventListener('click', function() { showPanel(); if (input) input.focus(); });

  function addBubble(text, role) {
    var div = document.createElement('div');
    div.className = 'ai-assistant-msg ' + role;
    div.textContent = text;
    messagesEl.appendChild(div);
    messagesEl.scrollTop = messagesEl.scrollHeight;
    return div;
  }

  function setTyping(show) {
    var el = document.querySelector('.ai-assistant-typing');
    if (show && !el) {
      el = document.createElement('div');
      el.className = 'ai-assistant-typing';
      el.textContent = 'Thinking...';
      messagesEl.appendChild(el);
      messagesEl.scrollTop = messagesEl.scrollHeight;
    } else if (!show && el) el.remove();
  }

  function sendMessage() {
    var msg = (input && input.value) ? input.value.trim() : '';
    if (!msg) return;
    input.value = '';
    addBubble(msg, 'user');
    history.push({ role: 'user', content: msg });
    sendBtn.disabled = true;
    setTyping(true);

    fetch(chatUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
      body: JSON.stringify({ message: msg, history: history })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      setTyping(false);
      if (data.success) {
        addBubble(data.message, 'assistant');
        history.push({ role: 'assistant', content: data.message });
      } else {
        addBubble(data.error || 'Something went wrong.', 'error');
      }
    })
    .catch(function() {
      setTyping(false);
      addBubble('Unable to connect. Please try again.', 'error');
    })
    .finally(function() { sendBtn.disabled = false; });
  }

  sendBtn && sendBtn.addEventListener('click', sendMessage);
  input && input.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
  });

  var findBtn = document.getElementById('ai-assistant-find-btn');
  var searchWrap = document.getElementById('ai-assistant-search-wrap');
  var searchInput = document.getElementById('ai-assistant-search-input');
  var searchGo = document.getElementById('ai-assistant-search-go');
  var searchUrl = '{{ route("ai.assistant.search") }}';
  if (findBtn && searchWrap && searchInput && searchGo) {
    findBtn.addEventListener('click', function() {
      var hidden = searchWrap.getAttribute('hidden');
      if (hidden) { searchWrap.removeAttribute('hidden'); searchInput.focus(); }
      else { searchWrap.setAttribute('hidden', ''); }
    });
    function doSearch() {
      var q = (searchInput.value || '').trim();
      if (!q) return;
      findBtn.disabled = true;
      fetch(searchUrl + '?q=' + encodeURIComponent(q)).then(function(r) { return r.json(); })
        .then(function(data) {
          if (data.success && data.url) window.location = data.url;
          else if (data.error) addBubble(data.error, 'error');
        })
        .catch(function() { addBubble('Search failed. Try again.', 'error'); })
        .finally(function() { findBtn.disabled = false; });
    }
    searchGo.addEventListener('click', doSearch);
    searchInput.addEventListener('keydown', function(e) { if (e.key === 'Enter') doSearch(); });
  }
})();
</script>
@endif
