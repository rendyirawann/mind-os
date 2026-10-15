#!/usr/bin/env bash
# Mind OS — update script. When the app was installed with `git clone` it
# pulls the latest commit from GitHub first; otherwise upload the code before
# running it:
#
#   sudo -u mindos -H env PHP=/usr/bin/php8.3 bash /var/www/mindos.mooda.id/deploy/deploy.sh
#
# Touches only /var/www/mindos.mooda.id and the "mindos" database. It does
# not reload nginx or PHP-FPM, so other sites on the server are untouched.
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/mindos.mooda.id}"
PHP="${PHP:-php}"
COMPOSER="${COMPOSER_BIN:-$(command -v composer)}"
cd "$APP_DIR"

if [ ! -f .env ]; then
    echo "ERROR: $APP_DIR/.env not found — copy deploy/env.production.example first." >&2
    exit 1
fi

if [ -d .git ]; then
    if git symbolic-ref -q HEAD >/dev/null; then
        echo "==> Pull latest code from GitHub ($(git rev-parse --abbrev-ref HEAD))"
        # Fast-forward only: never merges or overwrites local edits on the server.
        git pull --ff-only
    else
        # Rollback: an older commit was checked out on purpose — deploy it as is.
        echo "==> Detached at $(git rev-parse --short HEAD) (rollback) — skipping git pull"
    fi
fi

echo "==> Maintenance mode (this app only)"
$PHP artisan down --retry=30 || true

# Bring the site back up even if a step below fails.
trap '$PHP artisan up >/dev/null 2>&1 || true' EXIT

echo "==> Composer (production dependencies)"
# Run Composer with the same PHP binary as the app, not the server default.
$PHP "$COMPOSER" install --no-dev --prefer-dist --optimize-autoloader --no-interaction

echo "==> Database migrations"
$PHP artisan migrate --force

echo "==> Content seeders (idempotent; existing accounts are never overwritten)"
$PHP artisan db:seed --class=RolePermissionSeeder --force
$PHP artisan db:seed --class=MindOsSeeder --force

echo "==> Storage link"
[ -L public/storage ] || $PHP artisan storage:link

echo "==> Rebuild caches"
$PHP artisan optimize:clear
$PHP artisan optimize

echo "==> Back online"
$PHP artisan up
trap - EXIT

echo "Selesai. Cek: https://mindos.mooda.id"
