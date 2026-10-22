<?php

namespace App\LegacyApi;

/**
 * Reads request fields the way 2.0.x's YAPF InputFilter did, so stored values and request hashes
 * match the in-world scripts exactly:
 *
 * - a field only counts as missing when it is not sent at all (an empty string is a value);
 * - string values go through htmlentities() (2.0.x stored names that way, and hashed them that way);
 * - numbers are intval() of that string, so "abc" is 0;
 * - booleans are true only for "1", "true", "yes", "True" and "TRUE".
 */
class Input
{
    private const UUID = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    /** @param array<string, mixed> $fields */
    public function __construct(private readonly array $fields) {}

    public function has(string $field): bool
    {
        return array_key_exists($field, $this->fields);
    }

    public function string(string $field): ?string
    {
        if (! $this->has($field) || $this->fields[$field] === null) {
            return null;
        }
        $value = $this->fields[$field];

        return htmlentities(is_array($value) ? (string) json_encode($value) : (string) $value);
    }

    /** At least $min characters; "" counts as a value (as in 2.0.x). */
    public function stringMin(string $field, int $min): ?string
    {
        $value = $this->string($field);
        if ($value === null || $value === '') {
            return $value;
        }

        return strlen($value) < $min ? null : $value;
    }

    /** Between $min and $max characters ("" counts as a value). */
    public function stringLength(string $field, int $min, int $max): ?string
    {
        $value = $this->string($field);
        if ($value === null || $value === '') {
            return $value;
        }

        return strlen($value) < $min || strlen($value) > $max ? null : $value;
    }

    public function uuid(string $field): ?string
    {
        $value = $this->string($field);
        if ($value === null || $value === '') {
            return $value;
        }

        return preg_match(self::UUID, $value) === 1 ? $value : null;
    }

    public function int(string $field): ?int
    {
        $value = $this->string($field);

        return $value === null ? null : intval($value);
    }

    /** An int from $min up (a missing field stays null). */
    public function intMin(string $field, int $min): ?int
    {
        $value = $this->int($field);

        return $value === null || $value < $min ? null : $value;
    }

    public function bool(string $field): bool
    {
        return in_array($this->string($field), ['1', 'true', 'yes', 'True', 'TRUE'], true);
    }
}
