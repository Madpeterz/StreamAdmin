# In-world API v3

What vendors, server objects, the renter HUD and the Second Life bot call. It replaces the 2.0.x
`api.php`, `hud.php` and `sys.php` entry points, which no longer exist. The Marketplace ANS URL
(`/ans.php`) stays, see the end.

Code: `routes/api.php`, `app/Api/`, `app/Services/Vendor*.php`, `QueueService.php`, `NoticeService.php`.
Tests: `tests/Feature/Api/`.

## Calls

Every call is `POST /api/v3/{path}` with a JSON object as the body (`Content-Type: application/json`) and four
headers:

| Header | Value |
|---|---|
| `X-SA-Class` | the key class: `vendor`, `owner`, `hud` or `bot` |
| `X-SA-Timestamp` | Unix time in seconds; accepted ±120 s from server time |
| `X-SA-Nonce` | 16 to 64 letters or digits, new for every call (kept for 241 s; a repeat is refused) |
| `X-SA-Signature` | the signature below |

```
signature = base64( HMAC-SHA256( key, timestamp "\n" nonce "\n" path "\n" body ) )
```

- `key` is the secret of the key class (System settings → Access keys). It is at least 16 characters; shorter
  ones are never accepted.
- `path` is the request path, `/api/v3/rentals/start`, exactly as sent.
- `body` is the exact JSON text sent, so every field is signed.
- In LSL: `llHMAC(key, timestamp + "\n" + nonce + "\n" + path + "\n" + body, "sha256")`, which returns the
  Base64 text. Send that body string as the request body.
- LSL cannot hash non-ASCII text, so **everything signed must be ASCII**: send free text (avatar, object and
  region names, notecard names) through `llEscapeURL`. The server decodes it.

### Key classes

| Class | Secret | Who | May call |
|---|---|---|---|
| `vendor` | Vendors key | resellers' vendors and renewal points | `objects`, `packages`, `vendors`, `texture-packs`, `stock`, `tree-vendors`, `rentals`, `clients` |
| `owner` | Owner objects key | the system owner's own objects | everything `vendor` can, plus `owner/...` |
| `hud` | Renter HUD key | the renter HUD | `hud/...` |
| `bot` | Bot API key | the Second Life bot | `bot/...` |

A call to something the class may not use is `403 forbidden`. The owner key identifies the owner by itself, so
owner objects send no `owner`. A vendor call that names the system owner as its owner is refused: the owner
must sign with the owner key.

The bot may sign with a **short lived key for one path** instead of the Bot API key. `FetchNextNotecard` gives
it one (see Bot commands): `hmac_sha256(key: botKey, data: "bot|" path "|" floor(unixtime / 3600))` as hex,
valid for the current and the previous hour and only for that path.

### Body

```json
{
  "object": {"key": "<uuid>", "name": "Vendor%20One", "type": "vendor", "region": "My%20Region", "position": "128,128,25"},
  "owner":  {"key": "<uuid>", "name": "Some%20Resident"},
  "data":   { }
}
```

- `object` says which object is calling, `owner` who owns it (the HUD's wearer for the HUD). `data` is what the
  endpoint takes. Names are `llEscapeURL` text.
- `vendor` calls need `object` (all fields) and `owner`. `owner` calls need `object`. `hud` calls need
  `owner` and `object.region`. `bot` calls need neither.
- Each call records the object as seen (Health page), and a new reseller is approved automatically or not
  as System settings say ("Accept new resellers automatically", with its rate).
- The request header `X-SecondLife-Shard: Production` (sent by Second Life) must be present on vendor, owner
  and hud calls.

### Replies

```json
{"ok": true,  "message": "pong", "data": {}}
{"ok": false, "code": "rejected", "message": "Payment amount not accepted"}
```

Types are real JSON types (numbers, booleans, `null`, arrays, objects). A call that does not succeed leaves
nothing behind in the database.

| HTTP | `code` | Meaning |
|---|---|---|
| 200 | | done |
| 400 | `invalid_request` | the body is not a JSON object |
| 401 | `unauthorized` | missing or bad headers, bad signature, stale timestamp |
| 403 | `forbidden` | the key may not do this; reseller not approved |
| 403 | `disabled` | a feature the owner switched off (HUD details/renewals, bot notecards) |
| 404 | `not_found` | unknown endpoint, package, rental, avatar... |
| 409 | `replay` | the nonce was already used |
| 422 | `validation` | a field is missing or wrong; `errors` lists them per field |
| 422 | `rejected` | the rules say no (wrong amount, nothing in stock, banned...) |

## Vendor and owner calls

Also open to the owner key. `data` fields are required unless noted.

| Path | `data` | Reply `data` |
|---|---|---|
| `objects/ping` | | |
| `packages/status` | `packageUid` | `inStock`, `cost`, `textures {packageSmall, packageBig, packageSoldOut}` |
| `vendors/config` | `packageUid`, `texturePackId` | `inStock`, `cost`, `textures {offline, waitOwner, gettingDetails, makePayment, packageSmall, packageBig, packageSoldOut}`, `reseller {rate, mode}` |
| `texture-packs/show` | `texturePackId` | `textures {offline, waitOwner, gettingDetails, makePayment, renewHere, inUse, requestDetails, stockLevels, proxyRenew}`, `reseller {rate, mode}` |
| `stock/levels` | | `servers [{id, domain, packages [{id, name, stock}]}]` |
| `tree-vendors/packages` | `treeVendorId` | `textureInUse`, `textureWaiting`, `packages [{packageUid, serverType, autoDj, autoDjSize, listeners, bitrate, days, cost}]` |
| `rentals/start` | `packageUid`, `amountPaid`, `avatar {key, name}` | `rentalUid`, `ownerPayment`, `credit` |
| `rentals/renew` | `rentalUid`, `amountPaid`, `payer {key, name}` | `expiresAt`, `timeLeft`, `inArrears`, `ownerPayment`, `credit` |
| `rentals/cost` | `rentalUid` | `systemOwnerKey`, `cost`, `word` (`Day`, `Week`, `Month`), `stackSize`, `timeLeft` |
| `rentals/list` | `avatarKey` | `client`, `rentals [{rentalUid, port, timeLeft}]`, newest first |
| `rentals/find` | `target` (escaped "First Last" or an avatar key) | as `rentals/list` |
| `rentals/send-details` | `rentalUid` | |
| `clients/has-rental` | `avatarKey` | `hasRental` |

`reseller.mode` is `reseller` (with the reseller's rate) or `owner` (rate 100) for the owner key.

### Money

- A payment is 1 to 4 periods of the package cost (`amountPaid` = cost × periods), else `rejected`.
- `ownerPayment` is `{required, amount, key}`: what the object must pay `key` (the system owner) after a
  sale. Owner objects pay nothing (`required: false`); a reseller passes on all but their rate, and keeps at
  least L$1 when the amount allows.
- `credit` is `{returned, remaining}`: prepaid Marketplace credit refunded against a payment made at an owner
  object (a negative transaction records it).
- With the rental time limit on, a rental may not run more than the limit ahead.

## Owner calls

Owner key only. The owner's server objects poll these; each call takes one unit of work.

| Path | `data` | Reply `data` |
|---|---|---|
| `owner/mail/next` | | `message` null or `{avatarKey, text}` (the IM to send) |
| `owner/events/next` | | `event` null or `{name, message}` (`message` is JSON text) |
| `owner/details/process` | | `processed` (queued details requests turned into IMs, all or nothing) |
| `owner/bot-commands/next` | | `sent`, `via` (`mail` or `http`); `rejected` if the bot did not accept it (the command stays queued) |
| `owner/notices/next` | | `changed`, and when true `rentalUid`, `notice`, `staticNotecard` null or `{name, avatarKey}` |
| `owner/notecards/next` | | `notecard` null or `{name, avatarKey}`, `recheck`; unsendable entries are dropped and reported with `dropped: true` |
| `owner/notecards/sync` | `notecards` (array of escaped names in the inventory) | `purged` |
| `owner/bot/notecards/sync` | | `state` (`nowork` or `requested`: the bot was asked to fetch) |
| `owner/support-code` | | `code` |

## HUD calls

HUD key. The caller's `owner` is the wearer, who can only see and pay their own rentals. `403 disabled` when
System settings switch the feature off; renewals are also off while the rental time limit is on.

| Path | `data` | Reply `data` |
|---|---|---|
| `hud/config` | | `allowDiscord`, `allowGroup`, `allowDetails`, `allowRenewal`, `groupLink`, `discordLink` |
| `hud/rentals` | | as `rentals/list` |
| `hud/rentals/cost` | `rentalUid` | as `rentals/cost` |
| `hud/rentals/renew` | `rentalUid`, `amountPaid`, `transactionKey` (the SL payment's key) | as `rentals/renew`; recorded against the owner |
| `hud/rentals/send-details` | `rentalUid` | |

## Bot calls

| Path | Reply `data` |
|---|---|
| `bot/notecards/next` | `notecard` null or `{avatarKey, title, content}`: the next dynamic notecard to write |

### Bot commands

Commands to the bot are signed with the **bot secret** set on the Bot page (not the Bot API key):

```
signature = hex( HMAC-SHA256( botSecret, command "\n" args "\n" unixtime ) )
```

`args` is the arguments joined with `~#~`. In an IM: `{command}|||{args}|||{unixtime}@@@{signature}`. In HTTP
mode the bot gets a form post to `{bot url}/api/Run` with `commandName`, `args`, `unixtime` and `signing`
(the same signature). The bot should refuse a `unixtime` more than a couple of minutes old.

`FetchNextNotecard` has two arguments: the full URL of `bot/notecards/next` and a short lived key for that path.
The bot calls that URL with `X-SA-Class: bot` and signs with the key instead of the Bot API key.

## Marketplace ANS (`GET /ans.php`)

Linden Lab's Automated Notification, so the protocol is theirs: the query string is the notification and
`X-ANS-Verify-Hash` is `sha1(raw query string . ANS salt)` (System settings → Marketplace coupons). Replies are
always HTTP 200 with `{"ok": true|false, "message": "..."}`. A delivery is paid once: the SL transaction id is
unique. Needs "Enable Marketplace coupons".

## Example (LSL)

```lsl
string KEY = "...Vendors key...";
call(string path, string data)
{
    string body = "{\"object\":{\"key\":\"" + (string)llGetKey() + "\",\"name\":\"" + llEscapeURL(llGetObjectName())
        + "\",\"type\":\"vendor\",\"region\":\"" + llEscapeURL(llGetRegionName()) + "\",\"position\":\"" + (string)llGetPos()
        + "\"},\"owner\":{\"key\":\"" + (string)llGetOwner() + "\",\"name\":\"" + llEscapeURL(llKey2Name(llGetOwner()))
        + "\"},\"data\":" + data + "}";
    string time = (string)llGetUnixTime();
    string nonce = llMD5String((string)llGenerateKey(), 0);   // 32 hex characters, new every call
    string sig = llHMAC(KEY, time + "\n" + nonce + "\n" + path + "\n" + body, "sha256");
    llHTTPRequest("https://streamadmin.example.com" + path, [HTTP_METHOD, "POST", HTTP_MIMETYPE, "application/json",
        HTTP_CUSTOM_HEADER, "X-SA-Class", "vendor", HTTP_CUSTOM_HEADER, "X-SA-Timestamp", time,
        HTTP_CUSTOM_HEADER, "X-SA-Nonce", nonce, HTTP_CUSTOM_HEADER, "X-SA-Signature", sig], body);
}
```

The nonce must be letters and digits only (16 to 64 of them), so an MD5 or SHA hex string of a fresh key works.
