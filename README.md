# Nexus POS — complete editable source

Vue 3 frontend, Laravel 12 backend, and MySQL or SQLite. The original
`backend.zip` and `frontend.zip` remain available; use the editable `backend/`
and `frontend/` directories for development and deployment.

Live demo: **https://asrith-nexus-pos.vercel.app/**

The editable source is on the review branch until the pull request is merged:

```powershell
git clone --branch codex/complete-project-setup-953b9b --single-branch https://github.com/asrith1999/NEX_POS_system.git
```

The implemented workflow is sign in → load products → build a cart → checkout
with a database transaction → update stock → sign out. Authentication uses a
server session and CSRF tokens. Prices and available stock are checked by the
server. This version does not include payment gateways, returns, product
administration, or multi-store accounting.

## Windows / local setup

Install PHP 8.2+ with PDO SQLite (or PDO MySQL), Composer 2, and Node.js 22.12+
or Node.js 24. Open two terminals in the cloned project.

**Backend:**

```powershell
cd backend
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item -ItemType File database/database.sqlite -ErrorAction SilentlyContinue
php artisan migrate
php artisan db:seed
php artisan serve --host=127.0.0.1 --port=8000
```

For MySQL, create an empty database and set `DB_CONNECTION=mysql`, `DB_HOST`,
`DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `backend/.env`
before migrating. `DB_CONNECTION=sqlite` is the simpler local default.

**Frontend:**

```powershell
cd frontend
npm ci
npm run dev -- --host 127.0.0.1
```

Open **http://127.0.0.1:5173**. Use the account already in your own database.
On a new **local development** database only, the seeder creates
`admin@gmail.com` / `123456`. Set `POS_ADMIN_EMAIL` and `POS_ADMIN_PASSWORD`
before the first seed to choose another initial account. Re-running the seed
does not overwrite existing passwords or reset product stock. The original
ZIPs are retained; existing databases are not migrated or reset by this code
change. Never run `migrate:fresh` on a database containing your work.

The frontend calls relative `/api` routes. Vite proxies them to Laravel,
including during `npm run preview`; keep the backend running. There is no
hard-coded `localhost` address in the built browser application.

## Docker and hosting

The root Dockerfile builds the Vue app and serves it together with Laravel on
one origin using FrankenPHP/Caddy. MySQL is persistent in the Compose volume.

```powershell
Copy-Item .env.docker.example .env
```

Fill in `POS_DB_PASSWORD`, `POS_DB_ROOT_PASSWORD`, and a unique
`POS_ADMIN_PASSWORD`. Generate `APP_KEY` with the installed PHP:

```powershell
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
docker compose up --build -d
```

Open **http://localhost:8080**. Keep the same `APP_KEY` across restarts. Set
`POS_SEED=0` after initial provisioning. `docker compose down` keeps data;
`docker compose down -v` deletes the database volume.

For a Docker-capable host, build this same Dockerfile, attach a persistent
MySQL service, and configure the environment from `compose.yaml`. Terminate
HTTPS at the host, set `APP_URL` to the HTTPS URL and
`SESSION_SECURE_COOKIE=true`, set `APP_DEBUG=false`, and configure the host's
health check to `GET /up`. Set `PORT` to its required HTTP port. Keep one app
replica while running startup migrations. Keep runtime secrets in the host's
environment settings. The verified Vercel deployment below runs the frontend
and backend together, with its database in TiDB Cloud.

## Vercel container deployment

`vercel.json` selects the Container preset and the existing root Dockerfile.
The Vue build and Laravel API stay on one HTTPS origin. The live project is
**asrith-nexus-pos** in the Vercel team **asriths-projects-5fec6fa1**.

Its MySQL-compatible TiDB Starter instance, **nexus-pos-db**, is in the
**nexus-pos** TiDB project in Singapore. The monthly spending limit is **$0**.
The Vercel integration has access to this POS project only; its database
connection variables are scoped to production. Sessions and cache use the
database so they survive across app instances.

The hosted database was initialized once with 12 tables, one owner login and
eight sample products. Login details are supplied privately to the owner.
The acceptance check left one synthetic order in the sample inventory.
The original local databases and their user passwords were preserved.

For another installation, the
[TiDB Cloud integration](https://vercel.com/marketplace/tidb-cloud) can connect
a separate empty MySQL-compatible cluster to a Vercel project. It supplies `TIDB_HOST`,
`TIDB_PORT`, `TIDB_USER`, `TIDB_PASSWORD` and `TIDB_DATABASE`; the Laravel
configuration accepts these directly. Standard `DB_*` settings still take
precedence for other MySQL hosts. TiDB connections use the system CA bundle
and verify the server certificate.

Use `.env.vercel.example` as the settings checklist. Store real values in
Vercel environment settings, never in Git or a browser bundle. Keep
`APP_KEY` stable, use database-backed sessions and cache, and set
`TRUST_PROXY=true` only behind the host's trusted reverse proxy. Local files
inside a function instance are temporary; do not use SQLite for this host.

Run `php artisan migrate --force` once against the connected database from
a trusted deployment terminal, then seed with a new, strong
`POS_ADMIN_PASSWORD` if this is a fresh installation. Existing user
passwords and product stock are preserved by repeat seeding. Keep
`POS_MIGRATE=0` and `POS_SEED=0` on autoscaling app instances so each cold
start does not migrate or seed the shared database.

Encrypted SQL connectivity, the container build and all 10 hosted HTTPS
acceptance checks passed. The checks cover login, CSRF protection, cart
validation, transaction rollback, stock updates and logout. See `VALIDATION.md`.

Deploy reviewed source explicitly through Vercel after changes. The current
deployment was uploaded from the review branch's source files; connect the
Git repository after review to configure automatic deployments.

## Verify

```powershell
cd backend
php artisan test
cd ../frontend
npm run build
```

Feature tests cover invalid credentials, server-side authorization, session
login/logout, valid checkout totals, rollback on insufficient stock, empty
carts, duplicate product rows, and repeat seeding. Laravel disables CSRF
validation inside its test harness; verify the browser flow against a running
backend as well. Local Compose checks require Docker. The cloud container
build and TiDB acceptance flow are recorded in `VALIDATION.md`.
