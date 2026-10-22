<?php

namespace App\Api\Http\Middleware;

use App\Api\ApiClass;
use App\Api\ApiException;
use App\Api\Signature;
use App\Models\SlConfig;
use App\Support\SystemApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Checks X-SA-Class, X-SA-Timestamp, X-SA-Nonce and X-SA-Signature (see Signature), rejects a nonce
 * that was already used, and hands on the parsed JSON body. Use as api.signed:vendor,owner with the key
 * classes the route accepts.
 */
class VerifySignature
{
    public function handle(Request $request, Closure $next, string ...$allowed): Response
    {
        $class = ApiClass::tryFrom((string) $request->header('X-SA-Class'));
        $timestamp = (string) $request->header('X-SA-Timestamp');
        $nonce = (string) $request->header('X-SA-Nonce');
        $signature = (string) $request->header('X-SA-Signature');
        if ($class === null || $signature === '' || ! ctype_digit($timestamp) || preg_match('/^[A-Za-z0-9]{16,64}$/', $nonce) !== 1) {
            throw ApiException::unauthorized('Missing or malformed X-SA-Class, X-SA-Timestamp, X-SA-Nonce or X-SA-Signature header');
        }
        if (abs((int) $timestamp - time()) > Signature::WINDOW) {
            throw ApiException::unauthorized('The timestamp is outside the allowed window');
        }

        $body = $request->getContent();
        $path = $request->getPathInfo();
        $secret = $class->secret(SlConfig::findOrFail(1));
        $codes = strlen($secret) < Signature::MIN_SECRET_LENGTH ? [] : [$secret];
        if ($class === ApiClass::Bot && $codes !== []) {
            // the bot is given a short lived key for one path instead of the secret
            array_push($codes, ...SystemApiKey::accepted($secret, $path));
        }
        $valid = false;
        foreach ($codes as $code) {
            $valid = hash_equals(Signature::sign($code, $timestamp, $nonce, $path, $body), $signature) || $valid;
        }
        if (! $valid) {
            throw ApiException::unauthorized('Invalid signature');
        }
        // only a request that was really signed is remembered, so strangers cannot fill the cache
        if (! Cache::add('api-v3:nonce:'.sha1($class->value.$nonce), true, 2 * Signature::WINDOW + 1)) {
            throw ApiException::replay();
        }
        if (! in_array($class->value, $allowed, true)) {
            throw ApiException::forbidden('This endpoint is not available to the '.$class->value.' key');
        }

        $data = $body === '' ? [] : json_decode($body, true);
        if (! is_array($data)) {
            throw ApiException::invalid('The body must be a JSON object');
        }
        $request->attributes->set('api.class', $class);
        $request->attributes->set('api.body', $data);

        return $next($request);
    }
}
