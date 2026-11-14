# Validation

Verified with PHP 8.4.27, the committed Composer lockfile and Node.js 24:

- `php artisan test --compact`: **8 tests passed, 24 assertions**.
- `npm ci` and `npm run build` in `frontend/`: passed.
- A real PHP HTTP server: unauthorized API access rejected; a login without
  a CSRF token rejected; login with a token succeeded; checkout saved an order
  and decremented stock; logout revoked access.

The acceptance test used a fresh synthetic SQLite database. The original ZIP
archives and existing user databases were not changed. MySQL concurrency,
the Docker/FrankenPHP image and a public hosting environment were not tested
in this workspace. Run those acceptance checks on the chosen Docker host.
