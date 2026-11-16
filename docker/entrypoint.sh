#!/bin/sh
set -e

[ -f .env ] || cp .env.example .env

if [ -z "$APP_KEY" ] && grep -q '^APP_KEY=$' .env; then
    php artisan key:generate --force
fi

php artisan migrate --force

# Datos de demostración solo la primera vez (si no hay usuarios)
if [ "$SEED_DEMO" = "true" ]; then
    USUARIOS=$(php artisan tinker --execute='echo \App\Models\User::count();' | tail -n 1)
    if [ "$USUARIOS" = "0" ]; then
        php artisan db:seed --force
    fi
fi

exec apache2-foreground
