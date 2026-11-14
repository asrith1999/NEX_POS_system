#!/bin/sh
set -eu
: "${APP_KEY:?Set a persistent Laravel APP_KEY before starting}"
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
if [ "${POS_MIGRATE:-0}" = "1" ]; then
    php artisan migrate --force
fi
if [ "${POS_SEED:-0}" = "1" ]; then
    php artisan db:seed --force
fi
exec "$@"
