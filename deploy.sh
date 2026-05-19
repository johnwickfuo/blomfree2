#!/bin/bash
#
# BLOMFREE production deployment script.
# Run on the server, from the project root, after pulling new code.
#
set -euo pipefail

echo "BLOMFREE Deployment Update"
echo "=========================="

echo "1/7  Pulling latest from main..."
git fetch origin main
git checkout main
git reset --hard origin/main

echo "2/7  Installing composer dependencies (no-dev)..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "3/7  Running migrations..."
php artisan migrate --force

echo "4/7  Clearing stale caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

echo "5/7  Rebuilding production caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "6/7  Restarting queue workers (graceful)..."
php artisan queue:restart

echo "7/7  Resetting writable permissions..."
chmod -R 775 storage bootstrap/cache

echo ""
echo "Done. Site: $(grep '^APP_URL=' .env | cut -d= -f2-)"
echo ""
echo "Reminder: rebuild + commit public/build BEFORE pushing to main."
echo "    npm run build && git add public/build && git commit -m 'build: production assets' && git push origin main"
