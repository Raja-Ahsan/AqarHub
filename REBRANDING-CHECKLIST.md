# Rebranding Checklist – Make This App Your Product

Use this list to rebrand the app and remove or replace original author references.  
**Do not edit files inside `vendor/`** unless you are prepared to re-apply changes after every `composer update`.

---

## 1. **README & project identity**

| What | Where | Change to |
|------|--------|-----------|
| Project name & ownership | `README.md` | Your product name, your company/name, and your own short description. Remove "owned by LegacyTech" (or similar) and "Please go back" if still present. |
| App name fallback | `config/app.php` | Line 16: `'name' => env('APP_NAME', 'Your Product Name')` — replace `AqarHub` with your product name if needed. |
| App name in env | `.env` | `APP_NAME='Your Product Name'` — you already have `AI Property`; set whatever you want. |
| Version / release info | `version.json` | Set `version` and `released_on` to your product version and date. |

---

## 2. **Frontend meta & author (visible to users)**

| What | Where | Change to |
|------|--------|-----------|
| Meta author | `resources/views/frontend/layouts/layout-v1.blade.php` | Line 8: `<meta name="author" content="LegacyTech">` (or your name/company). |
| Meta author | `resources/views/frontend/layouts/layout-v2.blade.php` | Same as above. |
| Meta author | `resources/views/frontend/layouts/layout-v3.blade.php` | Same as above. |

---

## 3. **Admin / database-driven branding (no code change)**

Configure in **Admin panel** after install:

- **Site title** – e.g. Basic Settings → Website Title.
- **Logo** – upload your logo (wherever the theme uses `basic_settings` / logo).
- **Favicon** – upload your favicon (same settings area).
- **Footer copyright** – stored in DB (e.g. footer content / language); set your company name and year, e.g. `© 2025 Your Company. All rights reserved.`
- **Contact / email** – replace demo contact info with yours.

These are usually under something like **Basic Settings**, **Footer**, or **Language / Content**. No file edits needed if the admin UI supports them.

---

## 4. **Installer & license (important if you sell or distribute)**

The installer includes a **license step** that checks an **Envato purchase code** (original author’s item). To make this your product you have three options:

- **Option A – Remove license step**  
  - In your **app** code (routes, middleware, or a custom installer flow), skip or bypass the `LaravelInstaller::license` and `LaravelInstaller::licenseCheck` routes so install goes: Requirements → Permissions → Environment → Database → Final.  
  - Do this in your own routes/middleware or by overriding the installer views/routes; avoid editing `vendor/` so updates don’t overwrite you.

- **Option B – Your own license server**  
  - Replace the Envato API call in the installer with a request to **your** backend that validates **your** license keys (or no key for internal use).  
  - This would require either publishing and editing the installer’s `LicenseController` (and related config) into `app/` or maintaining a small patch over the vendor controller.

- **Option C – Keep as-is**  
  - Leave the Envato verification; only buyers with a valid Envato purchase code can complete install.  
  - Not suitable if you want to sell or give the app as **your** product without Envato.

**Installer branding (optional):**

- Installer views live under `resources/views/vendor/installer/` (and possibly in vendor).  
- You can publish/customize those views to say “Your Product Name – Installation” and remove or change any “AqarHub” or other placeholder text in the installer UI.

---

## 5. **Vendor references (optional; will be overwritten by composer)**

Only change these if you accept re-applying them after each `composer update`:

| What | Where (in vendor) | Note |
|------|-------------------|------|
| License error message / support link | `vendor/kreativdev/installer/files/vendor/rachidlaasri/laravel-installer/.../LicenseController.php` | Points to original author support; replace with your support URL if you keep license check. |
| Item name | `vendor/kreativdev/installer/src/config/kdinstaller.php` | `'item_name' => "AqarHub"` – change to your product name only if you keep the package and accept vendor edits. |
| Email collector API | Same config | `'email_api' => 'https://kreativdev.com/...'` – set to your URL or disable if you don’t use it. |

Better long-term: **publish the installer config/views** (if the package supports it) into your app and override only what you need, so you don’t rely on editing `vendor/`.

---

## 6. **Demo / placeholder content**

- **Images** – Replace demo images in `public/assets/img/` (and any in `public/installer/`) with your own logo, favicons, and graphics.  
- **Database** – After first install, the DB may contain demo properties, pages, and text. Use the admin panel to delete or replace with your own content and wording.  
- **.env** – Use your own `APP_URL`, mail settings, and payment keys (don’t ship with demo keys in production).

---

## 7. **Legal / license**

- Check the **license** you have for this codebase (e.g. from CodeCanyon or original author).  
- If you **resell or redistribute** the app as your own product, you must comply with that license (e.g. extended license, or “use in one project” only).  
- Add your own **license file** or **terms of use** if you distribute the app to clients.

---

## Quick summary

| Priority | Action |
|----------|--------|
| **Must do** | Update README, `config/app.php` default name, `.env` APP_NAME, and the three layout meta `author` tags. |
| **Must do** | Set Admin branding (site title, logo, favicon, footer copyright). |
| **Must do** | Decide installer license: remove step, use your own check, or keep Envato. |
| **Should do** | Replace demo images and DB content; set your APP_URL and production keys in `.env`. |
| **Optional** | Customize installer views and `version.json`; only touch vendor if you accept re-applying changes after updates. |

After these changes, the app will present as your product to users and in search results (meta author, title, footer).  
If you want, I can apply the **non-vendor** changes for you (README, config app name, and the three layout author tags) and leave installer/license and admin content for you to set.
