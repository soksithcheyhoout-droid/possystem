# 🚀 Complete Google Gmail Login Setup Guide

## 📋 Step-by-Step Instructions

### Step 1: Create Google Cloud Project

1. **Go to Google Cloud Console**
   - Visit: https://console.cloud.google.com/
   - Sign in with your Google account

2. **Create New Project**
   - Click "Select a project" dropdown
   - Click "New Project"
   - Project name: "POS System OAuth"
   - Click "Create"

### Step 2: Enable Required APIs

1. **Navigate to APIs & Services**
   - In the left sidebar, click "APIs & Services" > "Library"

2. **Enable Google+ API**
   - Search for "Google+ API"
   - Click on it and press "Enable"

3. **Enable People API (Alternative)**
   - Search for "Google People API"
   - Click on it and press "Enable"

### Step 3: Configure OAuth Consent Screen

1. **Go to OAuth Consent Screen**
   - Left sidebar: "APIs & Services" > "OAuth consent screen"

2. **Choose User Type**
   - Select "External" (for public use)
   - Click "Create"

3. **Fill App Information**
   - App name: `POS System`
   - User support email: `your-email@gmail.com`
   - App logo: (optional)
   - App domain: `http://localhost` (for development)
   - Developer contact: `your-email@gmail.com`
   - Click "Save and Continue"

4. **Scopes (Step 2)**
   - Click "Add or Remove Scopes"
   - Select these scopes:
     - `../auth/userinfo.email`
     - `../auth/userinfo.profile`
     - `openid`
   - Click "Update" then "Save and Continue"

5. **Test Users (Step 3)**
   - Add your Gmail address as test user
   - Click "Save and Continue"

6. **Summary (Step 4)**
   - Review and click "Back to Dashboard"

### Step 4: Create OAuth 2.0 Credentials

1. **Go to Credentials**
   - Left sidebar: "APIs & Services" > "Credentials"

2. **Create OAuth Client ID**
   - Click "Create Credentials" > "OAuth 2.0 Client IDs"

3. **Configure Application**
   - Application type: "Web application"
   - Name: "POS System Web Client"

4. **Set Authorized Redirect URIs**
   - Click "Add URI"
   - Add: `http://localhost/auth/google/callback`
   - If using different port: `http://localhost:8000/auth/google/callback`

5. **Create and Copy Credentials**
   - Click "Create"
   - Copy the "Client ID" and "Client Secret"
   - Keep these safe!

### Step 5: Configure Your Laravel App

Add these to your `.env` file:

```env
GOOGLE_CLIENT_ID=your_client_id_here
GOOGLE_CLIENT_SECRET=your_client_secret_here
GOOGLE_REDIRECT_URL=http://localhost/auth/google/callback
```

### Step 6: Test the Setup

1. Run: `php artisan config:clear`
2. Visit: `http://localhost/login`
3. Click "Continue with Google"
4. Authorize with your Gmail account

## 🎯 Quick Setup Commands

```bash
# Clear config cache
php artisan config:clear

# Start development server
php artisan serve
```

## 🔧 Troubleshooting

### Common Issues:

**"Redirect URI mismatch"**
- Ensure exact URL match in Google Console
- Check for trailing slashes

**"Access blocked"**
- Complete OAuth consent screen setup
- Add your email as test user

**"Invalid client"**
- Verify Client ID and Secret in .env
- Check for extra spaces

## 🎉 Success Indicators

✅ Google login button appears on login page
✅ Clicking redirects to Google authorization
✅ After authorization, redirects back to POS dashboard
✅ User account created automatically
✅ Profile picture appears in navigation

## 📞 Need Help?

If you encounter issues:
1. Double-check all URLs match exactly
2. Ensure APIs are enabled
3. Verify OAuth consent screen is configured
4. Check .env file has correct credentials
5. Clear config cache with `php artisan config:clear`