<?php

namespace App\Api\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * A call that does not succeed leaves nothing behind in the database (2.0.x rolled back every
 * request that did not succeed). The router has already turned an ApiException (or any other
 * error) into its response when it gets back here, so the status decides.
 */
class InTransaction
{
    public function handle(Request $request, Closure $next): Response
    {
        DB::beginTransaction();
        try {
            $response = $next($request);
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
        $response->isSuccessful() ? DB::commit() : DB::rollBack();

        return $response;
    }
}
