# AI Roadmap 2026: Vendors & Agents – Complete Details

This document summarizes **what the project already has**, **what the industry expects in 2026**, and **what to build next** for vendors and agents, with priorities and implementation notes.

---

## Part 1: What This Project Already Has (Current State)

### Frontend (Visitors / Buyers)

| Feature | Description | Status |
|--------|-------------|--------|
| **AI Chat Widget** | Floating chat on homepage; natural language Q&A about properties. | ✅ Done |
| **Property Search in Chat** | “3 bed under 500k in Dubai” → parsed to filters, real DB results + link to properties page. | ✅ Done |
| **Property Cards in Chat** | Show listing cards (image, title, price, short desc) inside the chat. | ✅ Done |
| **Property Details in Chat** | “Show details” fetches full property + vendor/agent info. | ✅ Done |
| **Inquiry from Chat** | Form (name, email, phone, message) to submit inquiry; confirmation message. | ✅ Done |
| **Lead Intent** | Each user message classified (ready_to_buy, interested, browsing, question, other) and stored. | ✅ Done |
| **Chat UI** | Dark/light theme toggle, fullscreen, “Near me”, example chips. | ✅ Done |

### Vendor & Admin Panels

| Feature | Description | Status |
|--------|-------------|--------|
| **Generate with AI** | Button next to Description on property/project create & edit. Uses title + location + features → SEO-friendly HTML description + meta keywords + meta description. | ✅ Done |
| **Suggest from Image** | “Suggest from image” on featured image → Vision API returns tags + one-sentence description; fills description/keywords. | ✅ Done |
| **Translate from Default** | “Translate from default” on non-default language tabs → AI translates title/description into that language. | ✅ Done |
| **Package gating** | Packages can include “AI features” (Generate with AI, Suggest from image, Translate). | ✅ Done |

### Technical Stack

- **Config:** `config/ai.php`, `.env` (`OPENAI_API_KEY`, `AI_ASSISTANT_ENABLED`, etc.)
- **Service:** `App\Services\AiAssistantService` (chat, parseSearchQuery, generatePropertyDescription, analyzePropertyImage, translate, classifyIntent)
- **Controller:** `App\Http\Controllers\FrontEnd\AiAssistantController`
- **Routes:** `/ai-assistant/chat`, `/ai-assistant/search`, `/ai-assistant/generate-description`, `/ai-assistant/analyze-image`, `/ai-assistant/translate`, `/ai-assistant/property-details`, `/ai-assistant/inquiry`
- **Models:** `AiChatMessage` (with `intent`), Property, Vendor, Agent, etc.

---

## Part 2: What the Industry Expects in 2026 (Research Summary)

Sources: realtor.com, HousingWire, V7 Labs, McKinsey, Monday.com, RhinoAgents, Dialzara, Extend.ai, Medium/HomeSage, Primotech.

### Market & Adoption

- **~70%+** of real estate companies expected to use AI by 2026; **~97%** of professionals show active interest.
- Real estate AI market: **~$2.9B (2024) → ~$41.5B (2033)**; broader proptech AI toward **~$1.3T by 2034** (high growth).
- **70–90%** time reduction and **95%+** accuracy reported where AI is used; **~5%** of firms have fully achieved AI program goals (big opportunity).

### For Vendors & Agents Specifically

1. **Administrative automation**  
   First drafts for listing descriptions, client emails, social captions, follow-ups. Human review for tone/accuracy.

2. **Research & market analysis**  
   Neighborhood summaries, market trends, school info, buyer/seller FAQs in seconds.

3. **Document processing**  
   Lease abstraction, contracts, mortgage docs: **4–8 hours → minutes**; **95%+** accuracy; 66%+ of CRE firms moving to automation.

4. **Lead generation & CRM**  
   - Response in **seconds** (leads contacted in &lt;5 min are **21x** more likely to convert).  
   - **24/7** qualification (budget, location, timeline, pre-approval).  
   - **Lead scoring** and sync to CRM (Salesforce, HubSpot, Zoho).  
   - Automated follow-up (email, SMS, WhatsApp).  
   - **Meeting/viewing scheduling** (calendar integration).

5. **Content creation**  
   Listings, thumbnails, social posts, video scripts; multiple AI models (GPT, Claude, Gemini) in one workflow.

6. **Predictive analytics & pricing**  
   AI valuation **sub-3%** error; real-time market trends and buyer behavior.

7. **Integration**  
   Listings, CRM, and marketing in **one platform**; less switching between tools.

8. **Multi-channel**  
   Website, WhatsApp, SMS, social (Facebook, Instagram), QR at events.

9. **Voice AI**  
   Voice agents for calls: qualification, viewing booking, FAQ.

10. **Compliance**  
    Fair Housing and local compliance checks on copy and communication.

---

## Part 3: What to Add for 2026 – Prioritized for Vendors & Agents

### Tier 1 – High Impact, Clear Fit With Current Stack

| # | Feature | For | Description | Implementation notes |
|---|--------|-----|-------------|------------------------|
| 1 | **Vendor/Agent dashboard: AI insights** | Vendors, Agents | Small “AI insights” panel: top intents from chat (e.g. “12 ready_to_buy, 30 interested”), link to “View inquiries from chat.” | New dashboard widget; query `ai_chat_messages` by intent; optional by package (e.g. `has_ai_features`). |
| 2 | **Lead scoring (1–10) in addition to intent** | Vendors, Agents, Admin | Extend `classifyIntent` (or new call) to also return a numeric score. Store in `ai_chat_messages` (e.g. `lead_score`). Show in admin/vendor inquiry lists and in dashboard. | `AiAssistantService::classifyIntentAndScore()`; migration add `lead_score`; sort/filter by score. |
| 3 | **Suggested reply / follow-up for inquiries** | Vendors, Agents | On inquiry view (from chat or contact form), button “Suggest reply with AI”: pre-fill a professional reply based on inquiry text and property. | New endpoint e.g. `POST /ai-assistant/suggest-reply` (inquiry_id or message text + property_id); vendor/agent only. |
| 4 | **Social / ad copy from listing** | Vendors, Agents | “Generate social copy” next to property: short Facebook/Instagram/LinkedIn post + hashtags from title, description, price, location. | Reuse `AiAssistantService`; new method `generateSocialCopy(property)`; button on property edit (vendor/admin). |
| 5 | **Bulk “Generate description” for existing listings** | Vendors, Admin | In property list: select multiple properties → “Generate descriptions with AI” (for empty or outdated descriptions). Queue job to avoid timeouts. | Job `GenerateDescriptionsJob`; queue; throttle API; only for accounts with `has_ai_features`. |
| 6 | **Fair Housing / compliance check on description** | Vendors, Agents, Admin | Before or after “Generate with AI”: “Check compliance” → AI flags risky wording (discriminatory language). Optional auto-run on save. | New method e.g. `checkCompliance(string $text): array`; optional column `compliance_flagged` or store last check result. |

### Tier 2 – Strong 2026 Trends (Medium Effort)

| # | Feature | For | Description | Implementation notes |
|---|--------|-----|-------------|------------------------|
| 7 | **AI-suggested listing price** | Vendors, Agents | Optional “Suggest price” on create/edit: input address + basic specs → AI returns a range (e.g. from comparables or external API if available; otherwise “market context” text). | New endpoint; prompt with property attributes + optional comparables from DB (same city/category); clearly “suggestion only.” |
| 8 | **Neighborhood / market one-pager** | Vendors, Agents | “Market snapshot” for a city/area: schools, transport, trends, 2–3 sentences. Used in listing or for client PDF. | New method `getNeighborhoodSummary(city_id or name)`; cache by location; show in property form or reports. |
| 9 | **Automated follow-up reminders** | Vendors, Agents | For chat/inquiry leads: “Send follow-up in 2 days” (email/SMS) with AI-generated short message. | Queue job + notification; template + AI personalization; respect unsubscribe. |
| 10 | **WhatsApp (or SMS) for chat** | Vendors, Agents | Let visitors optionally “Continue on WhatsApp” from chat; same thread context (e.g. link to property). | Integrate WhatsApp Business API or similar; store consent; link from chat UI. |
| 11 | **Meeting / viewing scheduler** | Vendors, Agents | “Book a viewing” in chat or on property page → calendar (e.g. Calendly-style or simple slots). Agent/vendor approves. | Slots table + booking; optional sync to Google Calendar; notify agent/vendor. |
| 12 | **Document summarization (PDF)** | Vendors, Agents | Upload contract/lease PDF → AI summary (key terms, dates, parties). For future document processing. | New endpoint `POST /ai-assistant/summarize-document`; PDF text extraction + chat completion; vendor/agent only. |

### Tier 3 – Differentiators & Longer Term

| # | Feature | For | Description | Implementation notes |
|---|--------|-----|-------------|------------------------|
| 13 | **Semantic “Similar properties”** | Visitors, Vendors | Replace or augment rule-based similar properties with embedding-based similarity (same category/city + vector similarity). | Store embeddings (e.g. `text-embedding-3-small`) for title+description; similarity search; backfill job. |
| 14 | **Agent-facing chatbot** | Agents | Internal bot: “How many leads from Dubai last week?” “Top 3 intents this month.” Uses DB + AI. | New route/UI for agents only; prompts that query DB (aggregates on ai_chat_messages, properties, inquiries). |
| 15 | **Voice input in chat** | Visitors | Speech-to-text in chat (browser API or provider). | Frontend only; existing chat API unchanged. |
| 16 | **Multi-language chat** | Visitors | Detect language or “Language” selector; system prompt + replies in that language; keep search/inquiry flow. | Store preference; adjust system prompt; optional translate of property snippets. |
| 17 | **AI valuation report (PDF)** | Vendors, Agents | “Generate valuation report” → PDF with suggested range, comparables, neighborhood snapshot. | Use suggested price + neighborhood summary + template; PDF export (e.g. Dompdf or similar). |

---

## Part 4: Summary Table – Gaps vs 2026 Needs

| 2026 need | Current state | Suggested action |
|-----------|----------------|------------------|
| Fast lead response | Chat + inquiry in chat | ✅ Good. Add **suggested reply** (Tier 1) and **follow-up reminders** (Tier 2). |
| Lead qualification & scoring | Intent only | Add **lead score 1–10** and **dashboard insights** (Tier 1). |
| Listing & content automation | Generate description, image suggest, translate | Add **social copy**, **bulk description**, **compliance check** (Tier 1). |
| Market/neighborhood info | None | Add **neighborhood/market snapshot** (Tier 2). |
| Pricing help | None | Add **AI-suggested price** (Tier 2) with clear “suggestion only.” |
| Document handling | None | Add **document summarization** (Tier 2); later full doc processing. |
| Scheduling | None | Add **viewing/meeting scheduler** (Tier 2). |
| Multi-channel | Website chat only | Add **WhatsApp** (or SMS) link (Tier 2). |
| CRM integration | Inquiries stored, intent stored | Expose **intent + score** in lists; optional export/API for CRM (Tier 1–2). |
| Compliance | None | Add **Fair Housing/compliance check** (Tier 1). |

---

## Part 5: Recommended Order of Implementation

**Phase A (Quick wins)**  
1. Lead scoring (1–10) + dashboard intent summary (Tier 1 #1–2).  
2. Suggested reply for inquiries (Tier 1 #3).  
3. Fair Housing/compliance check on description (Tier 1 #6).

**Phase B (Content & efficiency)**  
4. Social/ad copy generator (Tier 1 #4).  
5. Bulk “Generate description” job (Tier 1 #5).  
6. AI-suggested listing price (Tier 2 #7).

**Phase C (Engagement & ops)**  
7. Neighborhood/market snapshot (Tier 2 #8).  
8. Viewing/meeting scheduler (Tier 2 #11).  
9. Automated follow-up reminders (Tier 2 #9).  
10. WhatsApp or SMS option (Tier 2 #10).

**Phase D (Advanced)**  
11. Document summarization (Tier 2 #12).  
12. Semantic similar properties (Tier 3 #13).  
13. Agent-facing analytics bot (Tier 3 #14).

---

## Part 6: Technical & Cost Notes

- **API usage:** New features will increase OpenAI (or other provider) calls. Use **queues** for bulk and heavy tasks; **cache** neighborhood/market and compliance checks where possible; set **rate limits** and **per-user/per-role limits** for vendor/agent AI.
- **Permissions:** All new AI endpoints for vendors/agents must respect **package** (`has_ai_features`) and **role** (vendor vs agent vs admin).
- **Logging:** Keep logging for AI errors and usage (by feature and user type) for cost and debugging.
- **Compliance:** Store that “AI suggested” and “compliance check” are advisory; final responsibility stays with the user.

---

*Document generated for the ai-property project (2026 roadmap for vendors and agents). Align with `AI-ASSISTANT-PLAN.md` for existing implementation details. aaed new point*


