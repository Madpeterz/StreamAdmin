<?php

declare(strict_types=1);

namespace Contract;

use InvalidArgumentException;

/**
 * Builds signed requests exactly as in-world scripts send them.
 *
 * Step keys:
 *   kind      sl | hud | sys | ans | raw
 *   path      "Module/Area" (sl, hud, sys) or full path (raw)
 *   as        identity preset for sl/hud: owner | reseller | client | stranger
 *   object    overrides for the standard object fields
 *   post      extra POST fields ({{var}} placeholders allowed)
 *   query     ordered GET fields (ans)
 *   tamper    hash: "invalid" | time_offset: int | shard: string|null | omit: string[] | sign_with: kind
 *             sys only: sys_key: window offset (0 current, -2 expired) | sys_key_for: [method, action]
 */
final class RequestBuilder
{
    /** Standard fields in hash order. */
    private const SL_FIELDS = [
        "version", "mode", "objectuuid", "regionname", "ownerkey", "ownername", "pos", "objectname", "objecttype",
    ];

    public const IDENTITIES = [
        // slconfig.ownerAvatarLink = 1
        "owner" => ["ownerkey" => "00000000-0000-0000-0000-000000000000", "ownername" => "System"],
        // avatar 2, used as an approved reseller by the seeds
        "reseller" => ["ownerkey" => "289c3e36-69b3-40c5-9229-0c6a5d230766", "ownername" => "Madpeter Zond"],
        // avatar created by the seeds as a renting client
        "client" => ["ownerkey" => "c11e0000-0000-4000-8000-000000000001", "ownername" => "Contract Client"],
        // unknown avatar (new resellers are disabled by default)
        "stranger" => ["ownerkey" => "5a5a5a5a-0000-4000-8000-000000000099", "ownername" => "Stranger Resident"],
    ];

    private const OBJECT_DEFAULTS = [
        "version" => "2.0.1.2",
        "mode" => "api",
        "objectuuid" => "0b1ec700-0000-4000-8000-000000000001",
        "regionname" => "Contract Region",
        "pos" => "128,128,25",
        "objectname" => "Contract object",
        "objecttype" => "vendor",
    ];

    public function __construct(private readonly string $baseUrl)
    {
    }

    /**
     * @param array<string, mixed> $step
     * @param array<string, string> $vars
     * @return array{method: string, url: string, headers: array<string, string>, fields: array<string, string>, record: array<string, mixed>}
     */
    public function build(array $step, array $vars, int $now): array
    {
        $kind = $step["kind"] ?? "sl";
        $tamper = $step["tamper"] ?? [];
        return match ($kind) {
            "sl", "hud" => $this->buildSecondlife($kind, $step, $vars, $now, $tamper),
            "sys" => $this->buildSystem($step, $vars, $now, $tamper),
            "ans" => $this->buildAns($step, $vars, $tamper),
            "raw" => $this->buildRaw($step, $vars),
            default => throw new InvalidArgumentException("Unknown request kind: " . $kind),
        };
    }

    private function buildSecondlife(string $kind, array $step, array $vars, int $now, array $tamper): array
    {
        [$module, $area] = $this->splitPath($step["path"]);
        $identity = self::IDENTITIES[$step["as"] ?? "owner"];
        $fields = array_merge(self::OBJECT_DEFAULTS, $identity, $step["object"] ?? []);
        $unixtime = $now + (int) ($tamper["time_offset"] ?? 0);

        $raw = $unixtime . $module . $area;
        foreach (self::SL_FIELDS as $name) {
            $raw .= $fields[$name];
        }
        // 3.0: api.php requests naming the system owner are signed with the owner link code
        $signKind = $tamper["sign_with"] ?? $kind;
        $asOwner = $kind === "sl" && $signKind === "sl" && strcasecmp($fields["ownerkey"], self::IDENTITIES["owner"]["ownerkey"]) === 0;
        $raw .= $asOwner ? Secrets::OWNER_LINK_CODE : Secrets::forKind($signKind);

        $post = [];
        foreach (self::SL_FIELDS as $name) {
            $post[$name] = (string) $fields[$name];
        }
        $post["unixtime"] = (string) $unixtime;
        $post["hash"] = ($tamper["hash"] ?? null) === "invalid" ? sha1("invalid") : sha1($raw);
        $post = array_merge($post, $this->substitute($step["post"] ?? [], $vars));
        foreach ($tamper["omit"] ?? [] as $name) {
            unset($post[$name]);
        }

        $headers = [];
        $shard = array_key_exists("shard", $tamper) ? $tamper["shard"] : "Production";
        if ($shard !== null) {
            $headers["X-SecondLife-Shard"] = $shard;
        }
        $script = $kind === "hud" ? "hud.php" : "api.php";
        return $this->result("POST", "/" . $script . "/" . $step["path"], $headers, $post, ["hash", "unixtime"]);
    }

    private function buildSystem(array $step, array $vars, int $now, array $tamper): array
    {
        $unixtime = $now + (int) ($tamper["time_offset"] ?? 0);
        [$module, $area] = $this->splitPath($step["path"]);
        $method = $step["method"] ?? $module;
        $action = $step["action"] ?? $area;
        $code = Secrets::forKind($tamper["sign_with"] ?? "sys");
        if (array_key_exists("sys_key", $tamper)) {
            [$keyMethod, $keyAction] = $tamper["sys_key_for"] ?? [$method, $action];
            $code = Secrets::sysKey($keyMethod, $keyAction, (int) $tamper["sys_key"]);
        }
        $token = sha1($unixtime . $method . $action . $code);
        $post = [
            "unixtime" => (string) $unixtime,
            "method" => $method,
            "action" => $action,
            "token" => ($tamper["hash"] ?? null) === "invalid" ? sha1("invalid") : $token,
        ];
        $post = array_merge($post, $this->substitute($step["post"] ?? [], $vars));
        foreach ($tamper["omit"] ?? [] as $name) {
            unset($post[$name]);
        }
        return $this->result("POST", "/sys.php/" . $step["path"], [], $post, ["token", "unixtime"]);
    }

    private function buildAns(array $step, array $vars, array $tamper): array
    {
        $query = http_build_query($this->substitute($step["query"] ?? [], $vars));
        $headers = [];
        if (($tamper["omit_hash_header"] ?? false) === false) {
            $hash = sha1($query . Secrets::ANS_SALT);
            if (($tamper["hash"] ?? null) === "invalid") {
                $hash = sha1("invalid");
            }
            $headers["X-ANS-Verify-Hash"] = $hash;
        }
        $path = "/ans.php" . ($query === "" ? "" : "?" . $query);
        return $this->result("GET", $path, $headers, [], [], ["X-ANS-Verify-Hash"]);
    }

    private function buildRaw(array $step, array $vars): array
    {
        return $this->result(
            $step["method"] ?? "POST",
            $step["path"],
            $step["headers"] ?? [],
            $this->substitute($step["post"] ?? [], $vars),
            []
        );
    }

    /**
     * @param string[] $volatileFields POST fields left out of the recorded request
     * @param string[] $volatileHeaders headers left out of the recorded request
     */
    private function result(
        string $method,
        string $path,
        array $headers,
        array $fields,
        array $volatileFields,
        array $volatileHeaders = []
    ): array {
        return [
            "method" => $method,
            "url" => rtrim($this->baseUrl, "/") . $path,
            "headers" => $headers,
            "fields" => $fields,
            "record" => [
                "method" => $method,
                "path" => $path,
                "headers" => array_diff_key($headers, array_flip($volatileHeaders)),
                "post" => array_diff_key($fields, array_flip($volatileFields)),
            ],
        ];
    }

    /** @return array{0: string, 1: string} module and area as the legacy router sees them (ucfirst) */
    private function splitPath(string $path): array
    {
        $bits = array_values(array_filter(explode("/", $path), fn ($bit) => $bit !== ""));
        return [ucfirst($bits[0] ?? ""), ucfirst($bits[1] ?? "")];
    }

    private function substitute(array $fields, array $vars): array
    {
        $out = [];
        foreach ($fields as $key => $value) {
            if (is_string($value)) {
                $value = preg_replace_callback(
                    "/\{\{([a-z0-9_]+)\}\}/",
                    fn ($m) => array_key_exists($m[1], $vars)
                        ? (string) $vars[$m[1]]
                        : throw new InvalidArgumentException("Unbound variable: " . $m[1]),
                    $value
                );
            }
            $out[$key] = is_bool($value) ? ($value ? "1" : "0") : (string) $value;
        }
        return $out;
    }
}
