<?php

namespace App\LegacyApi\Endpoints\Secondlifeapi\Object;

use App\LegacyApi\SlEndpoint;

/** Objects check in; the gateway's checks already recorded the object as seen. */
class Ping extends SlEndpoint
{
    public function process(): void
    {
        $this->ok('pong');
    }
}
