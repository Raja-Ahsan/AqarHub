# Social Media Connections – Setup

Admin, Vendor, and Agent can connect their **Facebook** and **LinkedIn** accounts in **Edit Profile** (Settings). After connecting, when they use **"Generate social copy"** on a property, they see **"Post to Facebook"** and **"Post to LinkedIn"** in the modal and can post the generated copy with one click.

## 1. Where it appears

- **Settings:** Admin → Edit Profile; Vendor → Edit Profile; Agent → Edit Profile.  
  A **"Social Media Connections"** card lists Facebook and LinkedIn with **Connect** / **Disconnect**.
- **Property edit:** After clicking **"Generate social copy"**, the modal shows Copy + (if connected) **Post to Facebook** / **Post to LinkedIn**.

## 2. Facebook (optional)

1. Go to [developers.facebook.com](https://developers.facebook.com) and create an app (e.g. "Business").
2. Add product **Facebook Login** and use **Custom** (OAuth).
3. In **App settings → Basic**: copy **App ID** and **App Secret** into `.env`:
   - `FACEBOOK_APP_ID=...`
   - `FACEBOOK_APP_SECRET=...`
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
4. Copy **Client ID** and **Client Secret** into `.env`:
   - `LINKEDIN_CLIENT_ID=...`
   - `LINKEDIN_CLIENT_SECRET=...`

## 4. Flow

1. User goes to **Edit Profile** and clicks **Connect** for Facebook or LinkedIn.
2. They are redirected to the provider, sign in, and authorize.
3. Callback saves the token and (for Facebook) fetches the first Page token for posting.
4. On **Property edit**, **Generate social copy** fills the modal; if the platform is connected, **Post to Facebook** / **Post to LinkedIn** appears and posts the text via the stored token.

If no keys are set in `.env`, the **Connect** links still work but the OAuth redirect will fail until the app is configured.
