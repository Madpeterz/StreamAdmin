# StreamAdmin 3.0.0

StreamAdmin is now a Laravel 12 application (Livewire 4, Flux, Tailwind 4).

**This is a breaking release.** The 2.0.x in-world API (`api.php`, `hud.php`, `sys.php`) is replaced by a signed
JSON API at `/api/v3` (`docs/api-v3.md`), and the 2.0.x staff password hashes are gone. Every in-world object that
talks to StreamAdmin (vendors, renewal points, the HUD, the mail, details, notice, event and bot-queue objects)
and the Second Life bot must be updated before they work again. Only the Marketplace ANS URL (`/ans.php`) is
unchanged.

## Before you upgrade

**Back up the database.** The first start renames tables and columns in place and drops data the old API needed
(see below). There is no automatic way back.

```
docker exec SA7Db sh -c 'mariadb-dump -uroot -p"$MARIADB_ROOT_PASSWORD" --single-transaction streamadmin' > streamadmin-2.0.sql
```

(The 2.0.x compose file gave MariaDB a random root password; if that is the case, dump with
`-ustreamadminDBuser -p<SQL_DB_PASS>` instead.)

## What changed for you

| | 2.0.x | 3.0.0 |
|---|---|---|
| Containers | `SA7Website`, `SA7Cron`, `SA7Redis` | `SA7App` (web + scheduler), `SA7Valkey` |
| cloudflared target | `http://SA7Website:80` | `http://SA7App:8080` |
| Settings | `SITE_URL`, `SQL_DB_PASS`, `SITE_*` | `.env.example`: `APP_URL`, `APP_KEY`, `DB_PASSWORD`, `VALKEY_PASSWORD`, `STREAMADMIN_TAG` |
| Cache / sessions | Redis 4 | Valkey 8 with a password; staff sign in again once |
| Cron | `SA7Cron` container | built in, `SCHEDULER_ENABLED`, per-task switches on the Health page |
| Spreadsheet export | `Exporter.sh` | nightly `.xlsx` with 14 backups, download on System settings |
| Staff passwords | old hash, upgraded at first sign-in | **old hashes are dropped**: set a password from the console (below) |
| In-world API | `api.php`, `hud.php`, `sys.php`; sha1 over a shared code; replay possible | `/api/v3`: JSON, HMAC-SHA256 over the whole body, one key per class, nonce per call |
| Keys | one code per entry point, 8 characters | four keys (vendors, owner objects, HUD, bot), 16 characters; short or `install` keys are replaced at the first sign-in |
| Owner objects | signed with the vendor code, any reseller could claim to be the owner | own key; a vendor key that names the owner is refused |
| Replies | strings ("1"/"0"), 2.0.x message text | JSON types, `ok` / `code` / `message`, real HTTP status codes |
| Bot commands | `sha1(command . args . secret)` | HMAC-SHA256 with a timestamp |
| Names in the database | stored with HTML entities | plain text (converted by the upgrade) |
| Swap tags | 1.x names (`[[FirstName]]`...) also worked | only the current tags; the upgrade renames them in templates and notices |
| Links in IMs and redirects | host of the request | always `APP_URL` |

New: client create / bulk-remove / transfer, global search, audit log, health page with object up/down by
region, outbox, notices with per-client opt-outs, dashboard that refreshes itself, Brotli and OPcache.

Cloudflare Tunnel is now the supported way to publish the site: see `docs/cloudflare.md` (including the WAF skip
rule for `/api/v3/` and `/ans.php`).

## Upgrade steps

1. Back up (above). Stop the old stack: `docker compose -f docker-compose.cloudflared.yml down` (not `-v`).
2. Find the old database volume: `docker volume ls | grep streamadmin-db`.
3. Copy `docker-compose.yml` and `.env.example` (as `.env`) to the server. Fill in the required values and
   set `DB_VOLUME=<old volume name>`. `DB_PASSWORD` must be the old `SQL_DB_PASS`. Generate the key with
   `docker run --rm madpeter/streamadmin:3.0.0 php artisan key:generate --show`.
4. Point the cloudflared route at `http://SA7App:8080`, and add the WAF skip rule for `/api/v3/` and `/ans.php`.
5. `docker compose up -d`. The first start runs the migrations and builds the caches (about a minute); follow with
   `docker logs -f SA7App`.
6. Set your own password from the console, then sign in (in-world password resets need the mail server object,
   which does not work until it is updated):

   ```
   docker compose exec SA7App php artisan streamadmin:staff-password <username>
   ```

   Do the same for every staff member who needs to get in before they use the in-world reset link.
7. The first sign-in replaces every key that is `install` or shorter than 16 characters. Open **System settings →
   Access keys** and copy the four keys (Vendors, Owner objects, Renter HUD, Bot API) into your in-world objects
   and the bot. Until then those calls get `401 unauthorized`.
8. Update the objects and the bot to the v3 calls in `docs/api-v3.md`. Check Health: the scheduled tasks show a recent
   run within a minute, and objects appear as they check in.
9. Remove the old `SA7Redis` volume if you no longer need it.

## Rolling back

Stop 3.0, restore `streamadmin-2.0.sql` into an empty MariaDB volume, start the 2.0.x stack. Rental data written
after the upgrade is not carried back.
