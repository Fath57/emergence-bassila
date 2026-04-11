# Dokku Deployment Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ship the `emergence-bassila` Laravel app to an existing Dokku host on `digit_immo_server` using Dokku's default virtual hostname, with queue worker, Laravel scheduler, persistent storage, and Gmail SMTP.

**Architecture:** Heroku multi-buildpack (Node → PHP). `Procfile` defines `web`/`worker`/`release` process types. Release phase runs migrations + Laravel cache priming on every deploy. Persistent uploads via `dokku storage:mount`. Scheduler via host-side cron invoking `dokku enter`.

**Tech Stack:** Laravel 13 (PHP 8.3+), Livewire 4, Vite 8, Tailwind 4, Postgres (via `DATABASE_URL`), nginx + php-fpm (from `heroku-buildpack-php`), Node 20 (from `heroku-buildpack-nodejs`).

**Reference spec:** `docs/superpowers/specs/2026-04-11-dokku-deployment-design.md`

---

## File Structure

| File | Responsibility | Status |
| --- | --- | --- |
| `.buildpacks` | Declare Node→PHP buildpack chain | Create |
| `Procfile` | web/worker/release process definitions | Create |
| `nginx_app.conf` | Laravel front-controller rewrite rule | Create |
| `.slugignore` | Exclude dev/CI files from the slug | Create |
| `app.json` | Declarative app description (buildpacks) | Create |
| `package.json` | Add `heroku-postbuild` script | Modify |
| `scripts/dokku-setup.sh` | One-shot server provisioning (config, storage, scale, cron) | Create |

Server-side effects (not files in the repo):

- Dokku config vars set on `emergence-bassila`
- Storage directory `emergence-bassila-storage` created + mounted to `/app/storage/app/public`
- `web=1 worker=1` scaling
- `/etc/cron.d/emergence-bassila` installed on the host

---

### Task 1: Create `.buildpacks`

**Files:**
- Create: `.buildpacks`

- [ ] **Step 1: Write the file**

Write `/opt/lampp/htdocs/personal/sites/emergence-bassila/.buildpacks` with exactly:

```text
https://github.com/heroku/heroku-buildpack-nodejs
https://github.com/heroku/heroku-buildpack-php
```

- [ ] **Step 2: Verify content**

Run: `cat .buildpacks`
Expected output:
```
https://github.com/heroku/heroku-buildpack-nodejs
https://github.com/heroku/heroku-buildpack-php
```

---

### Task 2: Create `nginx_app.conf`

**Files:**
- Create: `nginx_app.conf`

- [ ] **Step 1: Write the file**

Write `/opt/lampp/htdocs/personal/sites/emergence-bassila/nginx_app.conf` with exactly:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

- [ ] **Step 2: Verify content**

Run: `cat nginx_app.conf`
Expected: the three lines above.

Note: `heroku-php-nginx` injects this file inside its generated `server {}` block at runtime. Do not add a `server {}` wrapper.

---

### Task 3: Create `Procfile`

**Files:**
- Create: `Procfile`

- [ ] **Step 1: Write the file**

Write `/opt/lampp/htdocs/personal/sites/emergence-bassila/Procfile` with exactly:

```procfile
web: vendor/bin/heroku-php-nginx -C nginx_app.conf public/
worker: php artisan queue:work --tries=3 --timeout=90 --sleep=3
release: php artisan migrate --force && php artisan storage:link && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache
```

- [ ] **Step 2: Verify content**

Run: `cat Procfile`
Expected: three lines, one per process type (`web`, `worker`, `release`).

Key invariants:
- `web` uses `heroku-php-nginx` pointing at `public/` with the custom `nginx_app.conf`.
- `release` runs before web/worker boot; a failure aborts the deploy.

---

### Task 4: Modify `package.json` to add `heroku-postbuild` script

**Files:**
- Modify: `package.json`

- [ ] **Step 1: Read current `scripts` block**

Run: `cat package.json`

Confirm `scripts` currently contains:
```json
"scripts": {
    "build": "vite build",
    "dev": "vite"
},
```

- [ ] **Step 2: Add `heroku-postbuild` entry**

Edit `package.json` so the `scripts` block becomes:

```json
"scripts": {
    "build": "vite build",
    "dev": "vite",
    "heroku-postbuild": "vite build"
},
```

- [ ] **Step 3: Verify JSON is still valid**

Run: `php -r 'json_decode(file_get_contents("package.json"), true, 512, JSON_THROW_ON_ERROR); echo "ok\n";'`
Expected output: `ok`

---

### Task 5: Create `.slugignore`

**Files:**
- Create: `.slugignore`

- [ ] **Step 1: Write the file**

Write `/opt/lampp/htdocs/personal/sites/emergence-bassila/.slugignore` with exactly:

```text
node_modules/
tests/
docs/
specs/
.specify/
.idea/
.github/
phpstan.neon
pint.json
docker-compose.yml
CHANGELOG.md
CDC_Bassila_Network.md
README.md
```

- [ ] **Step 2: Verify content**

Run: `cat .slugignore`
Expected: the 13 lines above, in that order.

---

### Task 6: Create `app.json`

**Files:**
- Create: `app.json`

- [ ] **Step 1: Write the file**

Write `/opt/lampp/htdocs/personal/sites/emergence-bassila/app.json` with exactly:

```json
{
  "name": "emergence-bassila",
  "description": "Bassila Network Platform",
  "buildpacks": [
    { "url": "https://github.com/heroku/heroku-buildpack-nodejs" },
    { "url": "https://github.com/heroku/heroku-buildpack-php" }
  ]
}
```

- [ ] **Step 2: Verify JSON is valid**

Run: `php -r 'json_decode(file_get_contents("app.json"), true, 512, JSON_THROW_ON_ERROR); echo "ok\n";'`
Expected output: `ok`

---

### Task 7: Create `scripts/dokku-setup.sh`

**Files:**
- Create: `scripts/dokku-setup.sh`

- [ ] **Step 1: Ensure `scripts/` directory exists**

Run: `ls scripts/ 2>/dev/null || mkdir scripts && echo "ready"`
Expected: `ready` (or listing of existing scripts).

- [ ] **Step 2: Write the setup script**

Write `/opt/lampp/htdocs/personal/sites/emergence-bassila/scripts/dokku-setup.sh` with exactly:

```bash
#!/usr/bin/env bash
# One-shot provisioning script for the Dokku app "emergence-bassila".
#
# What it does (on the Dokku host, over SSH):
#   1. Sets all application config vars in a single `dokku config:set` call.
#   2. Ensures a persistent storage directory and mounts it to the app.
#   3. Scales web=1 worker=1.
#   4. Installs /etc/cron.d/emergence-bassila for the Laravel scheduler.
#
# Usage:
#   APP_KEY='base64:...' ./scripts/dokku-setup.sh
#
# Optional overrides (env vars):
#   SSH_ALIAS     SSH alias for the Dokku host (default: digit_immo_server)
#   APP_NAME      Dokku app name (default: emergence-bassila)
#   STORAGE_DIR   Dokku storage directory name (default: ${APP_NAME}-storage)

set -euo pipefail

SSH_ALIAS="${SSH_ALIAS:-digit_immo_server}"
APP_NAME="${APP_NAME:-emergence-bassila}"
STORAGE_DIR="${STORAGE_DIR:-${APP_NAME}-storage}"

if [[ -z "${APP_KEY:-}" ]]; then
    echo "ERROR: APP_KEY environment variable is required."
    echo "Generate one with:"
    echo "  php -r 'echo \"base64:\".base64_encode(random_bytes(32)).\"\\n\";'"
    echo "Then re-run:"
    echo "  APP_KEY='base64:...' ./scripts/dokku-setup.sh"
    exit 1
fi

echo "==> [1/4] Setting config vars on ${APP_NAME}..."
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

echo "==> [2/4] Ensuring storage directory and mount..."
ssh "${SSH_ALIAS}" "dokku storage:ensure-directory ${STORAGE_DIR}"
ssh "${SSH_ALIAS}" "dokku storage:mount ${APP_NAME} /var/lib/dokku/data/storage/${STORAGE_DIR}:/app/storage/app/public"

echo "==> [3/4] Scaling processes (web=1 worker=1)..."
ssh "${SSH_ALIAS}" "dokku ps:scale ${APP_NAME} web=1 worker=1"

echo "==> [4/4] Installing Laravel scheduler cron on the host..."
ssh "${SSH_ALIAS}" "APP_NAME='${APP_NAME}' bash -s" <<'REMOTE'
set -euo pipefail
cat > "/etc/cron.d/${APP_NAME}" <<CRON
* * * * * dokku dokku enter ${APP_NAME} web bash -c "php /app/artisan schedule:run >> /dev/null 2>&1"
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
```

- [ ] **Step 3: Make script executable**

Run: `chmod +x scripts/dokku-setup.sh`

- [ ] **Step 4: Syntax check with `bash -n`**

Run: `bash -n scripts/dokku-setup.sh && echo "syntax ok"`
Expected output: `syntax ok`

- [ ] **Step 5: Test dry-run of APP_KEY guard**

Run: `./scripts/dokku-setup.sh 2>&1 | head -5`
Expected: first line is `ERROR: APP_KEY environment variable is required.` and the script exits non-zero. This verifies the guard works without touching the server.

---

### Task 8: Commit all deploy artifacts in a single commit

**Files:**
- Commit: `.buildpacks`, `Procfile`, `nginx_app.conf`, `.slugignore`, `app.json`, `package.json`, `scripts/dokku-setup.sh`

- [ ] **Step 1: Stage only the new/modified deploy files**

Run:
```bash
git add .buildpacks Procfile nginx_app.conf .slugignore app.json package.json scripts/dokku-setup.sh
```

Do **not** use `git add -A` or `git add .` — there is pre-existing WIP in the working tree that the user will handle separately.

- [ ] **Step 2: Verify the staged file set**

Run: `git diff --cached --name-only`
Expected output (order may vary):
```
.buildpacks
.slugignore
Procfile
app.json
nginx_app.conf
package.json
scripts/dokku-setup.sh
```

- [ ] **Step 3: Create the commit**

Run:
```bash
git commit -m "$(cat <<'EOF'
feat(deploy): Dokku buildpack config + setup script

Adds Heroku multi-buildpack chain (Node then PHP), Procfile with
web/worker/release process types, Laravel nginx rewrite config,
slug filter, app.json descriptor, package.json heroku-postbuild
hook, and a one-shot scripts/dokku-setup.sh for server
provisioning (config vars, storage mount, scaling, scheduler cron).

Refs docs/superpowers/specs/2026-04-11-dokku-deployment-design.md

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
EOF
)"
```

- [ ] **Step 4: Confirm commit landed**

Run: `git log -1 --stat`
Expected: top commit has the 7 files listed above.

---

### Task 9: Generate `APP_KEY` locally (never committed)

- [ ] **Step 1: Generate a fresh Laravel-compatible key**

Run:
```bash
php -r 'echo "base64:".base64_encode(random_bytes(32))."\n";'
```

Expected output: a single line like `base64:XXXXXXXX...` (44 chars after the prefix).

- [ ] **Step 2: Save it to an environment variable in the current shell**

Run (replace with actual generated value):
```bash
export APP_KEY='base64:PASTE_GENERATED_VALUE_HERE'
```

Verify: `echo "$APP_KEY"` should print the `base64:...` value.

Do **not** write this value to any file. It must only exist in your shell session and in Dokku config.

---

### Task 10: Run `dokku-setup.sh` against the server

**⚠ This step modifies the remote Dokku server.** Requires user confirmation before execution.

- [ ] **Step 1: Sanity-check SSH connectivity**

Run: `ssh digit_immo_server "dokku apps:exists emergence-bassila && echo 'app exists'"`
Expected output: `app exists` (or the Dokku confirmation that the app is found). If this fails, stop and investigate SSH before continuing.

- [ ] **Step 2: Confirm the postgres link is in place**

Run: `ssh digit_immo_server "dokku config:get emergence-bassila DATABASE_URL"`
Expected: a `postgres://...` URL printed. If empty, stop — the postgres link needs to be re-applied before deploying.

- [ ] **Step 3: Execute the setup script**

Run (with `APP_KEY` already exported from Task 9):
```bash
./scripts/dokku-setup.sh
```

Expected output includes, in order:
```
==> [1/4] Setting config vars on emergence-bassila...
==> [2/4] Ensuring storage directory and mount...
==> [3/4] Scaling processes (web=1 worker=1)...
==> [4/4] Installing Laravel scheduler cron on the host...
installed /etc/cron.d/emergence-bassila

Done. Next steps:
  1. git remote add dokku dokku@digit_immo_server:emergence-bassila
  ...
```

- [ ] **Step 4: Spot-check each server-side effect**

Run:
```bash
ssh digit_immo_server "dokku config:get emergence-bassila APP_ENV"
```
Expected: `production`

Run:
```bash
ssh digit_immo_server "dokku storage:list emergence-bassila"
```
Expected: line containing `/var/lib/dokku/data/storage/emergence-bassila-storage:/app/storage/app/public`

Run:
```bash
ssh digit_immo_server "dokku ps:report emergence-bassila | grep -i 'Deployed\|Scale'"
```
Expected: shows `web: 1` and `worker: 1` (processes won't be "running" yet because no code has been pushed — scale is what we're checking here).

Run:
```bash
ssh digit_immo_server "cat /etc/cron.d/emergence-bassila"
```
Expected: the one-line cron entry we installed.

---

### Task 11: Add the `dokku` git remote locally

- [ ] **Step 1: Add the remote**

Run:
```bash
git remote add dokku dokku@digit_immo_server:emergence-bassila
```

- [ ] **Step 2: Verify remote is registered**

Run: `git remote -v`
Expected output contains:
```
dokku	dokku@digit_immo_server:emergence-bassila (fetch)
dokku	dokku@digit_immo_server:emergence-bassila (push)
```

If the remote already exists from a prior attempt, replace it with:
```bash
git remote set-url dokku dokku@digit_immo_server:emergence-bassila
```

---

### Task 12: First deployment push

**⚠ This triggers the first real build and deploy on the server.** Requires user confirmation before execution.

- [ ] **Step 1: Confirm the current branch contains the deploy commit**

Run: `git log --oneline -3`
Expected: top commit is the `feat(deploy): Dokku buildpack config + setup script` commit from Task 8.

- [ ] **Step 2: Confirm the current branch name**

Run: `git branch --show-current`
Expected: `001-bassila-network-platform` (the user's working branch). If different, adjust the push command below accordingly.

- [ ] **Step 3: Push to Dokku**

Run:
```bash
git push dokku 001-bassila-network-platform:main
```

Watch the streamed output for:
- `-----> Node.js app detected`
- `-----> PHP app detected`
- `-----> Discovering process types: Procfile declares types -> release, web, worker`
- `-----> Executing release: php artisan migrate --force ...`
- `-----> Releasing emergence-bassila (...)...`
- `-----> Deploying ...`
- `=====> Application deployed:` followed by a URL

Expected final line: URL of the deployed app (virtual domain).

**If the release phase fails:** the deploy is aborted, the push exits non-zero, and the old version (if any) stays live. Inspect the error in the push output. The most common first-deploy failures are:
- Missing `DATABASE_URL` → go back and re-link postgres on the server.
- `php artisan config:cache` failing because of closures in `config/*.php` → inspect which config file, remove the closure, commit, re-push.
- Missing PHP extension → the PHP buildpack enables common extensions automatically; if one is missing, add it to `composer.json`'s `require` block as `"ext-foo": "*"` and push again.

- [ ] **Step 4: Tail the logs to confirm web + worker are up**

Run:
```bash
ssh digit_immo_server "dokku logs emergence-bassila --tail --num 50"
```
Expected: lines from `heroku-php-nginx` starting up (web) and `queue:work` starting up (worker). No stack traces.

Press Ctrl-C to exit `--tail`.

---

### Task 13: Bootstrap `APP_URL` with the real vhost

The app was deployed with a placeholder `APP_URL=http://emergence-bassila.localhost` because we didn't know the real vhost before the first deploy. Now we fix it.

- [ ] **Step 1: Fetch the real URL**

Run:
```bash
ssh digit_immo_server "dokku url emergence-bassila"
```
Expected output: a single URL line like `http://emergence-bassila.<host-global-domain>`. Copy this value.

- [ ] **Step 2: Update the config var**

Run (replace with the real URL from Step 1):
```bash
ssh digit_immo_server "dokku config:set emergence-bassila APP_URL='http://emergence-bassila.REAL_HOST'"
```

This triggers an automatic redeploy. Watch for `=====> Application deployed:` in the SSH output.

- [ ] **Step 3: Verify the config change took effect**

Run:
```bash
ssh digit_immo_server "dokku config:get emergence-bassila APP_URL"
```
Expected: the URL you just set.

---

### Task 14: Smoke test + verification

- [ ] **Step 1: HTTP check on root**

Run (replace with actual URL):
```bash
curl -I http://emergence-bassila.REAL_HOST/
```
Expected: `HTTP/1.1 200 OK` (or `302` if the homepage redirects to login — either is a healthy response). **Not** `502 Bad Gateway` or `500 Internal Server Error`.

- [ ] **Step 2: Confirm no errors in logs**

Run:
```bash
ssh digit_immo_server "dokku logs emergence-bassila --num 100" | grep -iE "error|exception|fatal" || echo "no errors found"
```
Expected: `no errors found`. If any lines are printed, inspect them before claiming success.

- [ ] **Step 3: Confirm scheduler cron is registered and parseable**

Run:
```bash
ssh digit_immo_server "dokku enter emergence-bassila web bash -c 'php /app/artisan schedule:list'"
```
Expected: either a list of scheduled tasks or `No scheduled tasks have been defined.` — either is fine; the point is the command runs without error, proving the container environment is healthy.

- [ ] **Step 4: Confirm worker container is running**

Run:
```bash
ssh digit_immo_server "dokku ps:report emergence-bassila"
```
Expected: `Processes:` shows `web: 1` (running) and `worker: 1` (running).

- [ ] **Step 5: Report success**

Summarize to the user:
- Real URL
- Web + worker status
- That migrations ran
- That the scheduler cron is in place
- Any warnings seen in logs

---

## Self-Review Checklist (for plan author)

1. **Spec coverage:**
   - §3 Build approach → Tasks 1, 4, 6
   - §4 Files added → Tasks 1-7
   - §5 Env variables → Task 7 (encoded in `dokku-setup.sh`)
   - §6 Server-side config → Tasks 7, 10
   - §7.1 First deploy checklist → Tasks 8-13
   - §7.3 APP_URL bootstrap → Task 13
   - §8 Observability → Task 14

2. **Types/names consistency:** `emergence-bassila` (app), `emergence_bassila_db` (postgres service — note: underscores, set by the user and used in the spec §2), `emergence-bassila-storage` (storage dir — dashes for consistency with app name), `digit_immo_server` (SSH alias). Consistent across tasks.

3. **Placeholders:** `PASTE_GENERATED_VALUE_HERE` in Task 9 Step 2 is an intentional runtime substitution (the operator pastes the value from Task 9 Step 1). `REAL_HOST` in Task 13/14 is an intentional runtime substitution (the operator reads it from `dokku url` in Task 13 Step 1). No unresolved TBDs.

---

## Execution Notes

- Tasks 1-9 are local and safe to execute without extra confirmation.
- **Tasks 10, 12, 13 modify the remote Dokku server** and should pause for explicit user confirmation before running.
- Task 14 is read-only verification.
- If any task fails, stop and surface the error — do not retry blindly.
