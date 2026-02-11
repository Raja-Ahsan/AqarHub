# AI 2026 – Sequential TODO Integration List

**Rule:** Complete one item → implement → integrate → self-test (100% working) → then move to the next. Do not start the next item until the current one is fully functional and tested in this project.

**Definition of Done (per item):**
- [ ] Backend: service/controller/routes/migrations exist and work
- [ ] Frontend/UI: integrated where specified (vendor / agent / admin / chat)
- [ ] Permissions: respects role (vendor/agent/admin) and package `has_ai_features` where applicable
- [ ] Self-test: manual test performed and passed (no broken flows, errors handled)
- [ ] Document: brief note in "Completed" section with test date/result

---

## Phase A – Quick Wins (Original + A8 Anomaly Detection)

| # | ID | Feature | Scope | Done |
|---|----|---------|-------|------|
| 1 | **A-1** | **Lead scoring (1–10) + dashboard intent summary** | Vendor/Agent dashboard: show counts by intent (ready_to_buy, interested, browsing, question, other); add `lead_score` (1–10) from AI; store in `ai_chat_messages`; sort/filter inquiries by score. | ☑ |
| 2 | **A-2** | **Suggested reply for inquiries** | Vendor/Agent: on inquiry view (from chat or contact), button "Suggest reply with AI" → pre-fill professional reply. Endpoint `POST /ai-assistant/suggest-reply` (inquiry text + optional property_id). | ☑ |
| 3 | **A-3** | **Fair Housing / compliance check on description** | Vendor/Admin property form: "Check compliance" button; AI flags risky wording; store last check result or show warnings only. Method `AiAssistantService::checkCompliance($text)`. | ☑ |
| 4 | **A-8** | **Anomaly detection for listings** | On property save (or before publish): background check – price vs similar properties, required fields, description quality. Flag anomalies in admin/vendor UI (e.g. "Review suggested"). | ☑ |

---

## Phase B – Content & Efficiency (Original + A2 Smart Email, A6 Video Scripts)

| # | ID | Feature | Scope | Done |
|---|----|---------|-------|------|
| 5 | **B-1** | **Social / ad copy from listing** | Vendor/Admin property edit: "Generate social copy" → Facebook/Instagram/LinkedIn post + hashtags. `AiAssistantService::generateSocialCopy()`. | ☑ |
| 6 | **B-2** | **Bulk "Generate description" for existing listings** | Vendor/Admin property list: select multiple → "Generate descriptions with AI" (queue job). Throttle API; only for accounts with `has_ai_features`. | ☑ |
| 7 | **B-3** | **AI-suggested listing price** | Vendor/Admin property form: "Suggest price" (address + specs) → AI returns range + short justification (clearly "suggestion only"). Use comparables from DB where available. | ☑ |
| 8 | **A2** | **Smart email campaigns for agents/vendors** | Select leads by intent/property → "Send update (e.g. price drop)" → AI generates personalized email variants → queue send. Respect opt-out / GDPR/CAN-SPAM; store consent. | ☑ |
| 9 | **A6** | **AI video scripts for listings** | Vendor/Admin: "Generate video script" (TikTok/YouTube/Reels) – 30–60 sec hook, transitions, CTA with timestamps. New method + button on property. | ☐ |

---

## Phase C – Engagement & Ops (Original + A3 Comparison, A5 ROI, B6 Smart Match Push)

| # | ID | Feature | Scope | Done |
|---|----|---------|-------|------|
| 10 | **C-1** | **Neighborhood / market snapshot** | Vendor/Admin: "Market snapshot" for city/area – schools, transport, trends (2–3 sentences). Cache by location; show in property form or report. | ☐ |
| 11 | **C-2** | **Viewing / meeting scheduler** | "Book a viewing" from chat or property page → calendar slots; vendor/agent approves. Slots table + booking; optional calendar sync; notifications. | ☐ |
| 12 | **C-3** | **Automated follow-up reminders** | For chat/inquiry leads: "Send follow-up in X days" with AI-generated short message (email/SMS). Queue job; respect unsubscribe. | ☐ |
| 13 | **C-4** | **WhatsApp or SMS option** | "Continue on WhatsApp" (or SMS) from chat; share property link; store consent. Integrate WhatsApp Business API or SMS provider. | ☐ |
| 14 | **A3** | **Property comparison assistant** | In chat: "Compare these 3 properties" (or user selects) → side-by-side table (price/sqft, location, pros/cons) with AI insights. Chat command + structured comparison response. | ☐ |
| 15 | **A5** | **ROI calculator for investors** | Investment properties: form (price, expected rent, expenses) → AI estimates rental yield, cash flow, short "market insight" paragraph. Optional rental data from DB. | ☐ |
| 16 | **B6** | **Smart property matching push** | When new listing matches a lead's chat/inquiry criteria → auto-notify lead (email/SMS) with AI summary. Background job; match by intent/filters. | ☐ |

---

## Phase D – Advanced (Original + A1 Virtual Tour, A4 Persona, B1 CRM, B2 Voice AI)

| # | ID | Feature | Scope | Done |
|---|----|---------|-------|------|
| 17 | **D-1** | **Document summarization (PDF)** | Vendor/Agent: upload contract/lease PDF → AI summary (key terms, dates, parties). `POST /ai-assistant/summarize-document`; PDF text extraction + completion. | ☐ |
| 18 | **D-2** | **Semantic "Similar properties"** | Replace/augment rule-based similar properties with embedding-based similarity (title+description). Store embeddings; backfill job; similarity search. | ☐ |
| 19 | **D-3** | **Agent-facing analytics bot** | Agent dashboard: internal bot – "How many Dubai leads last week?", "Top intents" – queries DB + AI. Agent-only route/UI. | ☐ |
| 20 | **A1** | **AI-powered virtual tour narration** | Generate voiceover script from property description + images; integrate TTS (e.g. ElevenLabs). Sync with virtual tour if 360° exists; optional "Generate narration" flow. | ☐ |
| 21 | **A4** | **Buyer persona builder** | Agent dashboard: form (budget, lifestyle, work location) → AI suggests property types, neighborhoods, price range, features. | ☐ |
| 22 | **B1** | **Smart CRM sync (two-way)** | Two-way sync with Salesforce/HubSpot/Zoho: leads, scores, intent. OAuth, field mapping, webhooks. Start with one CRM; document API. | ☐ |
| 23 | **B2** | **AI showing assistant (voice + chat)** | Voice AI for calls: qualify leads, book viewings. Integrate Bland.ai, Retell, or Vapi; webhook to your CRM; phone routing. | ☐ |

---

## Phase E – Future / Differentiator (Late 2026+)

| # | ID | Feature | Scope | Done |
|---|----|---------|-------|------|
| 24 | **E-1** | **AI market trend reports** | Weekly/monthly PDF: market trends, top neighborhoods, pricing shifts – branded for agent. Scheduled job; aggregate data + AI. | ☐ |
| 25 | **E-2** | **Emotion / sentiment analysis from inquiries** | Detect urgency, frustration, excitement in chat/email → adjust lead score and optional "tone coach" for replies. | ☐ |
| 26 | **E-3** | **AI interior design / staging suggestions** | Upload property photos → AI suggests staging, colors, furniture (with image gen if applicable). Vendor/agent tool. | ☐ |
| 27 | **E-4** | **Multi-agent AI system** | Separate agents: lead qualification, content, research – orchestrated (e.g. OpenAI Assistants API or internal workflow). | ☐ |
| 28 | **E-5** | **Predictive churn alerts (admin)** | Flag vendors/agents likely to churn (low activity, expiring package) → AI suggests retention actions. Admin only. | ☐ |

---

## Additional Items (Medium Priority – Competitive Edge)

| # | ID | Feature | Scope | Done |
|---|----|---------|-------|------|
| 29 | **B3** | **Predictive "likely to sell" alerts** | For agents: "Properties in your area likely to be listed soon" (market patterns; requires MLS/public data or proxy). | ☐ |
| 30 | **B4** | **AI negotiation coach** | Agent inputs offer details → AI suggests counter-offer range, tips, market justification. Internal use. | ☐ |
| 31 | **B5** | **Content calendar for agents** | "Generate 30 days of social posts" → AI content calendar (property highlights, tips); export CSV/calendar. | ☐ |
| 32 | **B7** | **Expense / commission calculator** | Agent inputs deal details → net commission after splits, taxes, expenses + AI "recommendations" for similar deals. | ☐ |

---

## Future / Optional (Not in Main Sequence)

| # | ID | Feature | Scope | Done |
|---|----|---------|-------|------|
| 33 | **C2** | **Blockchain verification for listings** | AI verifies property/docs against blockchain (anti-fraud). Requires blockchain integration. | ☐ |
| 34 | **Marketplace** | **AI marketplace for agents** | Plugin/app store: optional AI features (e.g. Reels generator, gift recommender, comparable finder). | ☐ |

---

## Implementation Order (Copy This as Your Working Checklist)

Work strictly in this order. Mark done only after self-test.

```
Phase A:
[x] 1. A-1   Lead scoring (1–10) + dashboard intent summary
[x] 2. A-2   Suggested reply for inquiries
[x] 3. A-3   Fair Housing / compliance check
[x] 4. A-8   Anomaly detection for listings

Phase B:
[x] 5. B-1   Social / ad copy from listing
[x] 6. B-2   Bulk generate description (queue)
[x] 7. B-3   AI-suggested listing price
[x] 8. A2    Smart email campaigns
[ ] 9. A6    AI video scripts for listings

Phase C:
[ ] 10. C-1  Neighborhood / market snapshot
[ ] 11. C-2  Viewing / meeting scheduler
[ ] 12. C-3  Automated follow-up reminders
[ ] 13. C-4  WhatsApp or SMS option
[ ] 14. A3   Property comparison assistant
[ ] 15. A5   ROI calculator for investors
[ ] 16. B6   Smart property matching push

Phase D:
[ ] 17. D-1  Document summarization (PDF)
[ ] 18. D-2  Semantic similar properties
[ ] 19. D-3  Agent analytics bot
[ ] 20. A1   Virtual tour narration (TTS)
[ ] 21. A4   Buyer persona builder
[ ] 22. B1   CRM sync (two-way)
[ ] 23. B2   Voice AI agent (calls)

Phase E:
[ ] 24. E-1  AI market trend reports
[ ] 25. E-2  Emotion / sentiment analysis
[ ] 26. E-3  AI staging / design suggestions
[ ] 27. E-4  Multi-agent AI system
[ ] 28. E-5  Predictive churn alerts

Additional (after Phase E or in parallel where noted):
[ ] 29. B3   Predictive "likely to sell"
[ ] 30. B4   AI negotiation coach
[ ] 31. B5   Content calendar
[ ] 32. B7   Expense / commission calculator
```

---

## Completed (Fill After Each Item)

Use this section to record completion and self-test. Only add an entry when the item is 100% working and tested.

| ID | Completed date | Test summary |
|----|----------------|--------------|
| A-1 | 2026-02-03 | Lead score (1–10) stored in ai_chat_messages and property_contacts. classifyIntentAndScore() in AiAssistantService; chat and contactApi save intent+score. Vendor/Agent dashboard: AI Lead Insights card with intent counts + link to messages. Property messages list: sort by lead_score (desc), filter by intent; Intent and Score columns shown. Test: run migrations, enable AI, send chat message and submit inquiry from chat to verify. |
| A-2 | 2026-02-03 | AiAssistantService::suggestReply(); POST /ai-assistant/suggest-reply (vendor/agent/admin, message + optional name + property_id). Vendor and Agent Property Messages: View modal has "Suggested reply" textarea + "Suggest reply with AI" button; reply is pre-filled for copy/paste. |
| A-3 | 2026-02-03 | AiAssistantService::checkCompliance($text); POST /ai-assistant/check-compliance (vendor/admin, description text). Vendor/Admin property create & edit: "Check compliance" button per language tab; AI returns compliant + warnings + summary; result shown in alert box (green = no issues, yellow = warnings list). Vendor requires has_ai_features. |
| A-8 | 2026-02-03 | PropertyAnomalyService::detect() runs in DetectPropertyAnomaliesJob on property store/update (vendor + admin). Checks: required fields (title, address, description, price), description length (min 25 words), price vs similar (city/type). Stored: anomaly_checked_at, anomaly_review_suggested, anomaly_flags (JSON). Vendor & Admin property list: "Review" column with "Review suggested" badge; edit page: warning panel with list of anomaly messages. |
| B-1 | 2026-02-03 | AiAssistantService::generateSocialCopy(); POST /ai-assistant/generate-social-copy (vendor/admin, property_id). Vendor & Admin property edit: "Generate social copy" button opens modal with Facebook, Instagram, LinkedIn, Hashtags textareas + Copy buttons. Vendor requires has_ai_features. |
| B-2 | 2026-02-03 | BulkGenerateDescriptionJob per property; POST /ai-assistant/bulk-generate-description (property_ids). Vendor/Admin property list: "Generate descriptions with AI" button when rows selected; jobs dispatched with 15s delay to throttle API; vendor requires has_ai_features. Default-language description/meta updated. |
| B-3 | 2026-02-03 | AiAssistantService::suggestPrice($address, $specs, $comparables); POST /ai-assistant/suggest-price (vendor/admin, property_id or address+specs). Backend & Vendor property edit: "Suggest price" button near price field; API loads property address/specs and comparables (same city+type, limit 10), returns price_low, price_high, justification, disclaimer; UI shows range + justification + disclaimer and optionally pre-fills price with midpoint. Vendor requires has_ai_features. |
| A2 | 2026-02-03 | Smart email campaigns: AiAssistantService::generateCampaignEmail(); POST /ai-assistant/send-campaign (vendor/agent, lead_ids, campaign_type, property_id). Unsubscribe: unsubscribed_at + unsubscribe_token on property_contacts; token set on contact create; GET /unsubscribe/campaign/{token}. SendCampaignEmailJob per lead (throttled); campaign-update email template with unsubscribe link. Vendor Property Messages & Agent Property Messages: checkboxes, "Send update" button, modal (type: price_drop/new_listing/general_update, optional property); vendor requires has_ai_features. |
| | | |
| | | |

---

## Notes

- **API costs:** Use queues for bulk ops; cache market/compliance where possible; consider tiered AI credits per package.
- **Permissions:** Every new AI endpoint must check vendor/agent/admin and `has_ai_features` where relevant.
- **Voice (B2):** Separate per-minute cost; high value for premium agents.
- **CRM (B1):** Consider Zapier/Make for MVP before full OAuth integration.
