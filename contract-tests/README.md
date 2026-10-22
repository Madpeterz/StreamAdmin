# Contract tests (in-world APIs)

Black-box recorded tests for `api.php`, `hud.php`, `sys.php` and `ans.php`. Each scenario rebuilds the
database from the frozen 2.0.1.x schema, runs the Laravel migrations, sends real signed HTTP requests and
compares the responses with fixtures recorded from the 2.0.1.x application. The Laravel app must keep
matching them (see `docs/lsl-contract.md`).

## Running

```sh
# once: the dev stack provides the vendor volume
docker compose -f docker-compose.dev.yml up -d

# MariaDB + the app on the contract database
docker compose -f contract-tests/docker-compose.yml up -d app

# compare against fixtures (exit code 1 on any failure)
docker compose -f contract-tests/docker-compose.yml run --rm runner verify

# one scenario (substring match on the file name)
docker compose -f contract-tests/docker-compose.yml run --rm runner verify 30-renew

docker compose -f contract-tests/docker-compose.yml down
```

The runner targets `http://app:8080` and runs `php artisan migrate` after every database rebuild, so each
scenario also exercises the 2.0.1.x → 3.0 upgrade. It only needs HTTP access to the target and root access
to its database:

| Env | Purpose |
|---|---|
| `TARGET_URL` | Base URL of the app under test |
| `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` | Database the target uses; it is dropped and rebuilt for every scenario |
| `RESET_HOOK` | Shell command run after each rebuild (`php artisan migrate --force`) so the 2.0.1.x schema is upgraded exactly like a real install |
| `SITE_URLS` | Comma separated public URL(s) the target emits in bot commands (normalized to `{{site_url}}`) |

`record` re-writes the fixtures from whatever app is running. The recorded fixtures came from the 2.0.1.x
application (commit `adab6d1d` is the last that still contains `src/`), so only re-record to
add a scenario, and review the diff before committing.

## Layout

```
runner/run.php              record | verify
runner/src/RequestBuilder   signs requests like in-world scripts
runner/src/Normalizer       makes responses comparable (times, captured ids, dates, bot signatures)
runner/src/Comparator       strict, type-aware diff ("1" != 1 != true)
runner/src/Database         drop + install legacy schema + reset + seeds (+ RESET_HOOK)
sql/schema/                 copy of docs/legacy-sql/installer.sql + 2.0.1.1.sql (frozen 2.0.1.x schema)
sql/reset.sql               baseline data (from docs/legacy-sql/reset.db.sql) with a distinct link code per API
sql/seeds/*.sql             scenario data, legacy table names
scenarios/*.php             ordered request steps
fixtures/*.json             recorded responses (commit these)
```

## Writing scenarios

A scenario returns `description`, `seeds` (applied in order after `reset.sql`) and `steps`. Step keys:

| Key | Meaning |
|---|---|
| `id` | Unique within the scenario; fixtures are matched by position and id |
| `kind` | `sl` (default), `hud`, `sys`, `ans`, `raw` |
| `path` | `Module/Area` (or the full path for `raw`) |
| `as` | Identity for sl/hud: `owner` (default), `reseller`, `client`, `stranger` |
| `object` | Overrides for the standard object fields (`version`, `ownerkey`, ...) |
| `post` / `query` | Extra fields; `{{var}}` placeholders use captured values |
| `tamper` | `hash: "invalid"`, `time_offset`, `shard` (null = no header), `omit: [...]`, `sign_with: kind`, `omit_hash_header`; sys only: `sys_key` (window offset), `sys_key_for: [method, action]` |
| `capture` | `["var" => "body.key"]` or `["var" => ["body.key", "/regex (group)/"]]` |
| `mask` | Body paths whose value is not compared (only the type is) |
| `legacy_bug` | `note` + `fixed` response: for a known defect in the recorded target, verify also accepts the fixed response |

Rules:

- SQL belongs in seeds only, written against the **legacy** schema. On the Laravel stack the schema is
  upgraded by `RESET_HOOK` after seeding, so SQL run between steps would break after the table renames.
- Keep package stock to a single free stream when a step buys, because the legacy app picks a random free stream.
- Offset seeded times by +30 minutes so "N days, N hours" text does not flip during a run.

## Normalization

| Fixture value | Meaning |
|---|---|
| `{"$time": 604800, "$type": "int"}` | Unix time relative to the request, ±120 s tolerance |
| `{"$var": "name"}`, `"{{name}}"` | Value captured by an earlier step |
| `{"$mask": "string"}` | Masked by the step |
| `{"$json": {...}}` | JSON payload inside a string field |
| `{"$raw": "..."}` | Non-JSON body |
| `{{dur:20160~60}}` | Duration in minutes (± tolerance) parsed from "14 days, 0 hours" |
| `{{datetime}}` | Absolute date/time text |
| `{{site_url}}` | Target public URL |
| `@@@{{bot_sig_ok}}` | Bot command signature that checks out against the bot secret |
| `{{sys_key}}` | Valid short lived sys.php key in `FetchNextNotecard` (the raw inbound secret would show up unnormalized) |
