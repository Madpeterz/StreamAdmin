# StreamAdmin 3.0.0

StreamAdmin is now a Laravel 12 application (Livewire 4, Flux, Tailwind 4). The in-world API is unchanged:
vendors, servers, HUDs, the notice server, the bot and Marketplace listings keep working with no changes,
checked by 227 recorded request/response steps (`docs/lsl-contract.md`).

## Before you upgrade

**Back up the database.** The first start renames tables and columns in place. There is no automatic way back.

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
| Passwords | old hash | rehashed on each staff member's first sign-in |

New: client create / bulk-remove / transfer, global search, audit log, health page with object up/down by
region, outbox, notices with per-client opt-outs, dashboard that refreshes itself, Brotli and OPcache.

Cloudflare Tunnel is now the supported way to publish the site: see `docs/cloudflare.md` (including the WAF skip
rule for `api.php`, `hud.php`, `sys.php`, `ans.php`).

## Upgrade steps

1. Back up (above). Stop the old stack: `docker compose -f docker-compose.cloudflared.yml down` (not `-v`).
2. Find the old database volume: `docker volume ls | grep streamadmin-db`.
3. Copy `docker-compose.yml` and `.env.example` (as `.env`) to the server. Fill in the required values and
   set `DB_VOLUME=<old volume name>`. `DB_PASSWORD` must be the old `SQL_DB_PASS`. Generate the key with
   `docker run --rm madpeter/streamadmin:3.0.0 php artisan key:generate --show`.
4. Point the cloudflared route at `http://SA7App:8080`.
5. `docker compose up -d`. The first start runs the migrations and builds the caches (about a minute); follow with
   `docker logs -f SA7App`.
6. Sign in with your existing staff username and password. Check Health: the scheduled tasks show a recent run
   within a minute.
7. Remove the old `SA7Redis` volume if you no longer need it.

## Rolling back

Stop 3.0, restore `streamadmin-2.0.sql` into an empty MariaDB volume, start the 2.0.x stack. Rental data written
after the upgrade is not carried back.
