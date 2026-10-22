<?php

namespace App\LegacyApi\Endpoints\Secondlifeapi\Client;

use App\LegacyApi\SlEndpoint;
use App\Models\Avatar;
use App\Models\Rental;

/** Does this avatar rent anything (status is always ok; the message says). */
class Hasrental extends SlEndpoint
{
    public function process(): void
    {
        $uuid = $this->input->uuid('checkinguuid');
        if ($uuid === null) {
            $this->ok('No UUID');

            return;
        }
        $avatar = Avatar::where('avatar_uuid', $uuid)->first();
        if ($avatar === null) {
            $this->ok('Unknown user');

            return;
        }
        $this->ok(Rental::where('avatar_id', $avatar->id)->exists() ? 'some' : 'No streams');
    }
}
