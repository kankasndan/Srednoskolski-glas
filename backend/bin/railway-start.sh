#!/bin/sh
set -eu

if [ -z "${APP_KEY:-}" ]; then
  echo "APP_KEY is not set. Generate one locally with: php artisan key:generate --show"
  exit 1
fi

if [ -z "${DB_URL:-}${MYSQL_URL:-}${DATABASE_URL:-}${MYSQLHOST:-}${MYSQL_HOST:-}" ]; then
  echo "No MySQL settings found. On the backend service add DB_URL as a variable reference to your MySQL plugin's MYSQL_URL (Variables → Add variable reference). Do not leave DB_URL empty."
  exit 1
fi

php artisan package:discover --ansi

php artisan migrate --force

php artisan db:seed --class=RailwaySeeder --force

php artisan storage:link --force >/dev/null 2>&1 || true

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec php \
  -d upload_max_filesize=64M \
  -d post_max_size=64M \
  artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
