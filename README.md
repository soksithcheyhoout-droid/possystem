# POS System with Google OAuth & Telegram Integration

A modern Point of Sale system built with Laravel 12, featuring Google OAuth authentication, Telegram bot integration, and comprehensive sales management.

## 🚀 Quick Deploy

### Deploy to Render (Recommended)
[![Deploy to Render](https://render.com/images/deploy-to-render-button.svg)](https://render.com/deploy?repo=https://github.com/soksithcheyhoout-droid/possystem)

### Deploy to Railway
[![Deploy on Railway](https://railway.app/button.svg)](https://railway.app/new/template?template=https://github.com/soksithcheyhoout-droid/possystem&envs=APP_ENV,APP_DEBUG,APP_KEY,APP_URL,DB_CONNECTION,GOOGLE_CLIENT_ID,GOOGLE_CLIENT_SECRET,TELEGRAM_BOT_TOKEN,TELEGRAM_CHAT_ID)

## ✨ Features

- **POS Management**: Complete sales and inventory management
- **Google OAuth**: Secure login with Google accounts
- **Telegram Bot**: Real-time sales notifications via Telegram
- **Customer Management**: Track customers and loyalty points
- **Product Management**: Manage products, categories, and pricing
- **Sales Reports**: Comprehensive analytics and reporting
- **Admin Dashboard**: Full control panel for administrators
- **Responsive Design**: Works on desktop and mobile devices

## 📋 Requirements

- PHP 8.2+
- Laravel 12
- SQLite (or MySQL/PostgreSQL)
- Node.js 18+ (for frontend assets)
- Composer

## 🔧 Local Setup

```bash
# Clone the repository
git clone https://github.com/soksithcheyhoout-droid/possystem.git
cd possystem

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Seed sample data
php artisan db:seed

# Build assets
npm run build

# Start development server
php artisan serve
```

## 🌐 Environment Variables

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:your-key-here
APP_URL=https://your-domain.com
DB_CONNECTION=sqlite

# Google OAuth
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret

# Telegram Bot
TELEGRAM_BOT_TOKEN=your-bot-token
TELEGRAM_CHAT_ID=your-chat-id
```

## 📱 Default Login

**Email**: admin@example.com  
**Password**: password

## 🔐 Security

- Google OAuth for secure authentication
- CSRF protection on all forms
- Password hashing with bcrypt
- SQL injection prevention
- XSS protection

## 📞 Support

For issues and questions, please open an issue on GitHub.

## 📄 License

This project is open source and available under the MIT License.

---

**Repository**: https://github.com/soksithcheyhoout-droid/possystem
