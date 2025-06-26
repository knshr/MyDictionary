#!/bin/bash

echo "🚀 Starting Laravel Application with Queue Workers..."

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
while ! php artisan migrate:status > /dev/null 2>&1; do
    echo "Database not ready, waiting..."
    sleep 5
done

echo "✅ Database is ready!"

# Run migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

# Clear and cache config
echo "⚙️ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start queue worker in background
echo "👷 Starting queue worker..."
php artisan queue:work --sleep=3 --tries=3 --max-time=3600 &

# Keep the container running
echo "🎉 Application is ready!"
echo "📧 Queue worker is running in background"
echo "🌐 Web server should be accessible at http://localhost:8000"

# Wait for any background processes
wait
