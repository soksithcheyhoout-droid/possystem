# Deployment Guide - POS System

Your project is ready to deploy! Choose one of these free hosting options:

## Option 1: Render (Recommended - Easiest)

### Steps:
1. Go to https://render.com
2. Sign up with GitHub
3. Click "New +" → "Web Service"
4. Connect your GitHub repository
5. Select this repository
6. Render will auto-detect `render.yaml` and deploy automatically
7. Your app will be live at: `https://your-app-name.onrender.com`

**Advantages:**
- Free tier with 750 hours/month
- Auto-deploys on git push
- No configuration needed

---

## Option 2: Railway (Already Configured)

### Steps:
1. Go to https://railway.app
2. Sign up with GitHub
3. Click "New Project" → "Deploy from GitHub"
4. Select your repository
5. Railway will auto-detect and deploy
6. Your app will be live at: `https://your-app-name.railway.app`

**Advantages:**
- Free tier with $5/month credit
- Simple deployment
- Good for Laravel apps

---

## Option 3: Heroku (Free tier ended, but still available)

If you have Heroku account:
```bash
heroku login
heroku create your-app-name
git push heroku master
```

---

## Environment Variables to Set

After deployment, set these in your hosting provider's dashboard:

```
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:[your-app-key]
APP_URL=https://your-deployed-url.com
DB_CONNECTION=sqlite
GOOGLE_CLIENT_ID=[your-google-client-id]
GOOGLE_CLIENT_SECRET=[your-google-client-secret]
TELEGRAM_BOT_TOKEN=[your-telegram-bot-token]
TELEGRAM_CHAT_ID=[your-telegram-chat-id]
```

Get these values from:
- **APP_KEY**: Run `php artisan key:generate` locally
- **Google OAuth**: https://console.cloud.google.com
- **Telegram Bot**: https://t.me/BotFather

---

## Quick Start

**For Render:**
1. Push code to GitHub
2. Go to render.com
3. Connect GitHub repo
4. Done! It deploys automatically

**Your deployment is ready. Choose your platform and deploy now!**
