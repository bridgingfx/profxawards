# ProFX Awards

**Global Financial & Online Trading Awards** — public awards website with online nominations, CMS-managed pages, gallery, events, magazine PDF, admin dashboard, and a public JSON API.

Built on Laravel 10 (PHP 8.1+) using the SmartEnd CMS skeleton.

## Repository layout (cPanel-style)

This is **not** a standard Laravel layout. The repository root is the web document root:

```
profxawards/
├── index.php            ← public entry point (rewrites into core/)
├── .htaccess            ← cache rules + routing into core
├── core/                ← the Laravel application
│   ├── app/             ├── config/     ├── database/
│   ├── resources/views/ ├── routes/     ├── tests/
│   └── .env.example
├── uploads/             ← public uploads (banners, topics, settings…)
├── assets/              ← public frontend assets
└── INSPECTION_REPORT.md ← latest code audit
```

`index.php` at the root boots `core/` — do **not** move the app or serve `core/` directly.

## Requirements

- PHP >= 8.1 with extensions: `mbstring`, `xml`, `curl`, `sqlite3` (tests), **`gd`** (Composer install fails without it), `fileinfo`, `bcmath`, `ctype`, `tokenizer`, `openssl`, `pdo`
- Composer 2, Node 18+ / npm

## Local development

```bash
cd core
composer install
cp .env.example .env
php artisan key:generate
# SQLite (quickest):
#   set DB_CONNECTION=sqlite and touch database/database.sqlite
# MySQL: create DB + user, fill DB_* in .env
php artisan migrate --seed
npm install && npm run build   # Node 24: NODE_OPTIONS=--openssl-legacy-provider npm run build
```

Serve from the **repository root** (not `core/`):

```bash
cd /path/to/profxawards
php -S 127.0.0.1:8000
```

Then visit `http://127.0.0.1:8000` — admin login at `/admin/login`.

Tests (SQLite in-memory, no external DB needed):

```bash
cd core && php artisan test
```

## Key business flow: nominations

1. Visitor opens `/nomination` and submits the form → `POST nominations/store`
   (`Dashboard\NominationController@store`).
2. The submission is validated and stored in the `nominations` table
   (`2026_09_23_000001_create_nominations_table` migration).
3. Admins review/delete entries at `/admin/nominations`
   (requires login + `newsletter` permission flag).

## Deploying (shared hosting / cPanel)

1. Upload the repository so its **root is the domain's document root**.
2. In `core/`: `composer install --no-dev --optimize-autoloader`, `npm install && npm run build`.
3. Copy `core/.env.example` → `core/.env` and set:
   - `APP_URL=https://your-domain.com`
   - `APP_ENV=production`, **`APP_DEBUG=false`**
   - Real `DB_*` credentials and `DB_TABLE_PREFIX`
   - Run `php artisan key:generate`
4. `php artisan migrate --force` (never `--seed` on an existing production DB).
5. `php artisan config:cache && php artisan route:cache && php artisan view:cache`.
6. Ensure `storage/` and `bootstrap/cache/` are writable.
7. `core/.htaccess` already blocks direct access to `.env`, `.json`, `.lock`, `.md` files —
   verify on the live domain (e.g. `https://your-domain.com/core/.env` must **not** return 200).

## Security notes

- Never deploy with `APP_DEBUG=true` — it leaks stack traces and config.
- Never commit a real `.env` — only `.env.example` (with empty secrets) belongs in git.
- The old `fix-live-cache.php` helper (hardcoded URL token that wiped caches/sessions/logs)
  was removed for security; clear caches with `php artisan config:clear` /
  `php artisan route:clear` / `php artisan view:clear` instead.
- Rotate `APP_KEY` and DB credentials if the previously committed example values were ever
  used in production.

## Useful artisan commands

```bash
php artisan route:list          # ~1,350 routes; must print clean (no HTML echoes)
php artisan route:cache         # must succeed — duplicate route names break this
php artisan migrate --force
php artisan test
```
