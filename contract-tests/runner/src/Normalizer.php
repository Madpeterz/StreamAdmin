<?php

declare(strict_types=1);

namespace Contract;

/**
 * Turns a response body into a stable, comparable structure.
 *
 *   {"$time": <seconds from request>, "$type": "int"|"string"}  unix times near the request time
 *   {"$var": "<name>"}                                          values captured by an earlier step
 *   {"$mask": "<type>"}                                         keys listed in the step's "mask"
 *   "{{name}}"                                                  captured values embedded in strings
 *   {"$raw": "<text>"}                                          non-JSON bodies
 *   {"$json": ...}                                              JSON payloads inside string fields
 *   "{{site_url}}"                                              the target's public URL
 *   "@@@{{bot_sig_ok}}" / "@@@{{bot_sig_bad}}"                   bot command signatures, checked with the bot secret
 *   "{{sys_key}}"                                               valid short lived sys.php key in FetchNextNotecard
 */
final class Normalizer
{
    private const TIME_WINDOW = 400 * 86400;

    /**
     * @param string[] $siteUrls site URLs the target may emit (replaced with {{site_url}})
     */
    public function __construct(private readonly array $siteUrls = [])
    {
    }

    /**
     * @param array<string, string> $vars
     * @param string[] $mask
     */
    public function normalize(string $body, int $now, array $vars, array $mask): mixed
    {
        $decoded = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE || (is_array($decoded) === false)) {
            return ['$raw' => $this->embedVars($body, $vars)];
        }
        return $this->walk($decoded, $now, $vars, array_flip($mask), "");
    }

    private function walk(mixed $value, int $now, array $vars, array $mask, string $path): mixed
    {
        if ($path !== "" && array_key_exists($path, $mask)) {
            return ['$mask' => get_debug_type($value)];
        }
        if (is_array($value)) {
            $out = [];
            foreach ($value as $key => $item) {
                $out[$key] = $this->walk($item, $now, $vars, $mask, $path === "" ? (string) $key : $path . "." . $key);
            }
            return $out;
        }
        if (is_int($value) || is_string($value)) {
            foreach ($vars as $name => $varValue) {
                if ((string) $value === (string) $varValue && strlen((string) $varValue) >= 4) {
                    return ['$var' => $name];
                }
            }
        }
        if ($this->isUnixtime($value, $now)) {
            return ['$time' => (int) $value - $now, '$type' => get_debug_type($value)];
        }
        if (is_string($value) && in_array(substr($value, 0, 1), ["{", "["], true)) {
            $nested = json_decode($value, true);
            if (is_array($nested)) {
                // JSON payload inside a string field (e.g. eventMessage)
                return ['$json' => $this->walk($nested, $now, $vars, $mask, $path . ".\$json")];
            }
        }
        if (is_string($value)) {
            return $this->replaceDates($this->embedVars($this->replaceSiteUrls($this->checkBotSignature($value)), $vars));
        }
        return $value;
    }

    /** Bot commands are "Command|||arg~#~arg@@@sha1(Command . args . botSecret)". */
    private function checkBotSignature(string $value): string
    {
        if (preg_match("/^([A-Za-z]+)\|\|\|(.*)@@@([0-9a-f]{40})$/s", $value, $m) !== 1) {
            return $value;
        }
        $ok = sha1($m[1] . $m[2] . Secrets::BOT_SECRET) === $m[3];
        $args = $m[2];
        if ($m[1] === "FetchNextNotecard") {
            $bits = explode("~#~", $args);
            if (in_array($bits[1] ?? null, [Secrets::sysKey("Notecard", "Next"), Secrets::sysKey("Notecard", "Next", -1)], true)) {
                $bits[1] = "{{sys_key}}";
            }
            $args = implode("~#~", $bits);
        }
        return $m[1] . "|||" . $args . "@@@{{" . ($ok ? "bot_sig_ok" : "bot_sig_bad") . "}}";
    }

    private function replaceSiteUrls(string $value): string
    {
        foreach ($this->siteUrls as $url) {
            $value = str_replace($url, "{{site_url}}", $value);
        }
        return $value;
    }

    /**
     * Absolute dates, e.g. "Thursday 5th of November 2026 01:36:54 PM" (PHP "l jS \of F Y h:i:s A") become {{datetime}}.
     * Durations ("14 days, 0 hours", "5 hours, 3 mins", "12 mins") become {{dur:<minutes>~<tolerance>}},
     * because a rental created a second earlier can read "13 days, 23 hours".
     */
    private function replaceDates(string $value): string
    {
        $value = (string) preg_replace(
            "/\b(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday) \d{1,2}(st|nd|rd|th) of [A-Z][a-z]+ \d{4} \d{2}:\d{2}:\d{2} (AM|PM)\b/",
            "{{datetime}}",
            $value
        );
        $value = (string) preg_replace("/\b\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\b/", "{{datetime}}", $value);
        $value = (string) preg_replace_callback(
            "/\b(\d+) days, (\d+) hours\b/",
            fn ($m) => "{{dur:" . (((int) $m[1] * 24 + (int) $m[2]) * 60) . "~60}}",
            $value
        );
        $value = (string) preg_replace_callback(
            "/\b(\d+) hours, (\d+) mins\b/",
            fn ($m) => "{{dur:" . ((int) $m[1] * 60 + (int) $m[2]) . "~2}}",
            $value
        );
        return (string) preg_replace_callback(
            "/(?<![\d,] )\b(\d+) mins\b/",
            fn ($m) => "{{dur:" . (int) $m[1] . "~2}}",
            $value
        );
    }

    private function isUnixtime(mixed $value, int $now): bool
    {
        if (is_int($value) === false && (is_string($value) === false || ctype_digit($value) === false)) {
            return false;
        }
        $int = (int) $value;
        return $int > 1000000000 && abs($int - $now) <= self::TIME_WINDOW;
    }

    private function embedVars(string $value, array $vars): string
    {
        foreach ($vars as $name => $varValue) {
            $varValue = (string) $varValue;
            if (strlen($varValue) >= 4) {
                $value = str_replace($varValue, "{{" . $name . "}}", $value);
            }
        }
        return $value;
    }
}
