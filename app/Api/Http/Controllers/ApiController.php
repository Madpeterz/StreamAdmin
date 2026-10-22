<?php

namespace App\Api\Http\Controllers;

use App\Api\ApiException;
use App\Api\Caller;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Shared by the /api/v3 controllers. A request body is {"object": ..., "owner": ..., "data": {...}}:
 * the first two say who is calling (see IdentifyCaller), "data" is what the endpoint takes.
 * Replies are {"ok": true, "message": "...", "data": {...}}.
 */
abstract class ApiController extends Controller
{
    protected function caller(Request $request): Caller
    {
        return $request->attributes->get('api.caller');
    }

    /**
     * Validate the endpoint's "data".
     *
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    protected function data(Request $request, array $rules): array
    {
        $body = $request->attributes->get('api.body');
        try {
            return Validator::make(is_array($body['data'] ?? null) ? $body['data'] : [], $rules)->validate();
        } catch (ValidationException $e) {
            throw ApiException::validation($e->validator->errors()->first(), $e->errors());
        }
    }

    /** Free text is sent through llEscapeURL (everything signed has to be ASCII). */
    protected function text(string $value): string
    {
        return rawurldecode($value);
    }

    /** @param array<string, mixed> $data */
    protected function ok(string $message, array $data = []): JsonResponse
    {
        return response()->json(['ok' => true, 'message' => $message, 'data' => (object) $data]);
    }
}
