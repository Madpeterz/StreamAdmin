<?php

declare(strict_types=1);

namespace Contract;

/**
 * Compares a recorded (normalized) response with an actual one.
 * Types are strict: LSL reads "1", 1 and true differently.
 */
final class Comparator
{
    /** Allowed drift in seconds for {"$time": ...} values. */
    private const TIME_TOLERANCE = 120;

    /** @return string[] differences, empty when equal */
    public function diff(mixed $expected, mixed $actual, string $path = '$'): array
    {
        if (is_array($expected) && array_key_exists('$time', $expected)) {
            if (is_array($actual) === false || array_key_exists('$time', $actual) === false) {
                return [$path . ": expected a unix time, got " . $this->show($actual)];
            }
            $diffs = [];
            if ($expected['$type'] !== $actual['$type']) {
                $diffs[] = $path . ": unix time type " . $expected['$type'] . " != " . $actual['$type'];
            }
            if (abs($expected['$time'] - $actual['$time']) > self::TIME_TOLERANCE) {
                $diffs[] = $path . ": unix time offset " . $expected['$time'] . "s != " . $actual['$time'] . "s";
            }
            return $diffs;
        }
        if (is_array($expected) && array_key_exists('$mask', $expected)) {
            if (is_array($actual) && ($actual['$mask'] ?? null) === $expected['$mask']) {
                return [];
            }
            return [$path . ": expected masked " . $expected['$mask'] . ", got " . $this->show($actual)];
        }
        if (is_array($expected) && is_array($actual)) {
            if (array_is_list($expected) && array_is_list($actual)) {
                if (count($expected) !== count($actual)) {
                    return [$path . ": list length " . count($expected) . " != " . count($actual)];
                }
            }
            $diffs = [];
            foreach ($expected as $key => $value) {
                if (array_key_exists($key, $actual) === false) {
                    $diffs[] = $path . "." . $key . ": missing";
                    continue;
                }
                $diffs = array_merge($diffs, $this->diff($value, $actual[$key], $path . "." . $key));
            }
            foreach (array_diff_key($actual, $expected) as $key => $value) {
                $diffs[] = $path . "." . $key . ": unexpected " . $this->show($value);
            }
            return $diffs;
        }
        if (is_string($expected) && is_string($actual) && $this->durationsMatch($expected, $actual)) {
            return [];
        }
        if ($expected !== $actual) {
            return [$path . ": " . $this->show($expected) . " != " . $this->show($actual)];
        }
        return [];
    }

    /** Strings equal apart from {{dur:<minutes>~<tolerance>}} tokens that are within tolerance. */
    private function durationsMatch(string $expected, string $actual): bool
    {
        $pattern = "/\{\{dur:(\d+)~(\d+)\}\}/";
        if (preg_replace($pattern, "{{dur}}", $expected) !== preg_replace($pattern, "{{dur}}", $actual)) {
            return false;
        }
        preg_match_all($pattern, $expected, $e, PREG_SET_ORDER);
        preg_match_all($pattern, $actual, $a, PREG_SET_ORDER);
        if (count($e) === 0) {
            return false;
        }
        foreach ($e as $i => $token) {
            $tolerance = max((int) $token[2], (int) $a[$i][2]);
            if (abs((int) $token[1] - (int) $a[$i][1]) > $tolerance) {
                return false;
            }
        }
        return true;
    }

    private function show(mixed $value): string
    {
        $json = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (strlen($json) > 120) {
            $json = substr($json, 0, 117) . "...";
        }
        return $json;
    }
}
