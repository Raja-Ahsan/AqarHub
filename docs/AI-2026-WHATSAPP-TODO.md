# WhatsApp Integration – TODO List

**Rule:** All WhatsApp settings and credentials are stored in the **database** (per user), not in `.env`. WhatsApp is connected from the **profile page** (Edit Profile), same as other social platforms (Facebook, LinkedIn, etc.).

---

## Phase 1 – Foundation (DB + Profile + Click to Chat)

| # | Task | Status | Notes |
|---|------|--------|--------|
| 1.1 | Migration: add WhatsApp columns to `user_social_credentials` (whatsapp_phone_number, whatsapp_channel_link) | ☑ | Optional later: whatsapp_phone_number_id, whatsapp_business_account_id, whatsapp_access_token for API |
| 1.2 | Migration: add `whatsapp_url` to `social_links` (optional; or build wa.me from credentials) | — | For public profile display; can build from phone in credentials |
| 1.3 | Model `UserSocialCredentials`: fillable WhatsApp fields, `hasWhatsApp()`, helpers | ☑ | |
| 1.4 | Controller `updateSocialCredentials`: validate and save WhatsApp phone + channel link | ☑ | |
| 1.5 | Profile partial `social-credentials.blade.php`: add WhatsApp section (phone, channel link, “How to connect” toggle) | ☑ | Same card as other platforms |
| 1.6 | Click to Chat (property): button on property detail page; use vendor/agent/admin WhatsApp number from DB | ☑ | wa.me/{number}?text=... |
| 1.7 | Click to Chat (vendor/agent): on vendor/agent profile page; use their WhatsApp number from DB | ☑ | |
| 1.8 | Click to Chat (contact page): global “Contact on WhatsApp”; use admin/default number from DB | ☑ | |

---

## Phase 2 – Receive & Reply (API + Webhook)

| # | Task | Status | Notes |
|---|------|--------|--------|
| 2.1 | Store API credentials in DB (whatsapp_phone_number_id, whatsapp_business_account_id, whatsapp_access_token) per user | ☑ | Encrypt token; profile page fields |
| 2.2 | Webhook route + verify + handle incoming messages; create/attach lead; show in Property Messages | ☑ | |
| 2.3 | Reply from panel (Vendor/Agent/Admin): send via WhatsApp API | ☑ | |

---

## Phase 3 – Proactive (Templates + Campaigns)

| # | Task | Status | Notes |
|---|------|--------|--------|
| 3.1 | First message via template; notify vendor/agent of new lead on WhatsApp | ☑ | |
| 3.2 | Campaign “Send update” via WhatsApp (opt-in, template) | ☑ | |

---

## Phase 4 – User + Admin

| # | Task | Status | Notes |
|---|------|--------|--------|
| 4.1 | User opt-in for WhatsApp updates; continue chat on WhatsApp; reminders; price drop/new listing alerts | ☑ | Consent + continue link; campaigns already via WA |
| 4.2 | Admin broadcast; WhatsApp settings (enable/disable, default number) in DB | ☑ | basic_settings.whatsapp_enabled; broadcast to opted-in |
| 4.3 | Unified inbox; AI + WhatsApp; templates management; webhook logs; analytics | ☑ | Source column in messages; webhook_logs table |

---

## Phase 5 – Channel + Status

| # | Task | Status | Notes |
|---|------|--------|--------|
| 5.1 | Vendor/Agent/Admin: Create WhatsApp Channel; store channel link in DB (profile); Post listing on channel | ☑ | Link + “Copy channel post text” in property list Actions |
| 5.2 | Vendor/Agent/Admin: Publish listing to WhatsApp Status from property page | ☑ | wa.me prefill in property list Actions |

---

## Storage Summary (all in DB, not .env)

| What | Where |
|------|--------|
| WhatsApp phone number (Vendor, Agent, Admin) | `user_social_credentials.whatsapp_phone_number` |
| WhatsApp Channel link | `user_social_credentials.whatsapp_channel_link` |
| WhatsApp API (optional) | `user_social_credentials`: whatsapp_phone_number_id, whatsapp_business_account_id, whatsapp_access_token |
| Public profile WhatsApp link | Build from credentials phone (wa.me) or `social_links.whatsapp_url` |
| Admin default / contact page number | First admin’s credentials or dedicated basic_settings column (in DB) |

---

*Update status to ☑ when each task is done.*
