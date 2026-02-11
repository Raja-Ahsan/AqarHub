# Social Media Connections – Setup

Admin, Vendor, and Agent can connect **Facebook**, **LinkedIn**, **Instagram**, **TikTok**, and **Twitter | X** in **Edit Profile**. After connecting, **"Generate social copy"** on a property shows **Post to Facebook**, **Post to LinkedIn**, **Post to Instagram** (with property image), **Post to X**, and **Open TikTok Upload** (copy + open upload page).

## 1. Where it appears

- **Settings:** Admin / Vendor / Agent → **Edit Profile**.
  - **Social platform app credentials** card: Each user saves their own app credentials (Facebook App ID/Secret, LinkedIn Client ID/Secret, TikTok Client Key/Secret, Twitter/X Client ID/Secret) in the database. These are required to use **Connect** for each platform. No `.env` keys are used; everything is per-user in the DB.
  - **Social Media Connections** card: Connect or Disconnect OAuth accounts for each platform (for posting from the app). Uses the credentials you saved above.
  - **Social Profile Links** card: Optional URL fields (Facebook, LinkedIn, Instagram, TikTok, Twitter/X) saved per user in the database. Use these to display your public profile links on your profile or listings.
- **Property edit:** After **Generate social copy**, the modal shows Copy + (when connected) **Post to Facebook**, **Post to LinkedIn**, **Post to Instagram**, **Post to X**, **Open TikTok Upload**.

## 2. Facebook (optional)

1. Go to [developers.facebook.com](https://developers.facebook.com) and create an app (e.g. "Business").
2. Add product **Facebook Login** and use **Custom** (OAuth).
3. In **App settings → Basic**: copy **App ID** and **App Secret** and save them in **Edit Profile** → **Social platform app credentials** (Facebook App ID, Facebook App Secret).
4. **Facebook Login → Settings**: set **Valid OAuth Redirect URIs** to:
   - `https://your-domain.com/auth/social/callback/facebook`  
   (or `http://localhost/ai-property/auth/social/callback/facebook` for local.)
5. Under **App Review**, request permissions: `pages_show_list`, `pages_read_engagement`, `pages_manage_posts` (needed to post to a Page).  
   For testing you can use roles in **Roles → Test Users** without full App Review.

Posting uses the first Facebook **Page** linked to the user’s account (token is stored in the connection).

## 3. LinkedIn (optional)

1. Go to [linkedin.com/developers](https://www.linkedin.com/developers/) and create an app.
2. In the app, open **Auth** and add **Redirect URL**:
   - `https://your-domain.com/auth/social/callback/linkedin`  
   (or `http://localhost/ai-property/auth/social/callback/linkedin` for local.)
3. Under **Products**, request **Share on LinkedIn** (adds `w_member_social`).
4. Copy **Client ID** and **Client Secret** and save them in **Edit Profile** → **Social platform app credentials** (LinkedIn Client ID, LinkedIn Client Secret).

## 4. Instagram (optional)

Instagram uses the **same Facebook app**. The user must have an **Instagram Business or Creator** account linked to a **Facebook Page**.

1. In your Facebook app, add **Instagram Graph API** and request permissions: `instagram_basic`, `instagram_content_publish`, `pages_show_list`.
2. Add a **Valid OAuth Redirect URI**: `https://your-domain.com/auth/social/callback/instagram` (or `http://localhost/ai-property/auth/social/callback/instagram`).
3. Use the same Facebook app credentials in **Edit Profile** → **Social platform app credentials** (Instagram uses the Facebook App ID and App Secret).
4. **Post to Instagram** requires an image: the property’s **featured image** is used. Add a featured image to the property to post.

## 5. TikTok (optional)

1. Go to [developers.tiktok.com](https://developers.tiktok.com/) and create an app.
2. Add **Redirect URI**: `https://your-domain.com/auth/social/callback/tiktok`. (For local dev, TikTok may allow `http://...` in some setups; production must use **HTTPS**.)
3. Request **Login Kit** (scopes: `user.info.basic`, `video.list`).
4. Copy **Client Key** and **Client Secret** and save them in **Edit Profile** → **Social platform app credentials** (TikTok Client Key, TikTok Client Secret).
5. **Open TikTok Upload** does not post via API; it copies the caption to the clipboard and opens [tiktok.com/upload](https://www.tiktok.com/upload) so the user can paste and add their video. The app uses the modern clipboard API when available and falls back to the legacy copy method.
6. **Token refresh:** TikTok access tokens expire in 24 hours. The app automatically refreshes them when you open Edit Profile or a property edit page, so the connection stays valid. On **Disconnect**, the app revokes the token with TikTok so the app is removed from the user’s TikTok app permissions.

## 6. Twitter | X (optional)

1. Go to [developer.x.com](https://developer.x.com/) (Twitter Developer Portal) and create a Project and App.
2. In the app, enable **OAuth 2.0** and set **Callback URI / Redirect URL** to:
   - `https://your-domain.com/auth/social/callback/twitter`  
   (or `http://localhost/ai-property/auth/social/callback/twitter` for local.)
3. Request **OAuth 2.0 scopes**: `tweet.read`, `tweet.write`, `users.read`.
4. Copy **Client ID** and **Client Secret** (OAuth 2.0) and save them in **Edit Profile** → **Social platform app credentials** (Twitter/X Client ID, Twitter/X Client Secret).
5. **Post to X** posts the generated tweet (text only; 280 characters max). The AI generates a dedicated **twitter** copy when you use **Generate social copy**.

## 7. Social platform app credentials (database)

Admin, Vendor, and Agent each save their **own** app credentials in **Edit Profile** → **Social platform app credentials**. Stored in the `user_social_credentials` table (one row per user). Fields: Facebook App ID, Facebook App Secret, LinkedIn Client ID/Secret, TikTok Client Key/Secret, Twitter/X Client ID/Secret. Secrets are not shown when editing; leave the secret field blank to keep the current value. No `.env` configuration is required for social connections; everything is per-user in the database.

## 8. Social profile links (database)

Each user (Admin, Vendor, Agent) can save optional **social profile URLs** in **Edit Profile** → **Social Profile Links**:

- **Facebook URL**, **LinkedIn URL**, **Instagram URL**, **TikTok URL**, **Twitter / X URL**

These are stored in the `social_links` table (one row per user, polymorphic `connectable`). All fields are optional; valid values must be full URLs (e.g. `https://facebook.com/yourpage`). Use them to show links on profile pages or listings.

## 9. Flow

1. User goes to **Edit Profile** and clicks **Connect** for a platform (OAuth), or enters **Social Profile Links** and clicks **Save social links**.
2. For OAuth: they are redirected to the provider, sign in, and authorize. Callback saves the token; for Facebook we fetch the first Page token; for Instagram we fetch the linked Instagram Business account (via the same Facebook app).
3. On **Property edit**, **Generate social copy** fills the modal (including a **Twitter | X** tweet); when a platform is connected, **Post to…** or **Open TikTok Upload** appears.

If you have not saved credentials for a platform in **Edit Profile** → **Social platform app credentials**, **Connect** for that platform will show a message asking you to add them first.
