# Validation

Verified with PHP 8.4.27, the committed Composer lockfile and Node.js 24:

- `php artisan test --compact`: **8 tests passed, 24 assertions**.
- `npm ci` and `npm run build` in `frontend/`: passed.
- A real PHP HTTP server: unauthorized API access rejected; a login without
  a CSRF token rejected; login with a token succeeded; checkout saved an order
  and decremented stock; logout revoked access.

The local acceptance test used a fresh synthetic SQLite database. The
original ZIP archives and existing local user databases were preserved.

## Hosted acceptance

Verified on 2026-11-14 UTC at https://asrith-nexus-pos.vercel.app/:

- Vercel production deployment `dpl_Hcm56NiHZhSYAwwGgAS9weMPJKHi`: READY.
- Runtime source commit: `7ed1a8dc47d91e402cfe81525aa3405d0fef9d09`.
- Docker/FrankenPHP image build passed in approximately 3 minutes.
- TiDB 8.5.3 Starter in Singapore: actual encrypted SQL connection verified;
  server certificate verification and the system CA bundle are configured.
- One-time migrations and seeding passed: 12 tables, one owner login and
  eight sample products. The configured password matched its stored hash.
- The temporary initialization environment was stopped after completion.

All 10 public HTTPS acceptance checks passed:

1. Compiled frontend, assets, SPA route and backend health loaded.
2. Unauthenticated product and account requests returned 401.
3. A login without a CSRF token returned 419.
4. Incorrect credentials returned 401.
5. Login succeeded with a secure HttpOnly session cookie.
6. All eight starter products loaded from the hosted database.
7. Empty carts and duplicate product rows returned 422.
8. A multi-product checkout with insufficient stock returned 400 and
   rolled back all stock changes.
9. A valid checkout created an order and reduced stock by one.
10. Logout revoked access to protected API routes.

The live acceptance check created one synthetic order in the sample
inventory. Startup migrations and seeding remain disabled on autoscaling
instances. TiDB integration access is limited to the POS project and its
connection variables are production-only. The database spending limit is
$0. Secrets and the owner login are supplied outside the repository.

Local Docker Compose and concurrent high-load stock contention were not
tested. The deployed Vue login page was also inspected in a browser.
