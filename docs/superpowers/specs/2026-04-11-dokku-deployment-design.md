# Dokku Deployment — Design Spec

**Date** : 2026-04-11
**Status** : Approved, ready for implementation planning
**Scope** : Initial Dokku deployment of the `emergence-bassila` Laravel app on the `digit_immo_server` host, using Dokku's default virtual host (no custom domain yet).

---

## 1. Goals & Non-Goals

### Goals

- Deploy the `emergence-bassila` Laravel 13 app to an existing Dokku host accessible via SSH alias `digit_immo_server`.
- Use the already-created Dokku app `emergence-bassila` and the already-linked Postgres service `emergence_bassila_db`.
- Enable: web serving, background queue worker, Laravel scheduler, persistent storage for user uploads, transactional email via Gmail SMTP.
- Automate migrations and Laravel cache priming at every deploy.
- Stay on Dokku's default virtual hostname for this first iteration.

### Non-Goals

- Custom domain / Let's Encrypt TLS. Will be a follow-up task.
- Sentry DSN configuration (will be added later once the Sentry project is created).
- Redis, S3, or any external cache/object-store — DB-backed session/cache/queue are sufficient for v1.
- Zero-downtime DB migrations strategies. We accept brief maintenance at release phase.
- CI/CD pipeline integration. Deploys are manual via `git push dokku`.

---

## 2. Known Environment Facts

| Item | Value |
| --- | --- |
| Dokku app name | `emergence-bassila` |
| Postgres service | `emergence_bassila_db` (already linked → injects `DATABASE_URL`) |
| SSH alias | `digit_immo_server` → `31.220.73.143`, `User root` |
| Dokku git remote URL | `dokku@digit_immo_server:emergence-bassila` |
| Current local branch | `001-bassila-network-platform` |
| Deploy target branch | Dokku-side `main` (via push mapping `local-branch:main`) |
| PHP version requirement | `^8.3` (from `composer.json`) |
| Laravel version | `^13.0` |
| Frontend toolchain | Vite 8 + Tailwind 4 + Tiptap (all devDependencies) |

---

## 3. Build Approach

**Chosen approach : Heroku multi-buildpack via `.buildpacks`**

Chain:

1. `heroku/nodejs` — installs `devDependencies` (Vite, Tailwind, Tiptap) and builds assets via the `heroku-postbuild` script.
2. `heroku/php` — installs Composer dependencies (`--no-dev`), sets up nginx + php-fpm runtime for serving `public/`.

**Rejected alternatives** :

- *Dockerfile custom* — more control but more maintenance burden, slower builds, not justified for v1.
- *Nixpacks* — less battle-tested for Laravel + Livewire + Vite stacks; harder to debug when it misbehaves.

---

## 4. Files Added to the Repository

| File | Purpose |
| --- | --- |
| `.buildpacks` | Declares the Node → PHP buildpack chain |
| `Procfile` | Defines `web`, `worker`, `release` process types |
| `nginx_app.conf` | Custom nginx config for Laravel front-controller routing on `public/` |
| `.slugignore` | Excludes dev-only files and `node_modules/` from the final slug |
| `bin/post_compile` | PHP-buildpack build hook: creates `public/storage` symlink during slug compile | Create |
| `scripts/dokku-setup.sh` | One-shot script: config vars, storage mount, process scaling, scheduler cron |
| `package.json` (modified) | Adds `heroku-postbuild` script |

### 4.1 `.buildpacks`

```text
https://github.com/heroku/heroku-buildpack-nodejs
https://github.com/heroku/heroku-buildpack-php
```

### 4.2 `Procfile`

```procfile
web: vendor/bin/heroku-php-nginx -C nginx_app.conf public/
worker: php artisan queue:work --tries=3 --timeout=90 --sleep=3
release: php artisan migrate --force
```

- `release` only runs `migrate --force`. Release-phase filesystem changes are not guaranteed to persist on Dokku's herokuish builds, so only DB-external state goes in release.
- `storage:link` runs in `bin/post_compile` (see §4.8) during build phase — baked into the slug.
- Laravel `*:cache` commands are intentionally NOT run in release. They can be added to `bin/post_compile` later once we confirm they don't need runtime-only env vars.
- `worker` is scaled as a separate container so queued jobs (emails, image processing) don't share PHP-FPM workers with web traffic.
- A failed `release` aborts the deploy and keeps the previous version live.

### 4.3 `nginx_app.conf`

Standard Laravel front-controller config:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

Dropped into the `server {}` block that `heroku-php-nginx` provides by default. No PHP location overrides needed.

### 4.4 `.slugignore`

Lines (one per pattern) :

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

### 4.5 `package.json` change

Add `"heroku-postbuild": "vite build"` to the `scripts` block so the Node buildpack explicitly runs the Vite build (rather than relying on implicit `build` invocation).

### 4.6 `scripts/dokku-setup.sh`

Idempotent bash script, run **once** after the deploy files are committed and before the first `git push dokku`. Responsibilities :

1. **Preflight**: SSH ping to verify host reachability before making any changes.
2. `dokku config:set --no-restart emergence-bassila ...` — push all environment variables in a single call.
3. `dokku storage:ensure-directory` + idempotent `dokku storage:mount` (guarded by `dokku storage:list | grep`) — persist user uploads; safe to re-run.
4. `dokku ps:scale emergence-bassila web=1 worker=1` — start 1 web + 1 worker container.
5. Install the Laravel scheduler cron entry in `/etc/cron.d/emergence-bassila` on the host.

The script SSHes into `digit_immo_server` via the existing alias. It treats `APP_KEY` as a required env var passed in by the operator, e.g. `APP_KEY=base64:... ./scripts/dokku-setup.sh`.

**Security note**: `APP_KEY` and `MAIL_PASSWORD` are passed as command-line arguments to `dokku config:set`, briefly visible in `/proc/<pid>/cmdline` on the remote host during execution. Accepted for v1 (single-tenant host). For multi-tenant environments, pipe secrets via stdin instead.

### 4.7 `bin/post_compile`

Heroku PHP buildpack post-compile hook. Runs during the build phase after `composer install`, inside the slug. Filesystem changes are baked into the final image.

```bash
#!/usr/bin/env bash
set -euo pipefail

echo "-----> post_compile: creating storage symlink"
php artisan storage:link
```

Cannot access runtime config vars (`APP_KEY`, `DATABASE_URL`) — those are only injected at release/runtime.

---

## 5. Environment Variables

Laravel 11+ parses `DATABASE_URL` automatically, so **no individual DB_HOST/DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD are set**. The Postgres link already injects `DATABASE_URL=postgres://...`.

### 5.1 Application

| Var | Value |
| --- | --- |
| `APP_NAME` | `"Emergence Bassila"` |
| `APP_ENV` | `production` |
| `APP_KEY` | Generated locally with `php artisan key:generate --show`, passed to setup script. Never committed. |
| `APP_DEBUG` | `false` |
| `APP_URL` | `http://emergence-bassila.localhost` *(placeholder — corrected after first deploy, see §7.3)* |
| `APP_LOCALE` | `fr` |
| `APP_FALLBACK_LOCALE` | `en` |

### 5.2 Logs

| Var | Value |
| --- | --- |
| `LOG_CHANNEL` | `errorlog` |
| `LOG_LEVEL` | `info` |

### 5.3 Database

| Var | Value |
| --- | --- |
| `DB_CONNECTION` | `pgsql` |

### 5.4 Session / Cache / Queue

| Var | Value |
| --- | --- |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |

### 5.5 Filesystem

| Var | Value |
| --- | --- |
| `FILESYSTEM_DISK` | `public` |

### 5.6 Mail (Gmail SMTP)

| Var | Value |
| --- | --- |
| `MAIL_MAILER` | `smtp` |
| `MAIL_HOST` | `smtp.gmail.com` |
| `MAIL_PORT` | `587` |
| `MAIL_USERNAME` | `noreply.fct@gmail.com` |
| `MAIL_PASSWORD` | `wmmwcyyhhizsgfux` |
| `MAIL_ENCRYPTION` | `tls` |
| `MAIL_FROM_ADDRESS` | `no-reply@suiviprojetsfct.org` |
| `MAIL_FROM_NAME` | `"Emergence Bassila"` |

### 5.7 Build-time

| Var | Value | Reason |
| --- | --- | --- |
| `NPM_CONFIG_PRODUCTION` | `false` | Force install of `devDependencies` (Vite, Tailwind) during Node phase |
| `NODE_ENV` | `production` | Runtime mode for Node (only affects third-party libs that read it) |
| `COMPOSER_NO_DEV` | `1` | Skip Pest, Pint, Larastan at deploy time |

### 5.8 Deliberately unset for now

- `SENTRY_LARAVEL_DSN` — to be added later when Sentry project is ready.
- Broadcasting credentials — not used in v1.

---

## 6. Server-Side One-Time Configuration

Performed by `scripts/dokku-setup.sh`:

### 6.1 Storage mount

```bash
dokku storage:ensure-directory emergence-bassila-storage
dokku storage:mount emergence-bassila \
  /var/lib/dokku/data/storage/emergence-bassila-storage:/app/storage/app/public
```

The release phase's `php artisan storage:link` creates `public/storage → storage/app/public`. Because the mount is on the host, uploads persist across deploys.

### 6.2 Process scaling

```bash
dokku ps:scale emergence-bassila web=1 worker=1
```

### 6.3 Laravel scheduler

System cron on the Dokku host (no extra plugin) — file `/etc/cron.d/emergence-bassila` :

```cron
* * * * * dokku dokku enter emergence-bassila web bash -c "php /app/artisan schedule:run >> /dev/null 2>&1"
```

Runs every minute as user `dokku`, enters the `web` container, and triggers Laravel's scheduler. If no scheduled tasks exist, `schedule:run` is a no-op.

### 6.4 Virtual host

Dokku automatically provisions a vhost under the host's global domain when `web=1`. No manual `dokku domains:add` needed while staying on the virtual domain. Retrieve after deploy with :

```bash
ssh digit_immo_server "dokku url emergence-bassila"
```

---

## 7. Deployment Flow

### 7.1 First deploy (checklist)

1. Commit the deploy files listed in §4 (ensuring WIP files already in `git status` are handled separately by the user — **not committed by the assistant automatically**).
2. Run `APP_KEY=base64:... ./scripts/dokku-setup.sh` from local machine to provision config, storage, scaling, and cron on the server.
3. Add git remote : `git remote add dokku dokku@digit_immo_server:emergence-bassila`.
4. Push : `git push dokku 001-bassila-network-platform:main`.
5. Observe : `ssh digit_immo_server "dokku logs emergence-bassila --tail"`.
6. Retrieve real URL : `ssh digit_immo_server "dokku url emergence-bassila"`.
7. Fix `APP_URL` : `ssh digit_immo_server "dokku config:set emergence-bassila APP_URL=<real-url>"` (triggers auto-redeploy).
8. Smoke test : `curl -I <real-url>` — expect `200 OK`.

### 7.2 Runtime sequence per push

```
git receive
  → heroku/nodejs buildpack (npm install, heroku-postbuild → vite build)
  → heroku/php buildpack (composer install --no-dev --optimize-autoloader)
  → bin/post_compile hook (php artisan storage:link)
  → slug compile (.slugignore applied)
  → release phase (php artisan migrate --force)
  → web process start (heroku-php-nginx on public/)
  → worker process start (queue:work)
  → zero-downtime container swap
```

A failed release phase aborts the deploy; the previous release stays live.

### 7.3 `APP_URL` bootstrap note

Dokku's virtual hostname is `<app>.<global-domain>` where `<global-domain>` is set on the host. We don't know it ahead of time, so we deploy with a placeholder `APP_URL=http://emergence-bassila.localhost`, read the real URL with `dokku url emergence-bassila`, then update `APP_URL` with a second `dokku config:set` which triggers a redeploy. Because `config:cache` is not run during release, Laravel picks up the new `APP_URL` on the next request without needing a manual `ps:rebuild`. This is a one-time bootstrap cost.

---

## 8. Observability & Troubleshooting

| Symptom | Primary check |
| --- | --- |
| Build fails on assets | `dokku logs emergence-bassila --tail` during push stream |
| Migrations fail | Push stream output during release phase |
| 500 at runtime | `dokku logs emergence-bassila` (stderr via `LOG_CHANNEL=errorlog`) |
| Uploads disappear on redeploy | `dokku storage:list emergence-bassila` |
| Scheduler not running | `crontab -l` on host + `dokku enter emergence-bassila web bash -c "php /app/artisan schedule:list"` |
| Emails not sent | `dokku logs emergence-bassila -p worker` |

---

## 9. Risks & Mitigations

| Risk | Mitigation |
| --- | --- |
| `config:cache` crashes if closures exist in `config/*.php` | Current repo uses standard Laravel config files; spot-check during first deploy and remove closures if found |
| `node_modules/` bloats slug | `.slugignore` filters it out at slug compile time |
| Gmail SMTP credential rotation | Password stored only in Dokku config (not in git); documented in §5.6 for reference |
| Branch mismatch between local feature branch and Dokku `main` | Use explicit push refspec `local:main`; document in §7.1 |
| Pre-existing uncommitted WIP in working tree | Explicitly call out in §7.1 — assistant does not auto-commit user's WIP |

---

## 10. Out of Scope (future tickets)

- Custom domain + Let's Encrypt TLS via `dokku-letsencrypt` plugin.
- Sentry DSN + release tagging.
- Redis-backed cache/queue/session (only if DB contention becomes a real issue).
- S3 filesystem driver for uploads (only if multi-host scaling is needed).
- Log shipping to an external service (Logtail, Papertrail).
- CI-driven deploys (GitHub Actions → `git push dokku`).
