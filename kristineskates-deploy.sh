#!/bin/bash
# kristineskates-deploy.sh
# Run on the server: bash kristineskates-deploy.sh
# Deploys files from a kristineskates-site-laravel.zip in /home/ubuntu

set -e

APP_DIR="/var/www/kristineskates.com"
BUILD_DIR="/home/ubuntu/kristineskates-deploy-tmp"
ZIP_FILE="/home/ubuntu/kristineskates-site-laravel.zip"

echo "🚀 Kristine Skates Deploy Script"
echo "================================="

# Check zip exists
if [ ! -f "$ZIP_FILE" ]; then
  echo "❌ $ZIP_FILE not found. Upload it first."
  exit 1
fi

# Extract
rm -rf "$BUILD_DIR"
mkdir -p "$BUILD_DIR"
unzip -q "$ZIP_FILE" -d "$BUILD_DIR"
echo "✅ Extracted $ZIP_FILE"

# Show version
if [ -f "$BUILD_DIR/VERSION.json" ]; then
  VERSION=$(cat "$BUILD_DIR/VERSION.json" | python3 -c "import sys,json; d=json.load(sys.stdin); print(f\"v{d['version']} ({d['date']})\")")
  echo "📦 Version: $VERSION"
  cp "$BUILD_DIR/VERSION.json" "$APP_DIR/VERSION.json"
fi

# Copy todo list
if [ -f "$BUILD_DIR/kristineskates-remaining-todos.md" ]; then
  cp "$BUILD_DIR/kristineskates-remaining-todos.md" "$APP_DIR/kristineskates-remaining-todos.md"
fi

# rsync each directory (--no-group avoids chgrp errors when running as ubuntu)
sudo rsync -av --checksum --no-group --no-times --no-perms "$BUILD_DIR/app/" "$APP_DIR/app/"
sudo rsync -av --checksum --no-group --no-times --no-perms "$BUILD_DIR/resources/" "$APP_DIR/resources/"
sudo rsync -av --checksum --no-group --no-times --no-perms "$BUILD_DIR/routes/" "$APP_DIR/routes/"

echo "✅ Files synced"

# Fix permissions
sudo chown -R www-data:www-data "$APP_DIR/app" "$APP_DIR/resources" "$APP_DIR/routes"
sudo chmod -R 644 "$APP_DIR/app" "$APP_DIR/resources" "$APP_DIR/routes"
sudo find "$APP_DIR/app" "$APP_DIR/resources" "$APP_DIR/routes" -type d -exec chmod 755 {} \;

echo "✅ Permissions fixed"

# Laravel cache clear
cd "$APP_DIR"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "✅ Caches cleared"

# Cleanup
rm -rf "$BUILD_DIR"

echo ""
echo "🎉 Deploy complete! Version: $VERSION"
