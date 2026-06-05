#!/bin/bash

# Script para testear la configuración de broadcasting de Reverb

echo "=== REVERB BROADCAST TESTING SCRIPT ==="
echo ""

# 1. Check Reverb status
echo "1️⃣  Checking Reverb status..."
sudo supervisorctl status reverb
echo ""

# 2. Check if listening on port 8080
echo "2️⃣  Checking if Reverb listening on port 8080..."
sudo ss -tlnp | grep 8080 || echo "❌ Not listening on 8080"
echo ""

# 3. Check latest logs
echo "3️⃣  Latest broadcast logs from Laravel..."
sudo tail -50 /var/www/dino/storage/logs/laravel.log | grep -i "broadcast\|debug" | tail -20
echo ""

# 4. Show nginx config for /app location
echo "4️⃣  Nginx /app location config..."
sudo grep -A 10 "location /app" /etc/nginx/sites-available/dino
echo ""

# 5. Test endpoint locally (if curl available)
echo "5️⃣  Testing broadcast auth endpoint locally..."
echo "   (requires valid auth token - skipping for now)"
echo ""

# 6. Show .env reverb config
echo "6️⃣  Checking .env REVERB configuration..."
grep "REVERB_" /var/www/dino/.env | head -10
echo ""

# 7. Check if PHP can access the file
echo "7️⃣  Checking if audio file exists..."
ls -lh /var/www/dino/public/sounds/bell.wav
echo ""

# 8. Check if the JS file is compiled
echo "8️⃣  Checking if JS file is compiled..."
ls -lh /var/www/dino/public/build/assets/filament-vendor-order-notification-*.js 2>/dev/null | head -1
echo ""

echo "=== TESTING PROCEDURE ==="
echo ""
echo "To test the full flow:"
echo ""
echo "1. Open browser DevTools (F12)"
echo "2. Go to Console tab"
echo "3. Reload page (Ctrl+Shift+R)"
echo "4. Look for message: 'Echo initialized for vendor: X'"
echo "5. Go to Network tab"
echo "6. Assign an order"
echo "7. Look for POST request to /broadcasting/auth"
echo "   - Click on request"
echo "   - Go to Response tab"
echo "   - Should see JSON or error message"
echo ""
echo "Then run:"
echo "   sudo tail -100 /var/www/dino/storage/logs/laravel.log | grep -i broadcast"
echo ""
echo "And check if you see 'Broadcasting auth' logs"
