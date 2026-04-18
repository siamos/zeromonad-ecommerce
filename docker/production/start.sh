#!/bin/bash

cd /var/www/html

echo "=== Starting ZeroMonad Ecommerce ==="

cat > .env << EOF
APP_NAME="${APP_NAME:-ZeroMonad}"
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY:-}
APP_DEBUG=${APP_DEBUG:-false}
APP_TIMEZONE=${APP_TIMEZONE:-UTC}
APP_URL=${APP_URL:-http://localhost}
ASSET_URL=${ASSET_URL:-${APP_URL}}

APP_LOCALE=${APP_LOCALE:-en}
APP_FALLBACK_LOCALE=${APP_FALLBACK_LOCALE:-en}

BCRYPT_ROUNDS=${BCRYPT_ROUNDS:-12}

LOG_CHANNEL=${LOG_CHANNEL:-stack}
LOG_LEVEL=${LOG_LEVEL:-error}

DB_CONNECTION=mysql
DB_HOST=${DB_HOST:-mysql}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-zeromonad}
DB_USERNAME=${DB_USERNAME:-root}
DB_PASSWORD=${DB_PASSWORD:-}

SESSION_DRIVER=${SESSION_DRIVER:-redis}
SESSION_LIFETIME=${SESSION_LIFETIME:-120}
SESSION_SECURE_COOKIE=${SESSION_SECURE_COOKIE:-true}

FILESYSTEM_DISK=${FILESYSTEM_DISK:-local}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-redis}
CACHE_STORE=${CACHE_STORE:-redis}

REDIS_CLIENT=phpredis
REDIS_HOST=${REDIS_HOST:-redis}
REDIS_PASSWORD=${REDIS_PASSWORD:-null}
REDIS_PORT=${REDIS_PORT:-6379}

MAIL_MAILER=${MAIL_MAILER:-log}
MAIL_HOST="${MAIL_HOST:-127.0.0.1}"
MAIL_PORT=${MAIL_PORT:-2525}
MAIL_USERNAME="${MAIL_USERNAME:-null}"
MAIL_PASSWORD="${MAIL_PASSWORD:-null}"
MAIL_ENCRYPTION=${MAIL_ENCRYPTION:-null}
MAIL_FROM_ADDRESS="${MAIL_FROM_ADDRESS:-hello@zeromonad.com}"
MAIL_FROM_NAME="\${APP_NAME}"

OPENAI_API_KEY=${OPENAI_API_KEY:-}

STRIPE_KEY=${STRIPE_KEY:-}
STRIPE_SECRET=${STRIPE_SECRET:-}
STRIPE_WEBHOOK_SECRET=${STRIPE_WEBHOOK_SECRET:-}

VIVA_MERCHANT_ID=${VIVA_MERCHANT_ID:-}
VIVA_API_KEY=${VIVA_API_KEY:-}
VIVA_CLIENT_ID=${VIVA_CLIENT_ID:-}
VIVA_CLIENT_SECRET=${VIVA_CLIENT_SECRET:-}

CARDLINK_MERCHANT_ID=${CARDLINK_MERCHANT_ID:-}
CARDLINK_SHARED_SECRET=${CARDLINK_SHARED_SECRET:-}
EOF

if [ -z "$APP_KEY" ]; then
    echo "Generating APP_KEY..."
    php artisan key:generate --force
fi

echo "Starting supervisor in background..."
/usr/bin/supervisord -n -c /etc/supervisor/conf.d/laravel.conf &
SUPERVISOR_PID=$!

trap "kill -TERM $SUPERVISOR_PID 2>/dev/null; wait $SUPERVISOR_PID" TERM INT

sleep 3

echo "Enabling maintenance mode..."
php artisan down --retry=10 || true

echo "Waiting for MySQL..."
MAX_TRIES=30
TRIES=0
until php -r "try { new PDO('mysql:host=${DB_HOST:-mysql};port=${DB_PORT:-3306}', '${DB_USERNAME:-root}', '${DB_PASSWORD:-}'); echo 'OK'; } catch(Exception \$e) { exit(1); }" 2>/dev/null; do
    TRIES=$((TRIES + 1))
    [ $TRIES -ge $MAX_TRIES ] && echo "MySQL timeout, continuing..." && break
    sleep 2
done

php artisan config:clear || true
php artisan migrate --force || echo "WARNING: Migrations failed"
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true
php artisan storage:link 2>/dev/null || true

php artisan up
echo "=== Ready ==="

wait $SUPERVISOR_PID
