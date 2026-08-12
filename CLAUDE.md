# KPS — IDL School New

School Management System. Laravel (13.x) backend + Vue 3 frontend. Dev runs locally via Laravel Herd at `http://idlschoolnew.test/`.

## ⚠️ Multi-tenant

The app is multi-tenant by **subdomain → database** mapping. `idlschoolnew` is only the super/base DB (holds the tenant registry `databases` + `tenants` tables). Each tenant gets its own DB named `{subdomain}idlschoolnew` (e.g. `sub.kps.test` → `subidlschoolnew`) — **real school data lives there**, not in `idlschoolnew`.

- `app/Http/Middleware/SubdomainDatabaseSwitcher.php` (global middleware, `app/Http/Kernel.php`) extracts the first host label as the subdomain, looks it up in the `databases` table (10-min cache), and hot-swaps the default connection to a dynamic MySQL connection (`db`/`username`/`password` from the row). Skips `super*` paths.
- Super admin panel at `/super` (login, run SQL, toggle tenant active) — `SuperAdminController`.
- `IdentifyTenant.php` middleware exists but is **not registered**.
- Errors: unknown subdomain → `errors.db_not_found` (404); connection failure → `errors.db` (500).

## Stack

- **Backend**: Laravel 13 (PHP 8.3), Sanctum auth, `laravel/ai`, maatwebsite/excel, spatie/laravel-permission, spatie/laravel-query-builder, tightenco/parental (single-table inheritance), OneSignal push/SMS.
- **Frontend**: Vue 3.5, Element Plus, Pinia, Vue Router 4, Vite 6, vue-i18n, echarts, unplugin-auto-import.
- **DB**: MySQL.

## Dev commands

```bash
npm run watch     # vite build --watch (frontend dev)
npm run build     # production frontend build
npm run lint      # eslint resources/js
php artisan ...   # standard Laravel commands (serve not needed — Herd serves the site)
vendor/bin/pint   # PHP formatting
```

## Architecture

- **Controllers** live in `app/Http/Controllers`, grouped by domain (Fees, Exams, Attendance, Accounting, ...). Many follow the custom **Laravue** pattern in `app/Laravue/` (controller scaffolding + Vue views in `resources/js/views`).
- **AI data agent** (query-your-school-data): `app/Ai/Agents/DataQueryAgent.php` uses `laravel/ai` with tool functions in `app/Ai/Tools/*` + shared helpers in `app/Ai/Tools/Concerns/SchoolQueryHelper.php`. Add new data questions as new tools there.
- **Mobile API**: `/api/v1/` routes in `routes/api.php`, guarded by `API_KEY` middleware.
- **Web routes**: `routes/web.php`. Frontend routes: `resources/js/router`.
- Fee voucher design documented in `FEE_VOUCHER_IMPLEMENTATION.md` (repo root).

## Conventions & gotchas

- **Pint** for PHP, **ESLint** for `resources/js`, **husky** pre-commit hooks.
- `auto-imports.d.ts` and `public/` build artifacts are generated — don't commit churn in them.
- `.gitignore` excludes `composer.lock`, `package-lock.json`, and `database/migrations` — committed migration history is not tracked in this repo.
- `README.md` still says "Laravel 10" — outdated, actual is 13.
- Environment: `.env` (local) / `.env.docker` + `docker-compose.yml` (containerized runs).
