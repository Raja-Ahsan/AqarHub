# AI Assistant – Plan & Advanced Features for AqarHub

This document describes the **AI Assistant** and advanced AI-powered features for the AqarHub real estate project. **Phase 1 and Phase 2 are completed.** Section 9 documents **Advanced AI Plans** (Phase 3 and beyond) with implementation notes.

---

## 1. Overview

| Item | Description |
|------|-------------|
| **Goal** | Add an AI Assistant (chat + advanced features) integrated across frontend and (optionally) admin/vendor panels. |
| **Stack** | Laravel 9, PHP 8.2; external AI APIs (OpenAI recommended); optional queue for heavy tasks. |
| **UX** | Floating chat widget on frontend; optional full-page chat and admin tools. |

---

## 2. Suggested AI Features (by priority)

### Phase 1 – Core (implemented in this guide)
- **AI Chat Assistant** – Floating widget on the site. Users ask questions about properties, search, pricing, areas. Backend calls OpenAI (or similar) with a system prompt that includes your app context (e.g. “You are AqarHub’s assistant…”).

### Phase 2 – Smarter search & content
- **Natural language property search** – e.g. “3 bed near the beach under 500k” → map to filters (bedrooms, location, price) or semantic search if you store embeddings.
- **Property description generator** – In vendor/admin panel: “Generate description” from title + location + features; AI returns SEO-friendly text.
- **Smart recommendations** – “Similar properties” or “You might like” using embeddings or simple rules (same city, same category, similar price).

### Phase 3 – Advanced
- **Lead intent / scoring** – Classify contact form or chat messages (e.g. “ready to buy” vs “just browsing”) for follow-up.
- **Image analysis** – Optional: send property image to vision API for auto-tagging or description hints.
- **Multi-language** – Use AI to translate or localize property content per language.

---

## 3. Architecture (high level)

```
[Browser]  ←→  [Laravel: AiAssistantController]  ←→  [AiAssistantService]
                     ↓                                      ↓
              (optional: queue job)                  [OpenAI / AI API]
                     ↓
              [DB: chat history, optional embeddings]
```

- **Config:** `config/ai.php` + `.env` (e.g. `OPENAI_API_KEY`, `AI_ASSISTANT_ENABLED`).
- **Service:** One main service (e.g. `App\Services\AiAssistantService`) to call the AI API and optionally build context (recent properties, FAQs).
- **Controller:** Handles POST from the chat widget (and future endpoints for search, generate description, etc.).
- **Frontend:** Floating button + chat panel; JS sends message, displays stream or single reply.
- **Optional:** Store chat per session/user in DB; store property embeddings for semantic search later.

---

## 4. Tech choices

| Need | Option | Notes |
|------|--------|------|
| **Chat + text** | OpenAI API (GPT-4o-mini or GPT-4o) | Easiest; great for chat and description generation. |
| **Same API, alternative** | Azure OpenAI, Anthropic Claude | Use if you have enterprise or regional requirements. |
| **Embeddings** | OpenAI `text-embedding-3-small` | For semantic search / “similar properties” later. |
| **Laravel HTTP** | `Illuminate\Support\Facades\Http` | No extra package; use for REST calls to AI API. |

You need an **API key** from the provider (e.g. OpenAI). Never commit it; use `.env` only.

---

## 5. Implementation checklist (Phase 1)

- [x] Add `config/ai.php` (provider, model, enabled flag, system prompt).
- [x] Add `.env` keys: `OPENAI_API_KEY`, `AI_ASSISTANT_ENABLED`.
- [x] Create `App\Services\AiAssistantService` (method to send user message + get reply).
- [x] Create `App\Http\Controllers\FrontEnd\AiAssistantController` and route `POST /ai-assistant/chat`.
- [x] Create frontend: partial `frontend.partials.ai-assistant`; included in layout-v1, layout-v2, layout-v3.
- [x] (Optional) Store chat history in DB per session/user (`ai_chat_messages` table; set `AI_SAVE_CHAT_HISTORY=true`).
- [x] (Optional) Admin toggle to enable/disable widget: **Basic Settings → General Settings → AI Assistant** (Active/Deactive). Requires migration that adds `ai_assistant_status` to `basic_settings`.

### Phase 2 (implemented)

- [x] **Natural language property search** – `GET /ai-assistant/search?q=3+bed+under+500k` returns `{ "success": true, "url": "/properties?beds=3&max=500000", "filters": {...} }`. Use the `url` to redirect users to the properties page with filters applied.
- [x] **Property description generator** – `POST /ai-assistant/generate-description` with `title`, `location`, `features` (optional). Returns `{ "success": true, "description": "..." }`. **Auth:** Only vendor or admin (checked in controller).
- [x] **“Generate with AI” button** – Added to **Vendor** and **Admin** property create/edit forms (next to Description). Uses title + address for the current language; fills the Summernote description field. Shown only when `config('ai.enabled')` is true.

### How to enable the AI Assistant

1. Get an API key from [OpenAI](https://platform.openai.com/api-keys).
2. In `.env` set:
   - `AI_ASSISTANT_ENABLED=true`
   - `OPENAI_API_KEY=sk-your-key-here`
3. Run `php artisan config:clear` (or `config:cache` in production).
4. Run migrations: `php artisan migrate` (adds `ai_assistant_status` to `basic_settings` and creates `ai_chat_messages` table).
5. Reload the site; the floating chat button appears bottom-right. Click to open and chat.
6. (Optional) In **Admin → Basic Settings → General Settings**, use **AI Assistant** to turn the widget **Active** or **Deactive** without changing `.env`.

---

## 6. Security & cost

- **Rate limit** the chat endpoint (e.g. `throttle:60,1` per user/IP).
- **Validate** input length and strip script tags.
- **Never** send sensitive data (passwords, tokens) in prompts.
- **Monitor** API usage; set limits in OpenAI dashboard to control cost.

---

## 7. Where files go (Phase 1)

| Purpose | Path |
|--------|------|
| Config | `config/ai.php` |
| Service | `app/Services/AiAssistantService.php` |
| Controller | `app/Http/Controllers/FrontEnd/AiAssistantController.php` |
| Route | `routes/web.php` (e.g. `POST ai-assistant/chat`) |
| View partial | `resources/views/frontend/partials/ai-assistant.blade.php` |
| Layout | Include partial in `layout-v1`, `layout-v2`, `layout-v3` (or one to start). |

---

## 8. Routes reference

| Method | Route | Purpose |
|--------|--------|---------|
| POST | `/ai-assistant/chat` | Chat message (widget). Body: `message`, `history` (optional). |
| GET | `/ai-assistant/search?q=...` | Natural language search. Returns `url` to properties page with query params. |
| POST | `/ai-assistant/generate-description` | Generate property description. Body: `title`, `location`, `features`. Vendor or admin only. |
| POST | `/ai-assistant/analyze-image` | Analyze property image (vision). Body: `image` (file) or `image_url`. Vendor or admin only. |
| POST | `/ai-assistant/translate` | Translate text. Body: `text`, `target_language`. Vendor or admin only. |

## 9. Advanced AI Plans (Phase 3 and beyond)

The following are planned or optional enhancements. Implement in order of business value.

---

### 9.1 Smart recommendations (“Similar properties” / “You might like”)

| Priority | Description | How to implement |
|----------|-------------|-------------------|
| High | Show “Similar properties” on property detail page | **Rule-based:** Same `category_id`, same `city_id` (or `state_id`), price within ±20%. Query 4–6 properties, exclude current. Add to `PropertyController@details` and pass to view. |
| Medium | “You might like” on homepage for logged-in users | Use last viewed or saved (wishlist) category/city; suggest 3–4 properties. |
| Advanced | Semantic similarity with embeddings | Store embedding per property (OpenAI `text-embedding-3-small` from title + description). New column `embedding` (JSON or vector). On detail page, cosine similarity with other properties; show top 4. Requires migration + job to backfill embeddings. |

**Files to add:** Optional `PropertyRecommendationService`, or inline in `PropertyController@details`. View: `frontend/property/details.blade.php` – add a “Similar properties” section.

**Implemented:** `PropertyController@details` now builds “Similar properties” with same category, same city (or state if no city), price ±20% when set; limit 6. Section title in view set to “Similar Properties”.

---

### 9.2 Lead intent / scoring

| Priority | Description | How to implement |
|----------|-------------|-------------------|
| Medium | Classify contact form or chat message intent | On contact submit or when user sends a chat message, call AI with prompt: “Classify intent: ready_to_buy, interested, browsing, question, other. Reply with one word.” Store result in `contact_messages` or `ai_chat_messages` (add `intent` column). |
| Low | Score 1–10 for “hot” lead | Same flow; ask AI to also output a score. Use in admin/vendor CRM or list views to sort by “hottest” leads. |

**Files to add:** New method `AiAssistantService::classifyIntent(string $message)`. Call from contact form handler or from `AiAssistantController@chat` (optional, after saving message). Migration: add `intent` (and optionally `score`) to `ai_chat_messages` or contact table.

**Implemented:** `AiAssistantService::classifyIntent()` added; migration added `intent` to `ai_chat_messages`. On each user chat message, intent is classified (ready_to_buy, interested, browsing, question, other) and stored with the user message.

---

### 9.3 Natural language search in the chat widget

| Priority | Description | How to implement |
|----------|-------------|-------------------|
| High | When user asks to “find properties…”, return a clickable link | In the **system prompt** for chat, add: “When the user asks to search for properties (e.g. 3 bed under 500k), reply with a short message and include this exact link: [PROPERTIES_SEARCH_URL]. Replace PROPERTIES_SEARCH_URL with: ” + the result of calling your existing `parseSearchQuery` and building the URL. So: in `AiAssistantController@chat` or in the service, detect “search intent” (e.g. user message contains “find”, “search”, “looking for”) and call `parseSearchQuery`; if success, inject into system prompt or append to assistant reply: “You can see results here: [url]”. Or add a second step: after getting the chat reply, if the user message looks like a search, call search endpoint and append the link to the reply. |

**Implementation:** Option A – Enhance system prompt with instruction: “When users ask to find properties, call the search API and include the link in your reply.” (Requires the model to “call” something – not native in chat.) Option B – In the controller, after getting the chat reply, if the message looks like a search (regex or a quick AI classification), call `parseSearchQuery`, build URL, and append to the reply: “Here are matching properties: [url].” Option B is simpler and recommended.

**Implemented:** Option B in `AiAssistantController@chat`: `looksLikePropertySearch()` heuristic detects search-like messages; then `parseSearchQuery` is called and the properties page URL is appended to the assistant reply.

---

### 9.4 Image analysis (vision)

| Priority | Description | How to implement |
|----------|-------------|-------------------|
| Low | Auto-tag or suggest description from property image | When vendor uploads featured image, optionally call OpenAI Vision API (e.g. `gpt-4o` with image URL). Ask: “List 5 short tags for this real estate image” or “One sentence description.” Save as suggestions or auto-fill a “tags” field. |

**Files to add:** New method `AiAssistantService::analyzePropertyImage(string $imageUrl)`. Endpoint `POST /ai-assistant/analyze-image` (vendor/admin only) with `image` (file or URL). Return tags or description snippet. Frontend: optional “Suggest from image” next to featured image in property form.

**Implemented:** `AiAssistantService::analyzePropertyImage()` (base64 or image URL); `POST /ai-assistant/analyze-image`; “Suggest from image” button on **Vendor** and **Admin** property **create** and **edit** forms (thumbnail). On create: uses selected file; on edit: uses new file or current thumbnail URL. Fills default-language description and meta keywords.

---

### 9.5 Multi-language AI (translate / localize)

| Priority | Description | How to implement |
|----------|-------------|-------------------|
| Medium | “Translate with AI” in admin/vendor for property content | New endpoint `POST /ai-assistant/translate` with `text`, `target_language`. Call OpenAI with prompt “Translate the following real estate text to [language]. Keep tone professional.” Use to fill other language tabs from the default language. |
| Low | Auto-translate on property save | When saving a property, if “Translate to all languages” is checked, call translate for each non-default language and save content. |

**Files to add:** `AiAssistantService::translate(string $text, string $targetLanguage)`. Route and controller method; button “Translate to [Language]” in each language tab in property form.

**Implemented:** `AiAssistantService::translate()`; `POST /ai-assistant/translate`; “Translate from default” button on non-default language tabs in **Vendor** and **Admin** property **create** and **edit** forms. Translates default-language title and description into the current tab’s fields.

---

### 9.6 Chat widget: “Search properties” quick action

| Priority | Description | How to implement |
|----------|-------------|-------------------|
| High | In the chat panel, add a quick prompt or button: “Find properties” | Add below the input a chip/button “Find properties (e.g. 3 bed, under 500k)”. On click, either open a small “Search” input and on submit call `GET /ai-assistant/search?q=...` and redirect to `url`, or pre-fill the chat with “I’m looking for 3 bed under 500k” and let the assistant reply (and optionally append the search link via 9.3). |

**Files to add:** In `frontend/partials/ai-assistant.blade.php`, add a small “Search properties” link or button that opens a search input; on submit, fetch `ai-assistant/search?q=...` and set `window.location = data.url`.

**Implemented:** “Find properties” button in the chat panel toggles a search input; on submit, `GET /ai-assistant/search?q=...` is called and the user is redirected to the returned properties URL.

---

### 9.7 Summary: Advanced AI roadmap

| Phase | Feature | Status |
|-------|---------|--------|
| 1 | Chat widget, config, admin toggle, chat history | Done |
| 2 | Natural language search, description generator, “Generate with AI” button | Done |
| 3 | Smart recommendations (rule-based) – **9.1** Similar properties on detail page | **Done** |
| 3 | Search link in chat – **9.3** append search URL when user asks to find properties | **Done** |
| 3 | **9.6** Chat “Find properties” quick action (button + search input, redirect) | **Done** |
| 3 | **9.2** Lead intent classification (classify + store intent on user messages) | **Done** |
| 4 | **9.4** Image analysis (vision) – “Suggest from image” on vendor/admin create & edit | **Done** |
| 4 | **9.5** Multi-language translate – “Translate from default” on vendor/admin create & edit | **Done** |
| 4 | Embeddings + semantic “Similar properties” | Optional |
