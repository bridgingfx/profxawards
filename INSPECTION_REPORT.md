# ProFX Awards — Codebase Inspection Report

**Repo:** `bridgingfx/profxawards` (local: `~/workspace/repos/bridgingfx-profxawards`)
**Date inspected:** 2026-09-23
**Branch:** `main` — only 2 commits, both 2026-09-23 ("Initial commit", "last commit")
**Method:** read-only inspection + `php -l` + PHPUnit + artisan boot + local `php -S` smoke tests (server stopped afterwards). No code changed.

---

## 1. What the app is / stack

- **Laravel 10.48.16** (PHP `^8.1`; local CLI ran PHP 8.3.6) built on the **SmartEnd CMS** commercial CMS skeleton (config `smartend.php`, all DB tables prefixed `smartend_`).
- Layout is a **cPanel shared-hosting deploy**: repo root IS the web docroot — root `index.php` is the Laravel front controller (`require core/vendor/autoload.php`, `core/bootstrap/app.php`); the actual Laravel app lives in `core/`. A `web.config` (IIS) and cPanel-style `.htaccess` are at root alongside.
- **ProFX Awards 2026** — "Global Financial & Online Trading Awards" site: CMS pages (home, fintech, categories, gallery, events, FAQ, contact), public **nomination form** (`/nomination`), magazine PDF download, admin dashboard at `/admin` (`BACKEND_PATH=admin`).
- Notable packages: `laravel/sanctum`, `socialite`, `rachidlaasri/laravel-installer` (first-run installer), `alexusmai/laravel-file-manager`, `joedixon/laravel-translation`, `maatwebsite/excel`, `spatie/laravel-feed|newsletter`, `torann/geoip`, `anhskohbo/no-captcha`.
- 4,572 tracked files, 513 PHP files. `vendor/` and `node_modules/` are installed locally (untracked). `core/public/build/` contains Vite-built hashed assets (`app-fabb30ea.js`, `app-30f0affc.css`).
- SQLite dev DB present: `core/database/database.sqlite` (2.1 MB, 36 tables, all `smartend_*`).

## 2. File inventory (top level)

| Path | Purpose |
|---|---|
| `index.php` | Laravel front controller (docroot) |
| `core/` | The Laravel app (app, config, routes, resources, database, tests) |
| `assets/` | Bundled frontend libs: dashboard, file-manager, frontend (css/fonts/images/js), installer, keditor, translation |
| `uploads/` | CMS user uploads (banners, contacts, media, topics, users, settings, sections, pattern, inbox). `.htaccess` denies `*.php` here — correct |
| `magazine/` | 30 MB `ProFXAwardsMagazine-DecemberEdition2025.pdf` (real content) |
| `cgi-bin/` | cPanel leftover; 9-byte stub `wp-blog-header.php` (`<?php ` only) + Laravel-ish `.htaccess` |
| `fix-live-cache.php` | Web-accessible cache/session/view/log wipe with hardcoded token; **self-deletes after running** (still present) |
| `.htaccess` (389 lines) | WordPress W3 Total Cache header blocks + WP rewrite block with **`RewriteBase /profxawrds/`** (typo'd subdirectory; wrong if served at domain root) |
| `web.config` | IIS rewrite to `index.php` |
| `google0c06d5df9608d691.html` | Google Search Console verification file |
| `robots.txt` | `Disallow:` (allows all) — fine |
| `favicon.ico`, `assets/index.html`, `uploads/index.html` | 0-byte placeholders |
| `fin.png` | small image asset at root |

Key app areas (`core/`): `app/Http/Controllers/Dashboard/*` (admin CRUD), `app/Http/Controllers/APIs/APIsController.php` (~2,360 lines, public read API + form submits keyed by `api_key` setting), `app/Models/*` (incl. `Nomination`, `NominationGroup`), `routes/web.php`, `routes/dashboard.php` (auth-protected admin), `routes/apis.php` (public JSON API), `resources/views/frontEnd/*` (home, nomination, award, categoriesAwards, fintech, Influencer, media, gallery, faq, contact, closed, winner redirect, etc.), 37 migrations, admin file-manager + translation manager.

## 3. Test results (exact)

- `php -l` on `index.php`, `routes/{web,dashboard,api}.php`, `Dashboard/NominationController.php`, `Models/Nomination.php`, `config/smartend.php` → **no syntax errors, all clean**.
- `./vendor/bin/phpunit` → **OK (2 tests, 2 assertions)** — but these are only Laravel's stock `ExampleTest` (Unit + Feature). **There are no real tests.**
- `php artisan --version` → Laravel Framework 10.48.16 (boots fine); `route:list` → ~1,350 lines of routes.
- HTTP smoke test (`php -S` on repo root):
  - `GET /` → **200**, 101 KB, title "PROFX Awards 2026 - Global Financial & Online Trading Awards"
  - `GET /nomination` → **200**, 40 KB
  - `GET /admin` → **302** → `/admin/login` (auth gate works)
  - `GET /nonexistent-xyz` → **302** → `/` (catch-all redirects to home instead of 404 — CMS behaviour; SEO-hostile)
- **`/core/.env` → HTTP 200, full file served publicly** (1,812 bytes incl. `APP_KEY`, DB/mail/pusher config). See §6.

## 4. Broken / missing-functionality findings

1. **Nomination submit is broken.** `Nomination` model uses table `nominations` (→ `smartend_nominations` with prefix). **No migration anywhere creates it** (`grep` across `database/` found nothing; DB has only 36 `smartend_*` tables). Verified: `Nomination::count()` → `SQLSTATE[HY000]: General error: 1 no such table: smartend_nominations`. Public `POST nominations/store` will 500 with a DB error.
2. **`NominationController` has no `index()` method** (only `store()`). Two routes point at the missing method: public `GET /nominations` (`web.php:124`) and admin `GET /admin/nominations` (`dashboard.php:104`) → both 500 `BadMethodCallException`. The two routes also share the duplicate name `nominations.index`.
3. **`NominationGroup` model is referenced nowhere** (dead code).
4. **No voting or judging features exist.** No Vote/Judge models, tables, routes, or views. If awards need voting/judging, it's not here.
5. `APP_URL=https://smartend.app` — vendor placeholder, wrong domain (affects generated URLs, mail links, sitemap).
6. `MAIL_DRIVER` empty in local `.env`; mail only works if configured via admin SMTP settings or real env on the server.
7. `.env` is missing several keys present in `.env.example` (NOCAPTCHA_*, BACKEND_PATH present; social/FACEBOOK_*, GOOGLE_MAPS_KEY, TIMEZONE, DATE_FORMAT etc. absent) — social login, maps, captcha silently disabled by empty defaults.

## 5. What's missing (docs / config / hygiene)

1. **README is a stub** (root README is 25 bytes: "profxawrds"; `core/README.md` is the stock Laravel readme). No setup, deploy, or env documentation.
2. **No deployment docs** — nothing describing the unusual root-as-docroot + `core/` layout, required Apache/IIS config, or the MySQL setup production needs (local `.env` points at SQLite with empty `DB_HOST`).
3. **No `.env.production` example**; no documented required env list for the live server.
4. **No real tests** — only the 2 Laravel example tests; nomination/API/admin flows untested.
5. **No CI** (no GitHub workflow files), and git history is just 2 commits with placeholder messages ("last commit").
6. `fix-live-cache.php` **must not be deployed** (or must be deleted immediately after use) — it's a web shell-ish cache wiper with a git-committed token.
7. Root `.htaccess` `RewriteBase /profxawrds/` is wrong for domain-root hosting and the W3TC WordPress blocks are dead weight / potential interference on a Laravel app.
8. `favicon.ico` is 0 bytes; `assets/index.html`, `uploads/index.html` are 0-byte placeholders.

## 6. Security red flags (verified)

1. **[CRITICAL] `core/.env` is publicly downloadable** (`GET /core/.env` → 200, full contents incl. `APP_KEY`). The root `.htaccess` contains no rule blocking `.env`/dotfiles, and the docroot is the repo root so `core/` is web-accessible. Anyone can pull the app key (cookie/session encryption, signed URLs) and any DB/mail credentials set on the server. **Fix before/at deploy: block dotfiles at server level or move docroot to `core/public`.**
2. **`APP_DEBUG=true` with `APP_ENV=production`** in `core/.env` — stack traces (incl. the nomination 500s) leak paths/config to visitors. (`.env` is git-ignored, so this is a server-config issue, not a repo leak.)
3. **`fix-live-cache.php` committed in git with hardcoded token `profx2026clear`** — wipes config cache, routes, sessions, views, and the laravel log via a URL. Token is in the repo history; delete the file from the repo and the server.
4. **Public POST endpoints with only `api_key` setting check** (`/subscribe`, `/comment`, `/order`, `/contact`, API form submits) — acceptable if the `api_key` webmaster setting is set to a strong random value; verify it's not empty/default on the server.
5. `core/check.html` (a dev gallery test page) is web-accessible at `/core/check.html` — harmless but shows dev leftovers are exposed; same class of issue as (1).
6. `cgi-bin/wp-blog-header.php` is a 9-byte stub — junk, not exploitable, but indicates the hosting account previously ran WordPress; ensure no stale WP files with known CVEs remain on the server.
7. No hardcoded passwords/API keys found in `config/` or `app/` (grep clean); `.env` is correctly git-ignored. `uploads/.htaccess` correctly denies `*.php`.

## Suggested next steps (for planning, not done)

- Decide docroot strategy: keep cPanel root-docroot + add `.htaccess` deny for `.*` files (quick), or move docroot to `core/public` (cleaner, needs host support).
- Write the missing `nominations` (+ `nomination_groups`?) migration, add `index()` to `NominationController` or remove dead routes, verify the nomination flow end-to-end.
- Remove `fix-live-cache.php`, set `APP_DEBUG=false`, correct `APP_URL`, configure MySQL + mail + `api_key` on the server, fix `RewriteBase`.
- Write a real README + deploy runbook; add a basic PHPUnit feature test for the nomination flow.

---

## Remediation applied (2026-09-23)

All critical/high items above were fixed and verified (fresh SQLite migrate, `php artisan test` 6/6 green, `route:cache` clean, HTTP smoke tests 200):

- **Nominations table**: added `core/database/migrations/2026_09_23_000001_create_nominations_table.php`; form submissions now persist (verified end-to-end).
- **Admin nominations page**: added `index()` + `destroy()` to `Dashboard\NominationController` and `core/resources/views/dashboard/nominations/list.blade.php`; removed the dead public `GET /nominations` route (was 500 + duplicate route name).
- **Artisan safety**: 12 dashboard controllers + `APIsController` now skip constructor permission checks / `exit()` when running in console — `route:list` and `route:cache` work again.
- **Secrets/config**: deleted `fix-live-cache.php`; `APP_DEBUG=false` in `.env.example`; `APP_URL=http://localhost`; removed the committed `DB_PASSWORD` value (rotate it if ever used in production); removed dead `NominationGroup` model and duplicate `use HasFactory`.
- **Tests**: `phpunit.xml` now uses SQLite `:memory:`; added `tests/Feature/NominationTest.php` (submit stores row, email-mismatch validation, admin auth redirect); fixed `$_SERVER['SERVER_NAME']` warning in `config/installer.php`.
- **Docs**: replaced the placeholder root README with setup + cPanel deploy runbook.

Notes: `core/.htaccess` already denies `.env`/`.json`/`.lock` — the earlier `/core/.env → 200` reading came from the PHP dev server (ignores `.htaccess`); re-verify on the live Apache host. Voting/judging features do not exist yet (nominations + CMS only).
