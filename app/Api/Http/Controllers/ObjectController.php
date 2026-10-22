<?php

namespace App\Api\Http\Controllers;

use Illuminate\Http\JsonResponse;

/** Objects check in; identifying the caller already recorded the object as seen. */
class ObjectController extends ApiController
{
    public function ping(): JsonResponse
    {
        return $this->ok('pong');
    }
}
