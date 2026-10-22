# In-world API contract (frozen for 3.0.0)

In-world scripts (vendors, servers, HUDs) and Linden Marketplace callbacks cannot be updated in bulk, so
the Laravel port must answer these requests exactly as 2.0.1.x does. This document describes the contract;
`contract-tests/` enforces it (227 recorded request/response steps, see `contract-tests/README.md`).

Source of truth in the legacy code: `src/App/Template/SecondlifeAjax.php`, `SecondlifeHudAjax.php`,
`SystemApiAjax.php`, `src/App/Endpoint/Ans/Ans/Event.php`, and YAPF's `Switchboard` / `BootstrapConfigBox`.

## Entry points

| Entry | Method | Path | Signed with (slconfig column) | Endpoint namespace |
|---|---|---|---|---|
| Vendors and server objects | POST | `/api.php/{Module}/{Area}` | `slLinkCode` | `Secondlifeapi` |
| HUD | POST | `/hud.php/{Module}/{Area}` | `hudLinkCode` | `Hudapi` |
| System (bot notecards) | POST | `/sys.php/{Module}/{Area}` | `httpInboundSecret` | `SystemApi` |
| Marketplace ANS | GET | `/ans.php?{query}` | `ansSalt` | `Ans/Ans/Event` |

Routing:

- Path segments are matched case-insensitively (`object/ping` works). For the hash, module and area are
  the path segments with only the first letter upper-cased (`ucfirst`), as sent.
- The router tries `{Module}/{Area}`, then `{Module}`, then `{Module}/{Area}/{Page}`, then `{Module}/{Area}/{Page}/{Option}`, then `{Module}/DefaultView`.
- Unknown endpoint: HTTP **501** with `{"status":false,"message":"[Module | Area | Page] Unsupported"}`.
- Every other outcome, including validation failures, is HTTP **200**. `Content-Type` is `text/html` (scripts ignore it).

## api.php and hud.php requests

Required POST fields, in hash order:

| Field | Rule |
|---|---|
| `version` | min length 1; must be `>= 2.0.1.0` (version_compare) |
| `mode` | min length 1 |
| `objectuuid` | UUID |
| `regionname` | min length 1 |
| `ownerkey` | UUID |
| `ownername` | min length 1 |
| `pos` | min length 1 |
| `objectname` | min length 1 |
| `objecttype` | min length 1 |
| `unixtime` | integer |
| `hash` | string |

```
hash = sha1( unixtime . Module . Area . version . mode . objectuuid . regionname
             . ownerkey . ownername . pos . objectname . objecttype . linkCode )
```

`linkCode` is `slLinkCode` for api.php and `hudLinkCode` for hud.php.

Checks, in order. The first failure sets the message; the later checks still run but do not overwrite it,
except the version check, which always runs last:

1. Required fields present: `Value: {field} is missing`. Invalid UUIDs count as missing. A missing
   `unixtime` gives `Missing unixtime value`, a missing `hash` gives `Missing hash value`.
2. Time window, ±120 seconds from server time: `timewindow is out of scope`.
3. Hash: `Unable to vaildate request to API endpoint` (hud.php appends `": "`).
4. Header `X-SecondLife-Shard: Production`, else `wrong grid connected: ...`.
5. The owner avatar and region are loaded or created.
6. api.php only (object tracking):
   - The owner is loaded or created as a reseller (`newResellers` / `newResellersRate`).
   - A reseller that is not allowed is refused: `Unable to access this api - please contact owner`.
   - The owner avatar matching `slconfig.ownerAvatarLink` gives *owner override*.
   - The object is recorded in `objects`.
7. Version: `Requires version: 2.0.1.0 or higher given version {version}`. This always runs, and when it
   fails it overwrites any earlier message, so an empty POST returns this message with an empty version.

Owner-only endpoints refuse non-owners with `SystemAPI access only - please contact support`
(`Bot/Notecardsync` says `This API is owner only`):
`Eventq/Next`, `Mailserver/Next`, `Detailsserver/Next`, `Botcommandq/Next`, `Noticeserver/Next`,
`Object/Severrequestcode`, `Bot/Notecardsync`.

## sys.php requests

POST `unixtime` (int), `token`, `method`, `action` (all required, `Value: {field} is missing`).

```
token = sha1( unixtime . method . action . httpInboundSecret )
```

`method` and `action` are free text and are not tied to the path. The secret must be 5 to 30 characters
long, otherwise every call fails with `httpcode length must be 5 or longer` / `... 30 or less`.
Time window ±120 s. A bad token gives `Invaild token`.

The token may also be signed with a short lived key instead of the secret (2.0.1.3,
`src/App/Helpers/SystemApiKey.php`):

```
key = hmac_sha256( key: httpInboundSecret, data: "sys|" . method . "|" . action . "|" . floor(unixtime / 3600) )
```

The current and previous hour windows are accepted, so a key lives 1 to 2 hours and only for its
method/action. This is what the bot receives (see below); the secret itself never leaves the server.

The only client is SecondBot's `FetchNextNotecard` command (`SecondBotEvents/Commands/StreamAdmin.cs`). It posts
`Notecard/Next` with `method=Notecard`, `action=Next`, signs with the code it was given, and deserializes
`status` as a boolean.

## ans.php requests

GET query fields: `TransactionID` (3-20 chars), `Type`, `PayerName`, `PayerKey` (UUID), `ReceiverName`,
`ReceiverKey` (UUID), `ItemID` (int, 3-10 chars), `PaymentGross` (int). Header:

```
X-ANS-Verify-Hash = sha1( raw QUERY_STRING . ansSalt )
```

The order of checks (each has its own message, see fixtures `70-ans` / `71-ans-disabled`):

1. Coupons enabled.
2. Header present.
3. Fields present.
4. `Type == Purchase`.
5. Listing tracked.
6. Amount matches.
7. Hash.

Single-word names get ` Resident` appended. The hash has no timestamp, so replays are stopped only by the
unique `transactions.SLtransactionUUID` column (the response is `Unable to create transaction`).

## Responses

JSON object made of the endpoint's swap tags:

- Always present: `status`, `message`. `module` and `area` are added once request validation passed
  (validation failures do not include them).
- **`status` type differs per entry point** and scripts rely on it:
  - api.php / hud.php: strings `"1"` / `"0"` (every boolean tag is converted to `"1"` / `"0"`)
  - sys.php / ans.php: JSON booleans `true` / `false`
  - 501 unknown endpoint: boolean `false` on all entry points
- Other values keep their PHP type: ints stay ints (`package_cost: 500`), unset values are `null`, lists are
  JSON arrays, keyed sets are JSON objects (`Stock/Stocklevel` → `results`).
- A request that ends with `status` false rolls back all database changes made during the request.
- Embedded formats that in-world scripts parse:
  - Rental lists: `"{rentalUid}|||{port}|||{time left}"`
  - Bot commands in messages: `"{Command}|||{arg}~#~{arg}@@@{sha1(Command . args . botSecret)}"`.
    `FetchNextNotecard` args are `{SITE_URL}` and the short lived sys.php key for `Notecard/Next`
  - Time left: `"N days, N hours"`, `"N hours, N mins"`, `"N mins"`, `"Expired"`
  - Renew messages end with a PHP date `l jS \of F Y h:i:s A`
  - `Eventq/Next` returns `eventMessage` as a JSON string

## Contract changes since 2.0.1.2 (fixed in the legacy code, 2.0.1.3)

The fixtures record the fixed behaviour; the scenarios mark each step with a `fixed in 2.0.1.3` comment.

| Endpoint | 2.0.1.2 behaviour | Now |
|---|---|---|
| `Buy/Getconfig` | Unknown `packageuid` or `texturepack` answered `status "1"` with null textures (dead `loadX() == false` check) | `status "0"`, `Unable to load package` / `Unable to load texturepack` |
| `Buy/Startrental` | Missing or invalid `avatarUUID`, `avatarName` or `packageuid` threw a TypeError → empty HTTP 500 | `status "0"`, `Unable to attach avatar` / `Unable to find package` |
| `Tree/Getpackages` | Unknown tree vendor threw a TypeError → empty HTTP 500 | `status "0"`, `Unable to load selected tree vender` |
| `Noticeserver/Notecardmail` | Bad entries were removed then rolled back (status `"0"`), so the queue stalled on them forever. No owner check | Bad entries are dropped for good (committed before the reply). Owner only |
| `Noticeserver/Updatenotecards` | No owner check: any approved reseller object could rewrite the static notecard list | Owner only |
| `Rentals/Costs` (HUD) | Message contained a CRLF plus source indentation | Single-line message |
| `Stock/Stocklevel` | Free streams were matched with `rentalLink = NULL`, so every stock level was 0 | Real free stream counts |
| all api.php / hud.php | Wrong-grid message echoed every request header | `wrong grid connected: {shard or "none"}` |
| `FetchNextNotecard` bot command | Carried `httpInboundSecret` in plain text through the message table, IMs and the bot's error replies | Carries a short lived key scoped to sys.php `Notecard/Next`; sys.php accepts it as well as the secret |

The same dead `loadX() == false` check was also fixed in `Startrental` (server and owner avatar),
`Noticeserver/Next`, `SystemApi/Notecard/Next`, the reseller and system config controls, and the
Manage views (client, health, package, reseller, staff, template, tree).

## Security observations (not part of the contract, decide before 3.0.0)

- SL request hashes are sha1 over a shared secret with a ±120 s window and no nonce: replay is possible
  inside the window.
- The site URL put into bot commands comes from the `SITE_URL` env var. Without it, the fallback in
  `src/App/Config.php` is the developer's URL (`https://dev.blackatom.win/`).
