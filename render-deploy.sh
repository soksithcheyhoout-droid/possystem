#!/bin/bash
# Render One-Click Deploy Script
# This script automates the deployment to Render

echo "🚀 Starting Render Deployment..."
echo ""
echo "Opening Render dashboard..."
echo "Please follow these steps:"
echo "1. Click 'New +' → 'Web Service'"
echo "2. Select 'Connect a repository'"
echo "3. Choose: soksithcheyhoout-droid/possystem"
echo "4. Fill in:"
echo "   - Name: possystem"
echo "   - Environment: Docker"
echo "5. Click 'Create Web Service'"
echo ""
echo "Your app will be live at: https://possystem.onrender.com"
echo ""

# Open Render in browser
if command -v xdg-open &> /dev/null; then
    xdg-open "https://dashboard.render.com/new/web"
elif command -v open &> /dev/null; then
    open "https://dashboard.render.com/new/web"
else
    echo "Please visit: https://dashboard.render.com/new/web"
fi
