# Telegram Bot Setup Guide

This guide will help you set up Telegram notifications for your Mini Mart POS System.

## Step 1: Create a Telegram Bot

1. Open Telegram and search for `@BotFather`
2. Start a conversation with BotFather
3. Send the command `/newbot`
4. Follow the instructions:
   - Choose a name for your bot (e.g., "Mini Mart POS Bot")
   - Choose a username for your bot (must end with 'bot', e.g., "minimart_pos_bot")
5. BotFather will give you a **Bot Token** - save this!

## Step 2: Get Your Chat ID

### For Personal Chat:
1. Search for `@userinfobot` on Telegram
2. Start a conversation and send any message
3. The bot will reply with your user information including your **Chat ID**

### For Group Chat:
1. Add your bot to the group
2. Send a message in the group
3. Visit: `https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates`
4. Look for the "chat" object and find the "id" field (this is your **Chat ID**)

## Step 3: Configure Your Application

1. Open your `.env` file
2. Add the following lines:
```
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_CHAT_ID=your_chat_id_here
```

3. Replace `your_bot_token_here` with the token from BotFather
4. Replace `your_chat_id_here` with your Chat ID

## Step 4: Test the Connection

1. Go to Admin → Settings → Configure Telegram
2. Click "Test Connection"
3. You should receive a test message on Telegram

## Features

Once configured, you'll receive:

### 🛒 Payment Reports
- Automatic notifications for each completed sale
- Includes receipt number, customer info, items purchased, and payment details

### ⚠️ Low Stock Alerts
- Notifications when products fall below minimum stock levels
- Helps you restock before running out

### 📊 Daily Reports
- Manual or scheduled daily sales summaries
- Includes total sales, transaction count, and top-selling products

## Automatic Daily Reports

To send daily reports automatically, you can set up a cron job:

```bash
# Add this to your crontab to send reports at 11:59 PM daily
59 23 * * * cd /path/to/your/project && php artisan telegram:daily-report
```

## Troubleshooting

### Bot Token Issues:
- Make sure the token is correct and complete
- Ensure there are no extra spaces in the .env file

### Chat ID Issues:
- For group chats, the Chat ID might be negative (e.g., -123456789)
- Make sure the bot is added to the group and has permission to send messages

### Connection Issues:
- Check your internet connection
- Verify that your server can make outbound HTTPS requests
- Check the Laravel logs for detailed error messages

## Security Notes

- Keep your bot token secret - don't share it publicly
- Consider using environment variables for production deployments
- Regularly rotate your bot token if needed

## Support

If you encounter issues:
1. Check the Laravel logs: `storage/logs/laravel.log`
2. Test the bot token manually using Telegram's API
3. Verify your Chat ID is correct

Happy selling! 🛒📱