<?php

namespace App\Api;

use RuntimeException;

/**
 * An API call that cannot be done. Rendered as {"ok": false, "code": ..., "message": ...} with the
 * HTTP status of the code; anything done so far in the request is rolled back.
 */
class ApiException extends RuntimeException
{
    /** @param array<string, mixed> $errors */
    public function __construct(
        public readonly string $errorCode,
        string $message,
        public readonly int $status,
        public readonly array $errors = [],
    ) {
        parent::__construct($message);
    }

    /** The body is not usable (not JSON, missing headers...). */
    public static function invalid(string $message): self
    {
        return new self('invalid_request', $message, 400);
    }

    /** Missing or wrong signature, stale timestamp. */
    public static function unauthorized(string $message): self
    {
        return new self('unauthorized', $message, 401);
    }

    /** The key is good but is not allowed to do this. */
    public static function forbidden(string $message): self
    {
        return new self('forbidden', $message, 403);
    }

    /** A feature the owner has switched off. */
    public static function disabled(string $message): self
    {
        return new self('disabled', $message, 403);
    }

    public static function notFound(string $message): self
    {
        return new self('not_found', $message, 404);
    }

    /** The same nonce was already used. */
    public static function replay(): self
    {
        return new self('replay', 'This request was already used', 409);
    }

    /** A well formed request that the rules refuse (wrong amount, nothing in stock...). */
    public static function rejected(string $message): self
    {
        return new self('rejected', $message, 422);
    }

    /** @param array<string, list<string>> $errors */
    public static function validation(string $message, array $errors = []): self
    {
        return new self('validation', $message, 422, $errors);
    }
}
