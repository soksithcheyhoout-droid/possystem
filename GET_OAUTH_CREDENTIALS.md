# 🔑 Get OAuth Credentials (Not API Key)

## ⚠️ Important: API Key vs OAuth Credentials

You provided an **API Key**, but for Gmail login we need **OAuth 2.0 Credentials**:

- ❌ **API Key** (what you have): `AIzaSyAeW4zbz33y2nAXuBHPtiZv9corFiYDBvQ`
- ✅ **OAuth Client ID** (what we need): `123456789-abcdefg.apps.googleusercontent.com`
- ✅ **OAuth Client Secret** (what we need): `GOCSPX-abcdefghijklmnop`

## 🚀 Get the Correct Credentials

### Step 1: Go to Google Cloud Console
Visit: https://console.cloud.google.com/

### Step 2: Select Your Project
- If you already have a project, select it
- If not, create a new project

### Step 3: Go to Credentials
- Left sidebar: "APIs & Services" > "Credentials"

### Step 4: Create OAuth 2.0 Client ID
1. Click "Create Credentials"
2. Select "OAuth 2.0 Client IDs"
3. If prompted, configure OAuth consent screen first

### Step 5: Configure OAuth Client
- **Application type**: Web application
- **Name**: POS System OAuth
- **Authorized redirect URIs**: 
  ```
  http://localhost/auth/google/callback
  ```

### Step 6: Copy Your Credentials
After creating, you'll get:
- **Client ID**: (starts with numbers, ends with .apps.googleusercontent.com)
- **Client Secret**: (starts with GOCSPX-)

## 🔧 Add to Your .env File

Replace the empty values in your .env file:

```env
GOOGLE_CLIENT_ID=your_client_id_here
GOOGLE_CLIENT_SECRET=your_client_secret_here
```

## 🎯 Quick Test

After adding credentials:

```bash
php artisan config:clear
php artisan google:test
```

## 📱 Visual Guide Available

For step-by-step visual instructions:
Visit: http://localhost/auth/google/setup