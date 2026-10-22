# StreamAdmin → Laravel + Livewire Migration Plan

Target release: **3.0.0** (hard cutover once the full test suite passes).

## Decisions

| Topic | Decision |
|---|---|
| Framework | Latest Laravel + Livewire, PHP 8.4, MariaDB |
| Hosting | **Docker only.** All support for non-Docker hosting is removed |
| Cache / session / locks | **Valkey** (replaces Redis; Laravel `redis` driver via phpredis, protocol compatible) |
| Ingress | **cloudflared required.** Stack joins an external tunnel network; no ports published to the host |
| Models | YAPF generated models (`src/App/Models`, `src/Gen`) replaced by hand-written Eloquent models |
| Frontend | Tailwind v4 via Vite, Alpine (bundled with Livewire), **Flux UI free** components |
| jQuery | Removed entirely; Bootstrap 4, bootstrap-notify, DataTables, Popper, local FontAwesome removed |
| Tables | `rappasoft/laravel-livewire-tables` (Tailwind theme) |
| Schema | Renamed to Laravel conventions where it helps, via migrations that upgrade existing installs |
| Rollout | Built on a branch; single hard cutover after tests pass. No side-by-side running |

## Current state (reference)

| Area | Today | Size |
|---|---|---|
| Routing | YAPF `Switchboard` → `Endpoint/{Target}/{Module}/{Area}.php` | 7 switchboards |
| Models | YAPF `GenClass` + `CollectionSet` | 32 models + 32 sets |
| Admin UI | `View/*` (GET) + `Control/*` (POST/AJAX), swap-tag `.layout` themes | 130 views, 58 controls |
| In-world APIs | `api.php`, `hud.php`, `sys.php`, `ans.php` | 29 endpoints |
| Cron | `CronTab.php` fakes a signed SL POST, run by crontab several times a minute | 4 tasks + export |
| Schema | Raw SQL in `Versions/`, version in `slconfig.dbVersion` | 32 tables |
| Frontend JS | `site.js` (425 lines jQuery) + Bootstrap 4 JS | — |
| Tests | PHPUnit with globals (`$system`, `$_POST`) and SQL reset | 65 files |

## Hard constraints

1. **LSL contract is frozen.** In-world scripts cannot be updated in bulk, so the following must stay identical:
   - URLs: `/api.php/{Module}/{Area}`, `/hud.php/...`, `/sys.php/...`, `/ans.php/...`
   - Required POST fields and their names (`version`, `mode`, `objectuuid`, `regionname`, `ownerkey`, `ownername`, `pos`, `objectname`, `objecttype`, `unixtime`, `hash`)
   - Hash recipe: `sha1(unixtime . module . area . <static fields> . SlLinkCode)`, time window, `X-SecondLife-Shard` check
   - ANS `X-ANS-Verify-Hash` check
   - Response JSON: swap-tag keys unchanged, booleans as `"1"`/`"0"`, same HTTP status codes
   - **DB column renames must never leak into response keys.** Response keys are mapped explicitly, not derived from column names.
2. **Existing installs upgrade in place.** A 2.0.1.x database must migrate to the 3.0.0 schema without data loss.
3. **Existing staff logins keep working** (legacy salted sha256 hashes).
4. **Second Life traffic must pass through Cloudflare untouched.** LSL `llHTTPRequest` and Linden ANS callbacks cannot solve challenges, so `/api.php/*`, `/hud.php/*`, `/sys.php/*` and `/ans.php/*` need a WAF skip rule (Bot Fight Mode, managed challenges, Cloudflare Access off for these paths). `X-SecondLife-*` and `X-ANS-Verify-Hash` headers must reach the app.

## Docker-only cleanup

Removed because 3.0.0 only runs as the published image:

- Local DB fallback in `src/App/Db.php` (`localhost` / `root` / `test`). Laravel config reads env only and fails fast when required values are missing.
- `src/public_html/.htaccess` and every `index.html` directory-listing guard file (web server config lives in the image).
- Windows helper scripts: `cover.bat`, `dev.bat`, `fix.bat`, `gen.bat`, `login.bat`, `phpunit.bat`, `test.bat`, `update.bat`.
- Install-state leftovers in `.gitignore` (`db_installed.php`, `site_installed.php`, `ready.txt`, `r4.php`, `webpanel/`, `shared/`, `.ftpconfig`).
- XAMPP guidance in `CONTRIBUTING.md` → replaced with the Docker dev stack (below).
- The non-tunnel `docker-compose.yml` (published port `8082`) → replaced by a single cloudflared compose file.
- Redis service and `REDIS_*` / `SITE_CACHE_REDIS_HOST` naming → Valkey.

Development also runs in Docker: `docker-compose.dev.yml` with a bind-mounted source tree, a Vite dev server container, MariaDB, Valkey and Mailpit. Tests run inside the app container (`docker compose exec app php artisan test`).

## Schema rename policy

Applied in Phase 2 through migrations that run on both fresh and existing installs.

- Tables → plural snake_case (`rental` → `rentals`, `treevender` → `tree_vendors`, `slconfig` → `sl_configs`, …)
- Foreign keys `xxxLink` → `xxx_id` (`avatarLink` → `avatar_id`)
- camelCase columns → snake_case (`rentalUid` → `rental_uid`, `expireUnixtime` → `expire_unixtime`)
- Spelling fixes where the name is clearly wrong (`Rentalnoticeptout` → `rental_notice_opt_outs`, `vaildate` etc.)
- **Unix time integers stay integers** in 3.0.0 and use a custom `UnixTimestamp` cast. Converting to `timestamp` columns is deferred (timezone risk, LSL replies use unixtime).
- Existing FK constraints (36) are recreated after renames.
- A full mapping table (old → new) lives in `docs/schema-rename-map.md`, created in Phase 2 and used by the Excel export and golden-master tests.

## Progress (laravel/ on branch `laravel`)

- Phase 0: done (contract suite, legacy fixes).
- Phase 1: dev stack done (`laravel/docker-compose.dev.yml`: serversideup php 8.4 + nginx, MariaDB, Valkey, Vite, Mailpit). Production image, compose stack, TrustProxies and the Cloudflare/release docs are done (Phase 9 below).
- Phase 2: done. Baseline + rename migrations (`database/legacy/schema-map.php`), 29 Eloquent models + `Staff`, `UnixTimestamp` cast.
- Phase 3: started. `RentalService`, `StreamService`, `CatalogService`, `SalesService`, `MessagingService`, `BotService`, `SystemService`, `ObjectHealth`, `NoticeLevels`, `EventQueue`, `AuditLogger`, `Support\Swaps` (swap tags), `Support\PaymentKey`.
- Phase 4: done. `app/LegacyApi`: `Gateway` (api.php, hud.php, sys.php, ans.php; `routes/legacy.php`, plus `public/{api,hud,sys,ans}.php` shims so nginx hands bare `/ans.php` to Laravel), `SlEndpoint` / `HudEndpoint` / `SysEndpoint` (the 2.0.x request checks), `Input` (YAPF InputFilter semantics, including htmlentities on values and in request hashes), `Output` (swap tags, "1"/"0" booleans for api/hud), one class per endpoint under `Endpoints/`. Every request runs in a transaction that is rolled back when it ends with status false. The contract suite passes against both stacks: 227/227 steps (`docker compose -f contract-tests/docker-compose.yml run --rm runner-laravel verify`).
  - 2.0.x bug found and fixed in both (2.0.1.3): `Stock/Stocklevel` always reported 0 stock.
- Phase 5: done. Login (`LegacyStaffUserProvider`, bcrypt upgrade on first login, `owner` gate); password reset by Second Life name as in 2.0.x (`StaffPasswordReset`: 8 character code in `staff.email_reset_code`, sent as an in-world IM, valid 1 hour, rate limited, same answer for unknown names); `php artisan streamadmin:staff-password <username>` sets a password from the server (needed once on a fresh install, as the 2.0.x installer leaves admin without one; the dev stack uses `DevLoginSeeder`).
- Phase 6: shell, Dashboard, Clients (list + manage: adjust time, notes, transfer, revoke, message), Streams (list, create/edit, remove, restore, needs-work bulk update), Packages (list, create/edit, remove), Servers (capacity, add/edit, remove), Transactions, Reports, Resellers, Tree vendors, Coupons, Outbox (queue status + bulk message composer), Notices, Templates, Bot (owner), Health (service objects, vendors by region, object list), Avatars, Bans (owner), Texture packs, Audit log (owner), Staff (owner) and System settings (owner) done. Every 2.0.x admin page is ported.
  - Behaviour changes vs 2.0.x: bulk messages are all-or-nothing (every message is built and length-checked before any is queued); removing a notice level also removes clients' opt-outs of it (2.0.x refused); the bot avatar UID no longer has to be exactly 8 characters (the installer's `System` avatar is 6); owners set a new staff member's password (2.0.x set an unknown one); a changed password or removed account signs that staff member out everywhere (`AuthenticateSession`); removing an avatar moves their payments and audit entries to the system owner and removes their ban, objects and unsent mail; the audit log is searchable and paged (2.0.x showed the newest 25) and records switches as Yes/No; the 2.0.x list-display tweaks (full client list, items per page, server column, stream grouping) are not used by the new UI and are left untouched in `sl_configs`.
  - 2.0.x bug found: its texture pack audit entries never saved (store name longer than `audit_logs.store`); `AuditLogger` now clips to the column sizes.
- Phase 7: done. `routes/console.php` schedules `streamadmin:task botcommandq` (every 5 s; 2.0.x every 6 s), `streamadmin:task details-server` and `streamadmin:task dynamic-notecards` (every 10 s), and `streamadmin:export` (daily 03:00), each `->when()` the master switch (`SCHEDULER_ENABLED`, `config/streamadmin.php`) and its pause switch (`sl_configs.scheduler_*`, toggled by owners on the Health page) allow, with `withoutOverlapping()` + `onOneServer()`. `App\Services\Scheduler` runs a queue task the way 2.0.x CronTab.php did: a signed api.php call from the owner's virtual service object (`{n}0000000-…` in region `cron`), so the endpoint code and the Health page's service objects behave as before. Last run time, status and message per task are kept in the cache and shown on Health. `schedule:work` runs as an s6 service in the app container (`docker/s6/`, mirrors `/etc/s6-overlay`); `SCHEDULER_ENABLED` other than `true` leaves it down (dev defaults to off).
  - Export: `App\Services\SpreadsheetExport` (OpenSpout, no `gd` needed), same columns as 2.0.x Flow1. `streamadmin:export` keeps the newest `EXPORT_MAX_BACKUPS` files in `EXPORT_PATH` (default `storage/app/private/exports`), or writes one file with `--path=`. Owners download a fresh copy from System settings (audit logged).
  - 2.0.x bug: `Exporter.sh` called `CronTab.php -t=Export1`, which CronTab rejects (no `-d`, and `Export1` is not a task it knows), so the scheduled export wrote an error message into the .xlsx file instead of a spreadsheet.
- Review against 2.0.x (22 Oct 2026): every View/Control endpoint, in-world endpoint (all 4 entry points match file for file), cron task, helper and event-queue call was compared. Gaps found and closed:
  - Client create by hand (`Clients\Create`, `RentalService::create`: free L$0 transaction in region `website`, logged against the staff member's reseller entry), bulk remove of expired clients (`Clients\BulkRemove`, `purgeExpired`: queues `RentalEnd`, clears opt-outs, frees the stream as needs work), stream details preview and "send again" on the client page (2.0.x Getnotecard / Resend), a name/UID/UUID avatar picker for transfer and client create (2.0.x Avatar/Finder), global search (`/search`, sidebar box; 2.0.x Search page), the support key badge on the Dashboard (2.0.x showed a No key / Expired / days-left button).
  - Region and in-world object names are stored with `htmlentities()` as the API receives them; the app now reads them decoded (`Casts\HtmlEncoded`), so "Nova's Isle" is not shown as `Nova&#039;s Isle`. Avatar names need no cast: Second Life names are letters and digits only.
  - Differences kept on purpose: bulk remove skips clients with a details request waiting (2.0.x listed them but only skipped them in the view); a rented stream cannot be deleted (2.0.x allowed it); the system owner cannot be banned; the new-client notice level comes from `NoticeLevels` (2.0.x used its own loop); `Slconfig/Info` (phpinfo) and the per-user table settings (`Datatables`) are dropped; the 2.0.x "create a Server/Template/Package/Stream/Textureconfig first" redirect chain (Config page) is not ported.
- Tests: `php artisan test` (94 feature tests against MariaDB, fresh-install path) and the contract suite above.

## Phases

### Phase 0: Safety net (current codebase): done

- `contract-tests/`: black-box contract suite (Docker: MariaDB + legacy app + runner). 17 scenarios, 227 signed HTTP steps covering all 28 in-world endpoints (21 SL, 5 HUD, 1 sys, 1 ANS) including auth failures, queues, bot, events, HUD and time limits.
- Fixtures are framework-agnostic JSON; the runner verifies any target via `TARGET_URL`, upgrading the legacy schema with `RESET_HOOK` (so the Laravel run also exercises the 2.0.1.x → 3.0.0 migrations).
- Contract documented in `docs/lsl-contract.md`. Legacy defects found by the suite were fixed in the legacy code (2.0.1.3, see "Contract changes since 2.0.1.2"); legacy PHPUnit suite (233 tests) passes in Docker via `TEST_SQL_HOST`.

### Phase 1: Skeleton

- New Laravel app on branch `laravel` (built in `laravel/`, moved to the repo root at cutover).
- Install Livewire, Flux (free), Tailwind v4 via Vite, `rappasoft/laravel-livewire-tables`, `phpoffice/phpspreadsheet`, icon set (Heroicons via Flux).
- Map the existing Docker env vars (`SQL_HOST`, `SQL_DB_NAME`, `SQL_DB_USER`, `SQL_DB_PASS`, `SITE_NAME`, `SITE_URL`) in `config/*.php` so existing `.env` files keep working. New `VALKEY_HOST` / `VALKEY_PORT` / `VALKEY_PASSWORD` feed Laravel's `redis` connection config; session, cache, queue and lock stores all point at it.
- Proxy setup for cloudflared: `TrustProxies` trusts the tunnel network, client IP taken from `CF-Connecting-IP`, `URL::forceScheme('https')`, secure + `SameSite=Lax` session cookies.
- Docker dev stack (`docker-compose.dev.yml`) created here; it is the only supported dev environment.
- Test harness: Pest/PHPUnit with `RefreshDatabase` and a seeder equivalent to `Tests/reset.db.sql`.
- Base UI kit in `resources/views/components/ui/` around Flux: confirm dialog, toast stack, page header, form section.

### Phase 2: Schema + models

- Migration 1 (`baseline`): creates the 2.0.1.x schema from `Versions/installer.sql` + `Versions/2.0.1.1.sql`. Skipped (marked as run) when `slconfig` already exists and `dbVersion` is 2.0.1.x.
- Migration 2+ (`rename_*`): applies the rename policy per table.
- Update `dbVersion` to `3.0.0`.
- 32 Eloquent models: `$casts`, `UnixTimestamp` cast, `belongsTo` / `hasMany` relations, query scopes replacing `loadByX` / `Set` helpers.
- Model factories for tests.
- Delete `src/Gen/`, `gen.bat`.

### Phase 3: Services

- Port `src/App/Helpers/*` to `app/Services/*`: Avatar, Bot, EventsQ, Notices, Object, Region, Reseller, Swapables, Transactions.
- Extract shared logic out of `Template/*`: `useCredits`, `setupBot`, `sendMessageToAvatar`, `createAuditLog`, `createMultiAudit` → `CreditService`, `BotService`, `MessageService`, `AuditLogger`.
- `SlConfig` service wraps the single config row (`Cache::remember`, flushed on save). Timezone set from it in a service provider.
- Endpoints, Livewire components and console commands all call services; no business logic in controllers/components.

### Phase 4: In-world APIs (highest risk)

- Routes for `api.php/{module}/{area}`, `hud.php/...`, `sys.php/...`, `ans.php/...` mapped to invokable controllers. CSRF excluded.
- Middleware: `VerifySecondLifeRequest` (hash, time window, shard, object/region/reseller tracking), `VerifyHudRequest`, `VerifyAnsHash`.
- `SwapTagResponse` helper reproducing the current JSON shape exactly.
- Port all 29 endpoints. **Gate: every Phase 0 golden-master fixture passes unchanged.**
- Web server config: ensure `*.php/...` paths reach Laravel's `public/index.php` (no physical `api.php` files left in `public/`).

### Phase 5: Auth

- `staff` guard + Eloquent provider on `Staff`.
- `LegacyStaffHasher`: verifies old `phash`/`psalt`; on success rehashes to bcrypt into a new `password` column and clears the legacy fields.
- Password reset flows (`Reset`, `Resetwithtoken`, `Resetnow`) → Laravel password broker; delivery via the existing in-world message/bot path.
- Laravel session (Valkey) replaces `lhash` / `nextcheck` / `autologout`; idle timeout middleware keeps the 10-minute behaviour.
- `owner` gate for `ownerLevel` checks.

### Phase 6: Admin UI (Livewire + Flux + Tailwind)

- Layout: Flux sidebar layout replacing `src/App/Theme/sidemenu/*`; menu from `src/App/Framework/Menu.php`.
- Each `View/{Module}` + `Control/{Module}` pair → Livewire full-page components (`Index`, `Create`, `Manage`, …) with Livewire form objects and validation rules replacing `InputFilter`.
- URLs preserved where sensible (`/client/manage/{rentalUid}`).
- Lists → rappasoft tables (search, sort, filters, bulk actions).

jQuery replacement map:

| Current | Replacement |
|---|---|
| `.confirmDialog` + `#confirmModal` | `wire:confirm` or Flux modal calling a Livewire action |
| `data-toggle="modal"`, `#NotecardModal` | Flux modal; content via Livewire action |
| `$.ajax` forms (`.ajax`, `.ajaxAndCloseModal`, `.ajaxonpageload`) | Livewire actions / form objects, `wire:init` |
| `.notify()` + `bubblemessage` query param | Flux toast via `dispatch()`; session flash after redirects |
| `.DataTable()` / server-side datatables | rappasoft table component |
| `.avatarfinderajax` | `AvatarPicker` Livewire component (Flux autocomplete) |
| `.inputwithlimit` | Alpine character counter |
| `.bulkactiontoggle`, `.bulksenduncheck` | rappasoft bulk actions / `$selected` array |
| `data-toggle="buttons"` groups | Flux radio/checkbox groups bound with `wire:model` |
| `data-toggle="tooltip"` | Flux tooltip |
| `#logintimeleft` | Alpine countdown from session expiry |

Module order (simplest → hardest):

1. Login, Home, Health
2. Slconfig, Textureconfig, Template, Servertypes
3. Server, Package, Stream
4. Avatar, Reseller, Banlist, Staff
5. Client (rentals)
6. Notice, Outbox/Mailer, Bot, Tree, Objects, Coupons
7. Transactions, Reports, Search, Auditlog, Export

Delete `src/public_html/3rdparty/`, `js/site.js`, `css/site.css` at cutover.

### Phase 7: Cron + export

- Artisan commands calling services directly (no fake signed POST): `sa:botcommandq`, `sa:details-server`, `sa:dynamic-notecards`, `sa:export-flow1`.
- Schedule in `routes/console.php` with `everyTenSeconds()` / `everyFiveSeconds()` to match the current crontab cadence, `withoutOverlapping()`.
- **No separate cron container.** Today the stack needs two services from one image (`SA7Website` + `SA7Cron` with a different entrypoint). In 3.0.0 a single `SA7App` container runs both, controlled by flags:
  - `SCHEDULER_ENABLED` env (default `true`): the container entrypoint starts `supervisord` with two programs, the web server and `php artisan schedule:work`. When `false`, only the web server starts.
  - The same flag is read in-app (`config('streamadmin.scheduler.enabled')`) and guards every scheduled task with `->when(...)`, so a stray `schedule:run` with the flag off does nothing.
  - Per-task toggles stored in the settings table (`scheduler_botcommandq`, `scheduler_details_server`, `scheduler_dynamic_notecards`, `scheduler_export`), editable by owners in the admin UI (Slconfig page) and checked by each task's `->when(...)`. Lets a task be paused without a redeploy.
  - Every task uses `->onOneServer()` + `->withoutOverlapping()` with Valkey locks, so running more than one app container with the flag on can never double-run a task.
  - Health page shows last run time and status per task (written to cache by `->onSuccess()` / `->onFailure()`).
- `.docker/CronEntrypoint.sh` deleted.
- `Exporter.sh` calls `php artisan sa:export-flow1 --path=...`; column headers keep their current (pre-rename) labels.

### Optimisation pass (done, ahead of Phase 9)

- **`laravel/Dockerfile`** (production image, pulled forward from Phase 9; the compose file is still to do). Multi-stage on `serversideup/php:8.4-fpm-nginx`:
  - PHP stage: `composer install --no-dev`, `dump-autoload --optimize --classmap-authoritative`.
  - Node stage: `vite build`, then `docker/precompress.mjs` writes `.br` and `.gz` next to every built asset.
  - nginx stage: builds `ngx_brotli` as a dynamic module against the exact nginx version in the base image; `docker/nginx/brotli.conf` turns on `brotli` (level 5, text types), `brotli_static` and `gzip_static`.
- **Caches**: the base image's `AUTORUN_ENABLED=true` runs migrations then `php artisan optimize` (config, events, routes, views, blade-icons) on every start, so the config cache is built from the run-time environment. Verified in a built container against a fresh database: `artisan about` shows all four CACHED.
- **OPcache** (image `ENV`): `validate_timestamps=0`, `memory_consumption=256`, `max_accelerated_files=20000`. A code change means a new image.
- **Valkey**: `.env.example` is now the production template with `SESSION_DRIVER`, `CACHE_STORE` and `QUEUE_CONNECTION` on `redis` (Valkey, phpredis). Sessions confirmed landing in Valkey from the built image.
- **Sizes measured** on the built CSS: 371 KB plain, 43 KB gzip, 32 KB Brotli; login page HTML 17 KB, 3.7 KB Brotli.
- **Queries** (migration `2026_10_22_000400_add_performance_indexes`): the 2.0.x schema only indexed foreign keys. Added `rentals.expire_unixtime` (notice server tick, next to expire), `transactions (unixtime, amount)` (newest first, report ranges, by-year totals read the index only), `audit_logs.unixtime` and `.store` (sorted/polled list, area filter), `avatars.avatar_name` (pickers and searches). `EXPLAIN` on 300k transaction/audit rows and 100k rentals shows each using its new index (no filesort on the lists; the area filter is a loose index scan).
- The display timezone was looked up with a join on every web request and Livewire poll; it is now cached (1 h) and dropped whenever `SlConfig` is saved.
- Not changed on purpose: `LIKE '%x%'` searches on names and UIDs (cannot use an index; limited to 8 rows or paginated).

### Phase 8: Tests

- Port the 65 PHPUnit tests: HTTP feature tests for APIs, `Livewire::test()` for UI, unit tests for services.
- Golden-master suite kept permanently.
- Upgrade test: load a 2.0.1.x dump, run migrations, assert data integrity and a legacy staff login.
- CI: GitHub Action running the suite before the Docker build.

### Phase 9: Docker stack + cutover

**Status:** stack done and tested; cutover cleanup (last bullet) still to do.

- Done: `laravel/Dockerfile` (see the optimisation pass), `laravel/docker-compose.yml` (`SA7App`, `SA7Db`, `SA7Valkey`, optional `SA7PHPmyAdmin` under `--profile tools`; no published ports; external tunnel network `TUNNEL_NETWORK`, default `playerdb`; `DB_VOLUME` to reuse a 2.0.x database volume), `.env.example`, `trustProxies` (`TRUSTED_PROXIES`, default `*`; `ProxyTest`), `docs/cloudflare.md`, `docs/RELEASE_3.0.0.md`.
- Verified by `docker compose up` from the built image on a fresh database and an external network: all three services healthy, migrations and caches run on start, scheduler process running, `/login` and `/up` answered over the tunnel network with Brotli, no host ports.
- Differences from the plan below: the app listens on **8080** (non-root image), not 80, and the scheduler runs under s6 (not supervisord).

- Dockerfile (multi-stage): Node stage builds Vite assets; PHP 8.4 stage with `phpredis`, `pdo_mysql`, `gd`/`zip` (PhpSpreadsheet); docroot `public/`; `composer install --no-dev`; `php artisan optimize`; entrypoint runs `php artisan migrate --force --isolated` (Valkey lock, safe with several app containers), then starts `supervisord`.
- One compose file, `docker-compose.yml`, based on today's `docker-compose.cloudflared.yml`:

  | Service | Notes |
  |---|---|
  | `SA7App` | Web + scheduler in one container (`supervisord`), scheduler controlled by `SCHEDULER_ENABLED`. No published ports. Networks: `streamadmin` + external tunnel network. cloudflared ingress → `http://SA7App:80` |
  | `SA7Db` | MariaDB, volume `streamadmin-db` |
  | `SA7Valkey` | `valkey/valkey` (pinned major), password via `VALKEY_PASSWORD`, AOF volume `streamadmin-valkey` so sessions survive restarts |
  | `SA7PHPmyAdmin` | Optional (`profiles: [tools]`), bound to `127.0.0.1` only |

- External tunnel network name configurable: `TUNNEL_NETWORK` (default `playerdb`), declared `external: true`.
- `.env.example` (replaces `.env.cloudflared.example`): `STREAMADMIN_TAG`, `SITE_URL` (public tunnel hostname, required), `SQL_DB_PASS` (required), `VALKEY_PASSWORD` (required), `APP_KEY` (required), `SCHEDULER_ENABLED` (default `true`), `TUNNEL_NETWORK`, `SITE_NAME`, `PMA_PORT`.
- Docs: `docs/cloudflare.md` covering tunnel ingress, the WAF skip rule for the SL/ANS paths, and recommending Cloudflare Access on the admin UI only.
- Release notes: **back up the database before upgrading**; `SA7Website` + `SA7Cron` merged into `SA7App` (update the cloudflared ingress target); Redis → Valkey (sessions reset once, staff log in again); cloudflared now required; first staff login rehashes passwords.
- Merge branch, tag `3.0.0`.
- Remove YAPF, `src/`, `Tests/` (old), `Versions/*.sql` (archived under `docs/legacy-sql/`), `phpcs.xml` (replaced by Pint), `docker-compose.cloudflared.yml`, `.env.cloudflared.example`, plus everything in "Docker-only cleanup".

## Gates before cutover

- [ ] All golden-master API fixtures pass
- [ ] All ported feature tests pass
- [ ] Upgrade test from a 2.0.1.x dump passes
- [ ] Legacy staff password login + rehash verified
- [ ] Cron cadence verified in the Docker stack
- [ ] `SCHEDULER_ENABLED=false` starts web only; per-task toggles pause a task; two app containers never double-run a task
- [ ] No jQuery in the built assets
- [ ] End-to-end through a real tunnel: admin login, an in-world vendor purchase, a HUD renew and an ANS callback all succeed with the WAF rule in place
- [ ] Logged client IPs are real client IPs, not the cloudflared container
- [ ] Stack starts with no host ports published (except optional phpMyAdmin on 127.0.0.1)

## Risks

| Risk | Mitigation |
|---|---|
| LSL contract drift | Golden masters; explicit response key mapping (never from column names) |
| Rename migration fails mid-way on a live DB | Mandatory backup in release notes; upgrade test in CI; migrations per table |
| `*.php/...` paths vs Laravel front controller | Explicit routes + web server rewrite verified in Docker |
| Hidden YAPF behaviour (input filtering, nulls) | Explicit validation rules + tests per endpoint |
| Timezone from DB | Set once in a service provider; unixtime stays integer |
| Cloudflare challenges/blocks LSL or ANS requests | Documented WAF skip rule for the four API paths; tunnel E2E gate |
| Wrong client IP / scheme behind the tunnel | `TrustProxies` + `CF-Connecting-IP`; forced https; gate check |
| Valkey compatibility drift from Redis | Pin Valkey major; use only core commands via Laravel's `redis` driver |
| Scale (~220 endpoints) | Port per module behind tests; Phases 4 and 6 can run in parallel after Phase 3 |
