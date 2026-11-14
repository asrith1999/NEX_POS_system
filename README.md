# Nexus POS — complete editable source

Vue 3 frontend, Laravel 12 backend, and MySQL or SQLite. The original
`backend.zip` and `frontend.zip` remain available; use the editable `backend/`
and `frontend/` directories for development and deployment.

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
replica while running startup migrations. There are no provider credentials
or paid hosting resources in this repository. Hosting is configured here;
a provider deployment and database provisioning must still succeed before
there is a live URL. A standalone static frontend cannot run this POS.

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
backend as well. Docker build and MySQL acceptance checks require Docker.
