# Application Features by Role

This document lists all features of the AI Property application, organized by user role.  
**Roles:** Admin, Vendor, Agent, User (registered frontend), Guest/Visitor (public).

---

## 1. Admin (Backend Panel)

Admins have full control over the platform. Access: **/admin** (login required). Many sections are gated by **permissions** (e.g. Menu Builder, User Management, Home Page).

### 1.1 Dashboard & Account
- **Dashboard** – Main admin panel with overview.
- **Membership request** – View and update membership/payment requests.
- **Theme** – Switch admin panel theme (dark/light).
- **Edit profile** – Update admin profile.
- **Change password** – Update password.
- **Logout.**

### 1.2 Social & Branding (Admin as connectable)
- **Social connections** – Connect/disconnect Facebook, LinkedIn, Instagram, TikTok, Twitter (OAuth); list connections.
- **Social links** – Store and update profile URLs (e.g. Facebook profile, LinkedIn URL).
- **Social credentials** – Store per-admin app credentials for social platforms (no .env); “How to connect & get keys” toggles per platform.

### 1.3 Menu Builder *(permission: Menu Builder)*
- Manage menu structure and update menus.

### 1.4 Payments & Packages
- **Payment log** – View and update payment logs.
- **Package settings** – Configure package-related settings.
- **Packages** – CRUD packages (upload image, store, edit, featured, delete, bulk delete).
- **Featured pricing** – CRUD featured pricing; view featured requests; change featured status and payment status; delete featured request.

### 1.5 Property Specification (Location & Taxonomies)
- **Settings** – Property specification settings and update.
- **Categories** – CRUD property categories; featured; bulk delete.
- **Amenities** – CRUD amenities; bulk delete.
- **Cities** – CRUD cities; get cities; featured; bulk delete.
- **Countries** – CRUD countries; bulk delete.
- **States** – CRUD states; get state; get states/cities; bulk delete.

### 1.6 Property Management
- **Settings** – Property management settings.
- **Properties list** – List all properties (by language); filter by type.
- **Create / Edit property** – Full property CRUD; assign agent; featured; status; approve status; specification delete; featured payment.
- **Images** – Store/update/remove property slider images; remove from DB.
- **Delete** – Delete single or bulk delete properties.
- **AI on property (when AI enabled):** Generate description, analyze image, translate content, check compliance, suggest price, generate social copy, “Add on your social pages” (post to Facebook/LinkedIn/Instagram/Twitter/TikTok), bulk generate description.

### 1.7 Project Management
- **Settings** – Project management settings and update.
- **Projects** – CRUD projects; featured; status; approve; specification delete; delete; bulk delete.
- **Gallery images** – Store, remove, remove from DB.
- **Floor plan images** – Store, remove, remove from DB.
- **Project types** – Per-project type CRUD; bulk delete.

### 1.8 Property Messages
- View all property messages (inquiries).
- Delete message.

### 1.9 Agent Management
- List agents; register (store); update; change status; secret login; delete.

### 1.10 User Management *(permission: User Management)*
- **Registered users** – List, create, edit, update account status, update email status, change password, update password, delete, secret login, bulk delete.
- **Subscribers** – List, delete, bulk delete; write email to subscribers; send email.
- **Vendor management** – Settings; add vendor; list registered vendors; per-vendor: update account/email status, details, edit, update balance, change/update password, delete; current/next package (remove, change, add); bulk delete; secret login.

### 1.11 Home Page *(permission: Home Page)*
- **Hero** – Slider version (CRUD, video URL) and static version (image, information).
- **Category section** – Manage and update.
- **Work process** – Section info and CRUD work process items; bulk delete.
- **Feature section** – Section info and CRUD features; bulk delete.
- **Property section** – Section info and update.
- **City section** – Section info and update.
- **Vendor section** – Section info and update.
- **Project section** – Section info and update.
- **Pricing section** – Section info and update.
- **Counter section** – Image and info; CRUD counters; bulk delete.
- **Testimonial section** – Section info, background; CRUD testimonials; bulk delete.
- **Subscribe section** – Section info and background.
- **Call to action** – Image and section update.
- **Blog section** – Section update.
- **Section customization** – Toggle section visibility.
- **About section** – Image and info.
- **Why choose us** – Image and info.
- **Brand section** – CRUD brands.

### 1.12 Support Tickets
- Settings and update; list tickets; view message; zip upload; reply; close; assign/unassign staff; delete; bulk delete.

### 1.13 Footer *(permission: Footer)*
- Logo and background image; content; quick links (CRUD).

### 1.14 Custom Pages *(permission: Custom Pages)*
- List, create, store, edit, update, delete, bulk delete custom pages.

### 1.15 Blog Management *(permission: Blog Management)*
- **Categories** – CRUD; bulk delete.
- **Blogs** – CRUD; bulk delete.

### 1.16 FAQ Management *(permission: FAQ Management)*
- CRUD FAQs; bulk delete.

### 1.17 Advertise *(permission: Advertise)*
- Advertise settings; list all advertisements; store, update, delete, bulk delete.

### 1.18 Announcement Popups *(permission: Announcement Popups)*
- List; select popup type; create/store; update status; edit/update; delete; bulk delete.

### 1.19 Payment Gateways *(permission: Payment Gateways)*
- **Online** – Configure PayPal, Instamojo, Paystack, Flutterwave, Razorpay, MercadoPago, Mollie, Stripe, Paytm, Authorize.net.
- **Offline** – Store, update status, update, delete offline gateways.

### 1.20 Basic Settings *(permission: Basic Settings)*
- Favicon, logo, information, general settings, contact page, theme & home, currency, appearance.
- Mail from admin, mail to admin, mail templates (edit/update).
- Breadcrumb, page headings.
- Plugins: Disqus, Tawk.to, reCAPTCHA, Facebook, Google, WhatsApp.
- SEO, maintenance mode, cookie alert, social media links (CRUD).

### 1.21 Admin Management *(permission: Admin Management)*
- **Roles & permissions** – Store/update/delete roles; manage permissions per role.
- **Registered admins** – Store, update status, update, delete.

### 1.22 Language Management *(permission: Language Management)*
- List languages; store; make default; update; edit keyword; add/update/delete keyword; delete language; RTL check.

---

## 2. Vendor (Vendor Panel)

Vendors manage their own listings, projects, agents, and leads. Access: **/vendor** (login). Features are limited by **package** (e.g. number of properties, projects, agents). **AI features** require package `has_ai_features`.

### 2.1 Dashboard & Account
- **Dashboard** – Vendor dashboard (with AI Lead Insights when AI enabled).
- **Edit profile** – Update profile.
- **Change password** – Update password.
- **Payment log** – View payment history.
- **Theme** – Change panel theme.
- **Logout.**

### 2.2 Auth (Guest)
- Signup, login, email verify, forget password, reset password.

### 2.3 Social & Branding
- **Social connections** – Connect/disconnect Facebook, LinkedIn, Instagram, TikTok, Twitter; list connections.
- **Social links** – Update profile URLs.
- **Social credentials** – Store per-vendor app credentials for social platforms; “How to connect & get keys” toggles.

### 2.4 Membership / Packages
- **Package list** – View plans to extend.
- **Checkout** – Checkout for package; payment instructions; success/cancel for PayPal, Stripe, Paytm, Paystack, MercadoPago, Razorpay, Instamojo, Flutterwave, Mollie, Authorize.net, offline, trial.

### 2.5 Property Management *(package: property limits)*
- **Properties list** – List own properties (by language); filter by type; **AI:** bulk generate descriptions (when selected), “Review suggested” anomaly badge.
- **Create / Edit property** – Full CRUD; update featured; update status; specification delete; delete; bulk delete.
- **Images** – Store/update/remove slider images; remove from DB.
- **Request to featured** – Request featured; featured payment (with gateways); success/cancel flows.
- **AI on create/edit (if has_ai_features):** Generate description, analyze image, translate, check compliance, suggest price, generate social copy, “Add on your social pages” (post to social + TikTok row).

### 2.6 Property Messages
- **List messages** – All inquiries for vendor; filter by intent; sort by lead score; columns: Intent, Score, Reply sent; **AI:** select leads, “Send update (e.g. price drop)” → modal (type: price_drop/new_listing/general_update, optional property) → send campaign (AI-generated emails, queued).
- **View** – View message; **Suggest reply with AI**; send reply (email).
- **Delete** message.

### 2.7 Auto-Reply Settings
- Configure auto-reply for inquiries.

### 2.8 Project Management *(package: project limits)*
- **Projects** – CRUD; featured; status; specification delete; delete; bulk delete.
- **Gallery & floor plan images** – Store, remove, remove from DB.
- **Project types** – CRUD per project; bulk delete.

### 2.9 Agent Management *(package: agent limits)*
- List agents; register (store); update; change status; secret login; delete.

### 2.10 Support Tickets
- Create ticket; list tickets; view message; zip upload; reply; delete.

---

## 3. Agent (Agent Panel)

Agents manage properties and projects (under their vendor or admin). Access: **/agent** (login). Limits follow vendor package when agent belongs to a vendor.

### 3.1 Dashboard & Account
- **Dashboard** – Agent dashboard (with AI Lead Insights when AI enabled).
- **Edit profile** – Update profile.
- **Change password** – Update password.
- **Theme** – Change panel theme.
- **Logout.**

### 3.2 Auth (Guest)
- Login, forget password, reset password.

### 3.3 Social & Branding
- **Social connections** – Connect/disconnect Facebook, LinkedIn, Instagram, TikTok, Twitter; list connections.
- **Social links** – Update profile URLs.
- **Social credentials** – Store per-agent app credentials; “How to connect & get keys” toggles.

### 3.4 Property Management
- **Properties list** – List own properties; filter by type.
- **Create / Edit property** – Full CRUD; featured; status; specification delete; delete; bulk delete; images (store/update/remove).
- **Get states/cities, get cities** – For forms.

### 3.5 Property Messages
- **List messages** – Inquiries for agent; filter by intent; sort by lead score; Intent, Score, Reply sent; **AI:** select leads, “Send update” → campaign modal → send campaign.
- **View** – View message; **Suggest reply with AI**; send reply (email).
- **Delete** message.

### 3.6 Auto-Reply Settings
- Configure auto-reply for inquiries.

### 3.7 Project Management
- **Projects** – CRUD; status; specification delete; delete; bulk delete.
- **Gallery & floor plan images** – Store, remove, remove from DB.
- **Project types** – CRUD; bulk delete.

---

## 4. User (Registered Frontend User)

Logged-in visitors. Access: **/user/** routes (auth required).

### 4.1 Dashboard & Account
- **Dashboard** – User dashboard.
- **Edit profile** – Update profile.
- **Change password** – Update password.
- **Logout.**

### 4.2 Auth (Guest)
- Login (including Facebook, Google OAuth), signup, signup verify, forget password, reset password.

### 4.3 Wishlist
- **Wishlist** – View wishlist (saved properties).
- **Add/remove wishlist** – From property pages (addto/wishlist, remove/wishlist).

### 4.4 Support Tickets
- List tickets; create; view message; reply.

---

## 5. Guest / Visitor (Public Frontend)

No login. All under **change.lang** where applicable.

### 5.1 General
- **Home** – Home page (sections per theme).
- **Change language** – Switch site language.
- **Subscribe** – Store subscriber email.
- **Push notification** – Store endpoint (optional).

### 5.2 Properties
- **Property listing** – List properties (filters: state, cities, categories).
- **Property details** – View single property (by slug).
- **Property contact** – Submit inquiry (name, email, phone, message); stored as property_contact; optional AI intent/lead score; email to vendor/agent/admin.
- **Contact (general)** – Contact page; send mail to admin.

### 5.3 Projects
- **Project listing** – List projects.
- **Project details** – View single project (by slug).

### 5.4 Vendors & Agents
- **Vendors list** – List vendors.
- **Vendor details** – View vendor profile (by username); send contact message.
- **Agent details** – View agent profile (by username).

### 5.5 Content
- **Blog** – List blogs; blog details (by slug).
- **FAQ** – FAQ page.
- **About us** – About page.
- **Pricing** – Pricing page.
- **Custom pages** – Dynamic page by slug.

### 5.6 AI Assistant (when config ai.enabled)
- **Chat** – Conversational AI (property search, questions).
- **Search** – AI-powered property search.
- **Property details** – Fetch details for chat.
- **Inquiry** – Submit inquiry from chat (same as property contact; intent/score stored).
- **Unsubscribe** – GET /unsubscribe/campaign/{token} – Opt out of campaign emails.

### 5.7 Other
- **Advertisement** – Count view (e.g. for ads).
- **Offline** – Offline page.
- **Service unavailable** – Maintenance message.

---

## 6. AI Features Summary (by role)

| Feature | Admin | Vendor | Agent |
|--------|-------|--------|-------|
| Lead scoring & intent (dashboard + messages) | ✓ | ✓ (if has_ai_features) | ✓ |
| Suggest reply for inquiries | ✓ | ✓ | ✓ |
| Compliance check on description | ✓ | ✓ (has_ai_features) | — |
| Anomaly detection (property save) | ✓ | ✓ | — |
| Generate description (single) | ✓ | ✓ (has_ai_features) | — |
| Bulk generate description | ✓ | ✓ (has_ai_features) | — |
| Generate social copy | ✓ | ✓ (has_ai_features) | — |
| Post to social (FB/LI/IG/Twitter/TikTok) | ✓ | ✓ (has_ai_features) | — |
| Suggest price | ✓ | ✓ (has_ai_features) | — |
| Smart email campaigns (send update to leads) | — | ✓ (has_ai_features) | ✓ |
| Analyze image / Translate / Check compliance (property form) | ✓ | ✓ (has_ai_features) | — |

**Vendor** “has_ai_features” is determined by the current package. **Agent** has campaign and suggest-reply (and lead insights); other AI tools are not exposed in the agent panel in the current routes.

---

## 7. Social & Integrations (by role)

- **Admin, Vendor, Agent:** OAuth connections (Facebook, LinkedIn, Instagram, TikTok, Twitter); per-user social links; per-user app credentials (Facebook, LinkedIn, Instagram, TikTok, Twitter) with “How to connect” toggles.
- **Post to social:** From property edit (Admin/Vendor): post to Facebook, LinkedIn, Instagram, Twitter; TikTok: copy caption + open TikTok + success message. Media selector: featured image, first gallery, video thumbnail.
- **Campaign emails:** Unsubscribe link in footer; token stored on property_contact; public unsubscribe page.

---

*Last updated from routes and AI-2026-TODO-INTEGRATION.md. For implementation details, see the codebase and AI-2026-TODO-INTEGRATION.md.*
