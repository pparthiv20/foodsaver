# OAuth Setup Guide - Google and Facebook

This guide will help you set up OAuth authentication with Google and Facebook for the Food-Saver application.

## Table of Contents
1. [Google OAuth Setup](#google-oauth-setup)
2. [Facebook OAuth Setup](#facebook-oauth-setup)
3. [Configuration](#configuration)
4. [Testing](#testing)
5. [Troubleshooting](#troubleshooting)

---

## Google OAuth Setup

### Step 1: Create a Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Sign in with your Google account
3. Click on the project dropdown at the top
4. Click "NEW PROJECT"
5. Enter project name: "Food-Saver" (or your preferred name)
6. Click "CREATE"

### Step 2: Enable Google+ API

1. In the Cloud Console, go to **APIs & Services** > **Library**
2. Search for "Google+ API"
3. Click on it and click "ENABLE"
4. Search for "Gmail API" and enable it as well (optional, for better user data)

### Step 3: Create OAuth 2.0 Credentials

1. Go to **APIs & Services** > **Credentials**
2. Click "CREATE CREDENTIALS" > "OAuth client ID"
3. If prompted, click "CONFIGURE CONSENT SCREEN"
4. Select "External" user type
5. Fill in the application details:
   - App name: Food-Saver
   - User support email: your-email@gmail.com
   - Developer contact: your-email@gmail.com
6. Click "SAVE AND CONTINUE"
7. Add scopes: `email`, `profile`, `openid`
8. Click "SAVE AND CONTINUE"
9. Add test users if needed
10. Back to **Credentials** page
11. Click "CREATE CREDENTIALS" > "OAuth client ID"
12. Select "Web application"
13. Add Authorized redirect URIs:
    - `http://localhost/food-saver-php/pages/oauth_callback.php?provider=google`
    - `http://yourdomain.com/pages/oauth_callback.php?provider=google`
    - `https://yourdomain.com/pages/oauth_callback.php?provider=google`
14. Click "CREATE"
15. Copy your **Client ID** and **Client Secret**

---

## Facebook OAuth Setup

### Step 1: Create a Facebook App

1. Go to [Facebook Developers](https://developers.facebook.com/)
2. Sign in with your Facebook account
3. Click "My Apps" > "Create App"
4. Select "Consumer" as the app type
5. Fill in the form:
   - App Name: Food-Saver
   - App Email: your-email@facebook.com
6. Click "Create App"

### Step 2: Configure Facebook Login

1. In your app dashboard, click "Add Product"
2. Search for "Facebook Login" and click "Set Up"
3. Choose "Web" as your platform
4. In **Settings** > **Basic**, copy your **App ID** and **App Secret**

### Step 3: Configure OAuth Redirect URLs

1. Go to **Settings** > **Basic**
2. Add your **App Domains**:
   - `localhost`
   - `yourdomain.com`
3. Go to **Facebook Login** > **Settings**
4. Add Valid OAuth Redirect URIs:
   - `http://localhost/food-saver-php/pages/oauth_callback.php?provider=facebook`
   - `http://yourdomain.com/pages/oauth_callback.php?provider=facebook`
   - `https://yourdomain.com/pages/oauth_callback.php?provider=facebook`
5. Click "Save Changes"

---

## Configuration

### Update config.php

Open `includes/config.php` and update the OAuth credentials:

```php
// OAuth Configuration
// Google OAuth - Get credentials from https://console.cloud.google.com
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');

// Facebook OAuth - Get credentials from https://developers.facebook.com
define('FACEBOOK_APP_ID', 'YOUR_FACEBOOK_APP_ID');
define('FACEBOOK_APP_SECRET', 'YOUR_FACEBOOK_APP_SECRET');
```

Replace the values with your actual credentials:
- `YOUR_GOOGLE_CLIENT_ID` - Your Google Client ID
- `YOUR_GOOGLE_CLIENT_SECRET` - Your Google Client Secret
- `YOUR_FACEBOOK_APP_ID` - Your Facebook App ID
- `YOUR_FACEBOOK_APP_SECRET` - Your Facebook App Secret

### Database Schema Updates

The system expects the following fields in your user tables:

For Google OAuth:
- `google_id` (VARCHAR 255)
- `google_token` (LONGTEXT, stores JSON)

For Facebook OAuth:
- `facebook_id` (VARCHAR 255)
- `facebook_token` (LONGTEXT, stores JSON)

Add these columns to your `users`, `restaurants`, `ngos`, and `admins` tables:

```sql
ALTER TABLE users ADD COLUMN google_id VARCHAR(255), ADD COLUMN google_token LONGTEXT;
ALTER TABLE users ADD COLUMN facebook_id VARCHAR(255), ADD COLUMN facebook_token LONGTEXT;

ALTER TABLE restaurants ADD COLUMN google_id VARCHAR(255), ADD COLUMN google_token LONGTEXT;
ALTER TABLE restaurants ADD COLUMN facebook_id VARCHAR(255), ADD COLUMN facebook_token LONGTEXT;

ALTER TABLE ngos ADD COLUMN google_id VARCHAR(255), ADD COLUMN google_token LONGTEXT;
ALTER TABLE ngos ADD COLUMN facebook_id VARCHAR(255), ADD COLUMN facebook_token LONGTEXT;

ALTER TABLE admins ADD COLUMN google_id VARCHAR(255), ADD COLUMN google_token LONGTEXT;
ALTER TABLE admins ADD COLUMN facebook_id VARCHAR(255), ADD COLUMN facebook_token LONGTEXT;
```

---

## File Structure

The OAuth implementation includes the following files:

### New Files Created:
1. **`includes/oauth_handler.php`** - Main OAuth handler class with methods for:
   - Generating authorization URLs
   - Exchanging authorization codes for access tokens
   - Retrieving user profiles
   - Finding or creating user accounts

2. **`pages/oauth_start.php`** - Initiates OAuth flow and redirects to provider

3. **`pages/oauth_callback.php`** - Handles OAuth provider callbacks and authenticates users

### Modified Files:
1. **`includes/config.php`** - Added OAuth credentials
2. **`pages/login.php`** - Added social login buttons and styling
3. **`pages/register.php`** - Added social signup buttons and styling

---

## Testing

### Local Testing with Google OAuth

1. Update `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` in `config.php`
2. Go to your Google Cloud Console
3. Add `http://localhost/food-saver-php/pages/oauth_callback.php?provider=google` to Authorized redirect URIs
4. Test by clicking "Continue with Google" on login/register pages

### Local Testing with Facebook OAuth

1. Update `FACEBOOK_APP_ID` and `FACEBOOK_APP_SECRET` in `config.php`
2. Go to Facebook Developer Console
3. Add `localhost` to App Domains
4. Add `http://localhost/food-saver-php/pages/oauth_callback.php?provider=facebook` to Valid OAuth Redirect URIs
5. Test by clicking "Continue with Facebook" on login/register pages

### Test Accounts
- **Google**: Use your Google account or create a test account
- **Facebook**: Use your Facebook account or create a test account

---

## How It Works

### Login Flow

1. User clicks "Continue with Google" or "Continue with Facebook"
2. Redirected to OAuth provider's login page
3. User authorizes the app
4. Provider redirects back to `oauth_callback.php` with authorization code
5. App exchanges code for access token
6. App retrieves user profile information
7. App finds or creates user account
8. User is automatically logged in
9. Redirected to dashboard

### Registration Flow

1. User selects role (Donor/Restaurant/NGO)
2. Clicks "Sign up with Google" or "Sign up with Facebook"
3. Same flow as login, but new account is created with:
   - Email from OAuth provider
   - Name from OAuth provider
   - Account status set to 'active'
   - User type based on selected role

---

## Features

### Automatic Account Creation
- New users are automatically registered when using OAuth
- Username is generated from email
- Temporary password is set (not used in OAuth login)
- Account status is automatically set to 'active'

### Account Linking
- If a user already exists with the same email, they are logged in instead of creating a duplicate

### Multi-Role Support
- Users can authenticate as Donor, Restaurant, or NGO
- Admin users can also use OAuth for login

### Session Management
- OAuth tokens are stored in database
- User session is created just like regular login
- Last login timestamp is updated
- Activity is logged

### Error Handling
- CSRF protection with state parameter validation
- Rate limiting still applies
- User-friendly error messages
- Fallback to regular login on OAuth failure

---

## Security Considerations

1. **HTTPS Only (Production)**: Always use HTTPS in production for OAuth
2. **Token Storage**: OAuth tokens are stored as JSON in database
3. **State Parameter**: CSRF protection using state parameter validation
4. **Email Verification**: Users can update profile information after OAuth signup
5. **Password Reset**: Users can set/reset their password through password reset flow
6. **Scope Limitation**: Only request necessary OAuth scopes (email, profile)

---

## Troubleshooting

### Issue: "Invalid client_id"
- **Solution**: Verify Client ID is correct in `config.php`
- Check for extra spaces or special characters
- Ensure you copied the entire Client ID from credentials page

### Issue: "Redirect URI mismatch"
- **Solution**: Ensure redirect URIs match exactly in provider console
- Check protocol (http/https)
- Check trailing slashes and query parameters
- Test with both localhost and production domain

### Issue: "Missing email in profile"
- **Solution**: Ensure email scope is requested
- Check provider consent screen settings
- Request email scope in OAuth handler

### Issue: "Users not auto-logged in"
- **Solution**: Check if session is being created
- Verify OTP implementation doesn't interfere
- Check database columns are created properly
- Review error logs in browser console

### Issue: "OAuth buttons not working"
- **Solution**: Clear browser cache
- Check JavaScript console for errors
- Verify OAuth credentials are set
- Ensure config.php is properly updated

---

## Best Practices

1. **Environment Variables**: In production, store OAuth credentials in environment variables
2. **Token Refresh**: Implement token refresh for better security
3. **Email Verification**: Consider adding email verification step for OAuth signups
4. **Profile Completion**: Redirect new OAuth users to complete their profile
5. **Logging**: Monitor OAuth authentication attempts and errors
6. **Testing**: Test with multiple providers thoroughly before production rollout

---

## Support

For issues or questions:
1. Check browser console for JavaScript errors
2. Check server error logs
3. Verify all configuration settings
4. Test with different browsers
5. Ensure database schema is updated correctly

---

## Additional Resources

- [Google OAuth 2.0 Documentation](https://developers.google.com/identity/protocols/oauth2)
- [Facebook Login Documentation](https://developers.facebook.com/docs/facebook-login)
- [OWASP OAuth 2.0 Security Best Practices](https://tools.ietf.org/html/draft-ietf-oauth-security-topics)

