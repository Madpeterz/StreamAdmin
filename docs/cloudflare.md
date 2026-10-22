# Running StreamAdmin 3.0 behind a Cloudflare Tunnel

The production stack (`docker-compose.yml`) publishes no ports. `cloudflared` reaches the app over a
shared Docker network and Cloudflare terminates https.

## 1. Tunnel route

cloudflared runs in its own stack, attached to the external network named in `TUNNEL_NETWORK` (default
`playerdb`; create it once with `docker network create playerdb` if it does not exist).

Public hostname in the Zero Trust dashboard (or `config.yml`):

| Field | Value |
|---|---|
| Hostname | `streamadmin.example.com` (the same address as `APP_URL`) |
| Service | `http://SA7App:8080` |

The container listens on **8080** (it runs as a non-root user), not 80 as the 2.0.x `SA7Website` did.

## 2. What the app does with the tunnel

- nginx trusts the private Docker ranges and Cloudflare's published ranges and takes the visitor address from
  `CF-Connecting-IP`.
- Laravel trusts `X-Forwarded-*` from the tunnel (`TRUSTED_PROXIES=*`), so generated URLs, Livewire assets and
  cookies are https. This is only safe because nothing else can reach the container: do not add a `ports:` entry
  to `SA7App`. If you share the network with other stacks, list the cloudflared address in `TRUSTED_PROXIES`
  instead of `*`.
- `SESSION_SECURE_COOKIE=true` is set, so staff must use the https hostname.

## 3. WAF: let Second Life through

In-world scripts and Linden Marketplace call two paths with no cookies and a `Second Life LSL` user agent.
Bot Fight Mode, managed challenges and rate limits will block them. Add a **Skip** rule (WAF → Custom rules,
placed first) for:

```
(starts_with(http.request.uri.path, "/api/v3/"))
or (starts_with(http.request.uri.path, "/ans.php"))
```

Skip: all remaining custom rules, rate limiting rules, managed rules and Super Bot Fight Mode. Every request
on /api/v3/ is HMAC-signed (`docs/api-v3.md`), and /ans.php is checked against the Marketplace hash, so the
application rejects anything forged.

Bot HTTP mode calls the bot from the app (outbound), and the bot calls back on `/api/v3/bot/`, so it is covered by
the same rule.

## 4. Protect the admin UI (recommended)

Put **Cloudflare Access** in front of everything *except* the two paths above, `/build/*` and `/up`:

- Application: `streamadmin.example.com`, policy: allow your staff emails.
- Bypass policy for the paths `/api/v3/*` and `/ans.php*`.

Staff still sign in to StreamAdmin itself; Access is a second lock in front.

## 5. Checks

```
curl -s https://streamadmin.example.com/up                                   # 200
curl -s -o /dev/null -w "%{http_code}" -X POST https://streamadmin.example.com/api/v3/nothing/here   # 401, not a Cloudflare page
```

If `/api/v3` returns a Cloudflare challenge page or 403, the skip rule is missing or ordered after another rule.
