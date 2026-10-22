<?php

/**
 * Contract test runner for the in-world APIs.
 *
 *   php runner/run.php record [scenario-filter]   run scenarios and write fixtures
 *   php runner/run.php verify [scenario-filter]   run scenarios and compare with fixtures
 *
 * Env: TARGET_URL, DB_HOST, DB_NAME, DB_USER, DB_PASS,
 *      SITE_URLS (comma separated public URLs the target emits), RESET_HOOK (optional)
 */

declare(strict_types=1);

namespace Contract;

require __DIR__ . "/src/Secrets.php";
require __DIR__ . "/src/Database.php";
require __DIR__ . "/src/RequestBuilder.php";
require __DIR__ . "/src/Normalizer.php";
require __DIR__ . "/src/Comparator.php";

$root = dirname(__DIR__);
$mode = $argv[1] ?? "verify";
$filter = $argv[2] ?? null;
if (in_array($mode, ["record", "verify"], true) === false) {
    fwrite(STDERR, "Usage: run.php record|verify [scenario-filter]\n");
    exit(2);
}

$env = fn (string $name, ?string $default = null): string => getenv($name) !== false
    ? (string) getenv($name)
    : ($default ?? throw new \RuntimeException("Missing env: " . $name));

$db = new Database(
    $env("DB_HOST"),
    $env("DB_NAME"),
    $env("DB_USER"),
    $env("DB_PASS"),
    $root . "/sql",
    $env("RESET_HOOK", "")
);
$builder = new RequestBuilder($env("TARGET_URL"));
$siteUrls = array_values(array_filter(array_map("trim", explode(",", $env("SITE_URLS", "")))));
$normalizer = new Normalizer($siteUrls);
$comparator = new Comparator();

$files = glob($root . "/scenarios/*.php");
sort($files);
$failures = 0;
$stepsRun = 0;

foreach ($files as $file) {
    $name = basename($file, ".php");
    if ($filter !== null && str_contains($name, $filter) === false) {
        continue;
    }
    $scenario = require $file;
    $fixturePath = $root . "/fixtures/" . $name . ".json";
    $fixture = null;
    if ($mode === "verify") {
        if (is_file($fixturePath) === false) {
            echo "MISSING  " . $name . " (no fixture, run record first)\n";
            $failures++;
            continue;
        }
        $fixture = json_decode((string) file_get_contents($fixturePath), true, 512, JSON_THROW_ON_ERROR);
    }

    $db->rebuild($scenario["seeds"] ?? []);
    $vars = [];
    $recorded = [];
    echo "SCENARIO " . $name . " - " . ($scenario["description"] ?? "") . "\n";

    foreach ($scenario["steps"] as $index => $step) {
        $stepsRun++;
        $id = $step["id"] ?? ("step" . $index);
        $now = time();
        $request = $builder->build($step, $vars, $now);
        $response = send($request);

        $decoded = json_decode($response["body"], true);
        // capture: ["var" => "body.key"] or ["var" => ["body.key", "/regex with (group)/"]]
        foreach ($step["capture"] ?? [] as $var => $spec) {
            [$key, $pattern] = is_array($spec) ? $spec : [$spec, null];
            $value = is_array($decoded) ? dig($decoded, $key) : null;
            if ($pattern !== null && is_string($value)) {
                $value = preg_match($pattern, $value, $match) === 1 ? $match[1] : null;
            }
            if ($value === null || $value === "") {
                echo "  FAIL   " . $id . ": capture " . $var . " <- " . $key . " is empty\n";
                $failures++;
                continue;
            }
            $vars[$var] = (string) $value;
        }

        $actual = [
            "http_status" => $response["status"],
            "content_type" => $response["content_type"],
            "body" => $normalizer->normalize($response["body"], $now, $vars, $step["mask"] ?? []),
        ];
        $entry = ["id" => $id, "request" => $request["record"], "response" => $actual];
        if (isset($step["legacy_bug"])) {
            $entry["legacy_bug"] = $step["legacy_bug"]["note"];
        }
        $recorded[] = $entry;

        $summary = is_array($decoded) ? (($decoded["status"] ?? "?") . " " . json_encode($decoded["message"] ?? null)) : "non-json";
        if ($mode === "record") {
            echo "  REC    " . $id . " [" . $response["status"] . "] " . $summary . "\n";
            continue;
        }

        $expected = $fixture["steps"][$index] ?? null;
        if ($expected === null || $expected["id"] !== $id) {
            echo "  FAIL   " . $id . ": fixture has no matching step (re-record?)\n";
            $failures++;
            continue;
        }
        $diffs = $comparator->diff($expected["response"]["http_status"], $actual["http_status"], '$status');
        $diffs = array_merge($diffs, $comparator->diff($expected["response"]["body"], $actual["body"], '$body'));
        if ($expected["response"]["content_type"] !== $actual["content_type"]) {
            echo "  WARN   " . $id . ": content type " . $expected["response"]["content_type"]
                . " != " . $actual["content_type"] . "\n";
        }
        if ($diffs === []) {
            echo "  PASS   " . $id . "\n";
            continue;
        }
        // legacy_bug: the recorded legacy response is a known defect; the fixed response is also accepted
        $fixed = $step["legacy_bug"]["fixed"] ?? null;
        if ($fixed !== null) {
            $fixedDiffs = array_merge(
                $comparator->diff($fixed["http_status"], $actual["http_status"], '$status'),
                $comparator->diff($fixed["body"], $actual["body"], '$body')
            );
            if ($fixedDiffs === []) {
                echo "  PASS   " . $id . " (legacy bug fixed: " . $step["legacy_bug"]["note"] . ")\n";
                continue;
            }
            $diffs = array_merge($diffs, ["-- also differs from the fixed response:"], $fixedDiffs);
        }
        $failures++;
        echo "  FAIL   " . $id . "\n";
        foreach ($diffs as $diff) {
            echo "         " . $diff . "\n";
        }
    }

    if ($mode === "record") {
        $out = [
            "scenario" => $name,
            "description" => $scenario["description"] ?? "",
            "seeds" => $scenario["seeds"] ?? [],
            "steps" => $recorded,
        ];
        file_put_contents(
            $fixturePath,
            json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
        );
    }
}

echo "\n" . $stepsRun . " steps, " . $failures . " failures\n";
exit($failures === 0 ? 0 : 1);

/**
 * @param array{method: string, url: string, headers: array<string, string>, fields: array<string, string>} $request
 * @return array{status: int, content_type: string, body: string}
 */
function send(array $request): array
{
    $curl = curl_init($request["url"]);
    $headers = [];
    foreach ($request["headers"] as $name => $value) {
        $headers[] = $name . ": " . $value;
    }
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CUSTOMREQUEST => $request["method"],
    ]);
    if ($request["method"] === "POST") {
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($request["fields"]));
    }
    $body = curl_exec($curl);
    if ($body === false) {
        throw new \RuntimeException("HTTP request failed: " . curl_error($curl));
    }
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    $contentType = (string) curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
    return ["status" => $status, "content_type" => strtolower(trim(explode(";", $contentType)[0])), "body" => $body];
}

function dig(array $data, string $key): mixed
{
    foreach (explode(".", $key) as $part) {
        if (is_array($data) === false || array_key_exists($part, $data) === false) {
            return null;
        }
        $data = $data[$part];
    }
    return $data;
}
