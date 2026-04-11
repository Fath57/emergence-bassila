#!/usr/bin/env bash
# One-shot provisioning script for the Dokku app "emergence-bassila".
#
# What it does (on the Dokku host, over SSH):
#   1. Preflight: verify SSH reachability.
#   2. Sets all application config vars in a single `dokku config:set` call.
#   3. Ensures a persistent storage directory and mounts it (idempotent).
#   4. Scales web=1 worker=1.
#   5. Installs /etc/cron.d/emergence-bassila for the Laravel scheduler.
#
# Usage:
#   APP_KEY='base64:...' ./scripts/dokku-setup.sh
#
# Optional overrides (env vars):
#   SSH_ALIAS     SSH alias for the Dokku host (default: digit_immo_server)
#   APP_NAME      Dokku app name (default: emergence-bassila)
#   STORAGE_DIR   Dokku storage directory name (default: ${APP_NAME}-storage)
#
# SECURITY NOTE:
#   APP_KEY and MAIL_PASSWORD are passed on the command line to `dokku
#   config:set`, which means they are briefly visible in /proc/<pid>/cmdline
#   on the remote host and may appear in dokku's audit log. For v1 this is
#   accepted because the Dokku host is single-tenant under the operator's
#   control. If you re-use this script in a multi-tenant environment, pipe
#   secrets via stdin instead (e.g. `dokku config:set --encoded` with base64
#   values from a heredoc).

set -euo pipefail

SSH_ALIAS="${SSH_ALIAS:-digit_immo_server}"
APP_NAME="${APP_NAME:-emergence-bassila}"
STORAGE_DIR="${STORAGE_DIR:-${APP_NAME}-storage}"
STORAGE_MOUNT="/var/lib/dokku/data/storage/${STORAGE_DIR}:/app/storage/app/public"

if [[ -z "${APP_KEY:-}" ]]; then
    echo "ERROR: APP_KEY environment variable is required."
    echo "Generate one with:"
    echo "  php -r 'echo \"base64:\".base64_encode(random_bytes(32)).\"\\n\";'"
    echo "Then re-run:"
    echo "  APP_KEY='base64:...' ./scripts/dokku-setup.sh"
    exit 1
fi

echo "==> [0/5] Preflight: SSH reachability to ${SSH_ALIAS}..."
if ! ssh -o BatchMode=yes -o ConnectTimeout=5 "${SSH_ALIAS}" true; then
    echo "ERROR: cannot reach ${SSH_ALIAS} via SSH. Check ~/.ssh/config and agent." >&2
    exit 1
fi

echo "==> [1/5] Setting config vars on ${APP_NAME}..."
ssh "${SSH_ALIAS}" "dokku config:set --no-restart ${APP_NAME} \
    APP_NAME='Emergence Bassila' \
    APP_ENV=production \
    APP_KEY='${APP_KEY}' \
    APP_DEBUG=false \
    APP_URL=http://${APP_NAME}.localhost \
    APP_LOCALE=fr \
    APP_FALLBACK_LOCALE=en \
    LOG_CHANNEL=errorlog \
    LOG_LEVEL=info \
    DB_CONNECTION=pgsql \
    SESSION_DRIVER=database \
    CACHE_STORE=database \
    QUEUE_CONNECTION=database \
    FILESYSTEM_DISK=public \
    MAIL_MAILER=smtp \
    MAIL_HOST=smtp.gmail.com \
    MAIL_PORT=587 \
    MAIL_USERNAME=noreply.fct@gmail.com \
    MAIL_PASSWORD=wmmwcyyhhizsgfux \
    MAIL_ENCRYPTION=tls \
    MAIL_FROM_ADDRESS=no-reply@suiviprojetsfct.org \
    MAIL_FROM_NAME='Emergence Bassila' \
    NPM_CONFIG_PRODUCTION=false \
    NODE_ENV=production \
    COMPOSER_NO_DEV=1"

echo "==> [2/5] Ensuring storage directory..."
ssh "${SSH_ALIAS}" "dokku storage:ensure-directory ${STORAGE_DIR}"

echo "==> [3/5] Mounting storage (idempotent check)..."
if ssh "${SSH_ALIAS}" "dokku storage:list ${APP_NAME}" | grep -qF "${STORAGE_MOUNT}"; then
    echo "    storage mount already configured, skipping"
else
    ssh "${SSH_ALIAS}" "dokku storage:mount ${APP_NAME} ${STORAGE_MOUNT}"
fi

echo "==> [4/5] Scaling processes (web=1 worker=1)..."
ssh "${SSH_ALIAS}" "dokku ps:scale ${APP_NAME} web=1 worker=1"

echo "==> [5/5] Installing Laravel scheduler cron on the host..."
ssh "${SSH_ALIAS}" "APP_NAME='${APP_NAME}' bash -s" <<'REMOTE'
set -euo pipefail
cat > "/etc/cron.d/${APP_NAME}" <<CRON
* * * * * dokku dokku enter ${APP_NAME} web bash -c "php /app/artisan schedule:run > /dev/null 2>&1"
CRON
chmod 0644 "/etc/cron.d/${APP_NAME}"
echo "installed /etc/cron.d/${APP_NAME}"
REMOTE

echo ""
echo "Done. Next steps:"
echo "  1. git remote add dokku dokku@${SSH_ALIAS}:${APP_NAME}"
echo "  2. git push dokku <your-branch>:main"
echo "  3. ssh ${SSH_ALIAS} 'dokku url ${APP_NAME}'"
echo "  4. ssh ${SSH_ALIAS} 'dokku config:set ${APP_NAME} APP_URL=<real-url>'"
