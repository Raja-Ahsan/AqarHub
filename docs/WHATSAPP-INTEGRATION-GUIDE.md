# WhatsApp Features List – AI Property Platform

All **WhatsApp features** that can be built on this platform, grouped by role. No other platforms—WhatsApp only.

---

## Visitor / Guest (Public)

| # | Feature | Description |
|---|--------|-------------|
| 1 | **Click to Chat (property)** | On property detail page: button “Chat on WhatsApp” that opens WhatsApp with pre-filled text (e.g. “Hi, I’m interested in [Property Title] – [URL]”). |
| 2 | **Click to Chat (vendor/agent)** | On vendor or agent profile: “Contact via WhatsApp” with optional pre-fill. Number = vendor/agent WhatsApp. |
| 3 | **Click to Chat (contact page)** | Global “Contact us on WhatsApp” with pre-fill (e.g. “I have a question about your properties”). Link to admin or main business number. |
| 4 | **First message via API** | User enters phone on site; you send first WhatsApp message (template) so user gets a thread; then they reply. |
| 5 | **Inquiry from WhatsApp** | User sends message to your number; you create a lead and assign to vendor/agent by context (e.g. shared property link). |

---

## Vendor

| # | Feature | Description |
|---|--------|-------------|
| 6 | **Vendor WhatsApp number** | Each vendor has own WhatsApp Business number. Stored in DB; used for “Click to Chat” and replies. |
| 7 | **Receive inquiries on WhatsApp** | Inquiries sent to vendor’s number appear in Property Messages (or dedicated WhatsApp tab). |
| 8 | **Reply from panel** | In Property Messages, “Reply” sends via WhatsApp instead of (or in addition to) email. |
| 9 | **Notify vendor of new lead** | When a lead comes from website (form or AI chat), send vendor a WhatsApp: “New lead for [Property]: [Name], [Phone].” |
| 10 | **Campaign: send update on WhatsApp** | Same as “Send update” (price drop, new listing) but channel = WhatsApp. User must have opted in and shared phone. |
| 11 | **Viewing reminder (WhatsApp)** | When a viewing is booked, send reminder: “Your viewing for [Property] is on [Date] at [Time].” |
| 31 | **Create WhatsApp Channel** | Vendor can create and link a WhatsApp Channel (broadcast-only). Channel link stored in settings; shown on profile or listings. |
| 32 | **Post listing on WhatsApp Channel** | From property edit or list: “Post to WhatsApp Channel” — share listing (title, image, price, link) as a channel update. |
| 33 | **Publish listing to WhatsApp Status** | From property (listing) page: “Share on WhatsApp Status” — post listing image/caption to vendor’s WhatsApp Status (24h story). |

---

## Agent

| # | Feature | Description |
|---|--------|-------------|
| 12 | **Agent WhatsApp number** | Agent has own WhatsApp number (or uses vendor’s). Used for “Chat on WhatsApp” on agent profile and property pages. |
| 13 | **Receive inquiries on WhatsApp** | Messages to agent number show in Property Messages. |
| 14 | **Reply from panel** | Agent replies to lead from panel; message sent via WhatsApp. |
| 15 | **Notify agent of new lead** | New lead (website or WhatsApp) → WhatsApp notification to assigned agent. |
| 16 | **Campaign: send update on WhatsApp** | Agent can send “Send update” (price drop, new listing) to selected leads via WhatsApp (opt-in only). |
| 34 | **Create WhatsApp Channel** | Agent can create and link a WhatsApp Channel. Channel link stored in settings; shown on agent profile or listings. |
| 35 | **Post listing on WhatsApp Channel** | From property edit or list: “Post to WhatsApp Channel” — share listing (title, image, price, link) as a channel update. |
| 36 | **Publish listing to WhatsApp Status** | From property (listing) page: “Share on WhatsApp Status” — post listing image/caption to agent’s WhatsApp Status (24h story). |

---

## Admin

| # | Feature | Description |
|---|--------|-------------|
| 17 | **Admin WhatsApp number** | Global support or “Contact us” number. Used when no vendor/agent (e.g. general contact page). |
| 18 | **Receive global inquiries** | Messages to admin number create a ticket or lead; show in admin panel (property messages or support). |
| 19 | **Reply from admin panel** | Admin can reply to WhatsApp conversations (support or unassigned leads). |
| 20 | **Broadcast (opt-in)** | Send announcement to all subscribers who opted in for WhatsApp. |
| 21 | **WhatsApp settings** | Enable/disable WhatsApp; set default number; configure templates. |
| 37 | **Create WhatsApp Channel** | Admin can create and link a global/site WhatsApp Channel. Channel link stored in basic settings; shown on contact/footer. |
| 38 | **Post listing on WhatsApp Channel** | From admin property list/edit: “Post to WhatsApp Channel” — share any listing (title, image, price, link) as a channel update. |
| 39 | **Publish listing to WhatsApp Status** | From property (listing) page: “Share on WhatsApp Status” — post listing image/caption to admin’s WhatsApp Status (24h story). |

---

## User (Registered)

| # | Feature | Description |
|---|--------|-------------|
| 22 | **Opt-in for WhatsApp updates** | In profile or during inquiry: “Receive property updates on WhatsApp” + phone; store consent. |
| 23 | **Continue chat on WhatsApp** | From AI chat or inquiry success: “We’ll follow up on WhatsApp” or “Click to continue on WhatsApp” with pre-fill. |
| 24 | **Viewing / booking reminders** | If user booked a viewing, send reminder on WhatsApp. |
| 25 | **Price drop / new listing (user)** | For saved/wishlist properties: “Price dropped for [Property]” or “New listing matching your criteria.” Opt-in required. |

---

## Cross-Role / Platform

| # | Feature | Description |
|---|--------|-------------|
| 26 | **Unified inbox** | Vendor/Agent see Email + WhatsApp in one Inbox (same lead, multiple channels). |
| 27 | **AI + WhatsApp** | When lead writes on WhatsApp, run AI (intent, suggested reply) and show in panel. |
| 28 | **Templates management** | Admin creates/submits templates in Meta; app stores template names and uses them for campaigns, viewing, OTP. |
| 29 | **Webhook health & logs** | Log incoming webhooks and failures; alert if WhatsApp webhook is down. |
| 30 | **Analytics** | Count: conversations started, replies sent, templates sent, opt-ins. Per vendor/agent optional. |

---

## Implementation Phases (WhatsApp only)

| Phase | Features |
|-------|----------|
| **Phase 1** | 1, 2, 3 (Click to Chat on property, vendor/agent, contact) + 6, 12, 17 (Store WhatsApp number for vendor, agent, admin). |
| **Phase 2** | 5, 7, 13, 18 (Receive messages; create/attach to lead; show in Property Messages) + 8, 14, 19 (Reply from panel). |
| **Phase 3** | 4 (First message via template) + 9, 15 (Notify vendor/agent of new lead) + 10, 16 (Campaign “Send update” via WhatsApp). |
| **Phase 4** | 22, 23, 24, 25 (User opt-in, continue on WhatsApp, reminders, property alerts) + 20, 21 (Admin broadcast, WhatsApp settings) + 26–30 (Unified inbox, AI, templates, webhook logs, analytics). |
| **Phase 5** | 31–33 (Vendor: WhatsApp Channel + post listing + Status), 34–36 (Agent: same), 37–39 (Admin: same). Create channel, post listing on channel, publish listing to WhatsApp Status from property for Vendor, Agent, Admin. |
