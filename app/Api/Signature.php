<?php

namespace App\Api;

/**
 * Request signing for /api/v3. The signature is base64( HMAC-SHA256( secret, message ) ), the same
 * as LSL's llHMAC(secret, message, "sha256"), over
 *
 *   timestamp "\n" nonce "\n" path "\n" body
 *
 * where path is the request path (/api/v3/...) and body is the exact JSON text that was sent, so
 * every field is signed. Everything signed has to be ASCII (LSL cannot hash other bytes): scripts
 * send free text such as names through llEscapeURL.
 */
final class Signature
{
    /** seconds either side of server time that a timestamp is accepted */
    public const WINDOW = 120;

    /** shorter secrets are never accepted */
    public const MIN_SECRET_LENGTH = 16;

    public static function sign(string $secret, string $timestamp, string $nonce, string $path, string $body): string
    {
        return base64_encode(hash_hmac('sha256', $timestamp."\n".$nonce."\n".$path."\n".$body, $secret, true));
    }
}
