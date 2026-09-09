#!/bin/sh
set -eu

if [ -z "${APP_KEY:-}" ]; then
  echo "APP_KEY is not set. Generate one locally with: php artisan key:generate --show"
  exit 1
fi

# Local .env leftovers. Railway MySQL is not on this container's localhost.
case "${DB_HOST:-}" in
  127.0.0.1|localhost) unset DB_HOST ;;
esac

if [ -z "${DB_URL:-}" ]; then
  if [ -n "${MYSQL_URL:-}" ]; then
    export DB_URL="$MYSQL_URL"
  elif [ -n "${DATABASE_URL:-}" ]; then
    export DB_URL="$DATABASE_URL"
  fi
fi

if [ -z "${DB_URL:-}" ] && [ -n "${MYSQLHOST:-}${MYSQL_HOST:-}" ]; then
  export DB_HOST="${MYSQLHOST:-$MYSQL_HOST}"
  export DB_PORT="${MYSQLPORT:-${MYSQL_PORT:-3306}}"
  export DB_DATABASE="${MYSQLDATABASE:-${MYSQL_DATABASE:-railway}}"
  export DB_USERNAME="${MYSQLUSER:-${MYSQL_USER:-root}}"
  export DB_PASSWORD="${MYSQLPASSWORD:-${MYSQL_PASSWORD:-}}"
fi

export DB_CONNECTION="${DB_CONNECTION:-mysql}"

db_host=""
if [ -n "${DB_URL:-}" ]; then
  db_host=$(php -r 'echo parse_url(getenv("DB_URL") ?: "", PHP_URL_HOST) ?: "";')
else
  db_host="${DB_HOST:-}"
fi

case "$db_host" in
  ""|127.0.0.1|localhost)
    echo "Database host is '${db_host:-missing}'. Laravel is not receiving Railway MySQL."
    echo "On the backend service: Variables → New variable → Add a variable reference → pick the MySQL service → MYSQL_URL → name it DB_URL."
    echo "Do not set DB_HOST=127.0.0.1. Delete DB_HOST / DB_DATABASE / DB_USERNAME / DB_PASSWORD if they came from a local .env."
    exit 1
    ;;
esac

echo "MySQL host: $db_host"

is_loopback_url() {
  php -r '
    $url = getenv("CHECK_URL") ?: "";
    $host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?: ""));
    exit(in_array($host, ["localhost", "127.0.0.1", "::1"], true) ? 0 : 1);
  '
}

# FRONTEND_URL must be the public SPA host. OAuth and CORS break on localhost leftovers.
if [ -z "${FRONTEND_URL:-}" ]; then
  echo "FRONTEND_URL is not set."
  echo "Set it to https://<your-frontend>.up.railway.app (variable reference to frontend.RAILWAY_PUBLIC_DOMAIN)."
  exit 1
fi

FRONTEND_URL=$(printf '%s' "$FRONTEND_URL" | sed 's:/*$::')
export FRONTEND_URL
export CHECK_URL="$FRONTEND_URL"
if is_loopback_url; then
  echo "FRONTEND_URL is still localhost ($FRONTEND_URL)."
  echo "Delete the local value and set https://\${{frontend.RAILWAY_PUBLIC_DOMAIN}} on the backend service."
  exit 1
fi

if [ -z "${APP_URL:-}" ]; then
  echo "APP_URL is not set. Set https://\${{RAILWAY_PUBLIC_DOMAIN}} on the backend service."
  exit 1
fi

APP_URL=$(printf '%s' "$APP_URL" | sed 's:/*$::')
export APP_URL
export CHECK_URL="$APP_URL"
if is_loopback_url; then
  echo "APP_URL is still localhost ($APP_URL)."
  echo "Set https://\${{RAILWAY_PUBLIC_DOMAIN}} on the backend service."
  exit 1
fi

# Browser talks to /api on the SPA host (Next rewrite). Socialite must use the same
# host for OAuth callbacks or Google/Facebook return to localhost / the API host,
# the session cookie is missing, and login fails with auth_failed.
fix_oauth_redirect() {
  provider="$1"
  var_name="$2"
  eval "current=\${$var_name:-}"
  export CHECK_URL="$current"
  if [ -z "$current" ] || is_loopback_url; then
    value="${FRONTEND_URL}/api/auth/${provider}/callback"
    export "$var_name=$value"
    echo "Using $var_name=$value"
  else
    echo "Using $var_name=$current"
  fi
}

fix_oauth_redirect google GOOGLE_REDIRECT_URI
fix_oauth_redirect facebook FACEBOOK_REDIRECT_URI

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
