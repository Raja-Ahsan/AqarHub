# Installer verification (Rajaahsandev)

Use this to confirm the installer and “Add installer” step work using the local package `rajaahsandev/installer`.

---

## 1. Main installer (step-by-step)

**URL:** `http://localhost/ai-property/install`

- **Welcome** → Next  
- **Requirements** → Next  
- **Permissions** → Next  
- **License** → Enter Envato username + purchase code (or skip if you removed this step)  
- **Environment** → Wizard: set app name, app URL, DB host/name/user/password → Save  
- **Database** → (if shown) run migrations / import  
- **Final** → Done  

If you see all these steps and can complete them without a 500 error, the main installer is working. It uses `vendor/rachidlaasri/laravel-installer` (unchanged).

---

## 2. “Add installer” / database upload (Rajaahsandev)

**URL:** `http://localhost/ai-property/add-installer`

- **Controller:** `Rajaahsandev\Installer\Controllers\KdInstallerController`
- **GET** → Shows the form (upload `database.sql`).
- **POST** → `addInstaller()`: saves `.sql` to `public/installer/`, copies installer files (rachidlaasri, views, config, etc.), creates `version.json`.

**What to do:**

1. Open `http://localhost/ai-property/add-installer` in the browser.
2. You should see the “Add installer” form (upload database file).
3. Choose a valid `database.sql` and submit.
4. If it runs without error and you get a success message (and `public/installer/database.sql` exists), the “Add installer” / database upload step is working.

If you get “Class not found” or 500, the app is not loading `Rajaahsandev\Installer\KdInstallerServiceProvider` or the route namespace is wrong — check that `vendor/rajaahsandev/installer` (or the path repo) is installed and that `packages/rajaahsandev/installer` has the correct namespace in `src/routes/web.php` and `KdInstallerServiceProvider`.

---

## 3. Quick checklist

| Check | URL | Expected |
|-------|-----|----------|
| Main installer | `http://localhost/ai-property/install` | Wizard steps (welcome → requirements → … → final). |
| Add installer form | `http://localhost/ai-property/add-installer` | Form to upload `database.sql`. |
| Add installer submit | Same URL, POST with file | Success and `public/installer/database.sql` updated. |

---

## 4. If the app is “already installed”

The main installer is skipped when `storage/installed` exists. To test the full flow again:

1. Delete (or rename) `storage/installed`.
2. Reload `http://localhost/ai-property/install` and go through the steps again.

Do not delete `storage/installed` on a production site.
