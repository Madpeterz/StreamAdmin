<?php

namespace App\LegacyApi\Endpoints\Secondlifeapi\Proxyrenew;

use App\LegacyApi\Endpoints\Secondlifeapi\Renew\Details as RenewDetails;
use App\LegacyApi\SlEndpoint;
use App\Models\Avatar;

/**
 * Find someone else's rentals to pay for, by "First Last" name or SL UUID. Found avatars get the
 * Renew/Details reply (which, as in 2.0.x, has no module/area tags).
 */
class Details extends SlEndpoint
{
    public function process(): void
    {
        $this->set('dataset_count', 0);
        $target = $this->input->stringMin('targetuid', 3);
        if ($target === null || $target === '') {
            $this->failed('Unable to find avatar');

            return;
        }
        $avatar = match (true) {
            count(explode(' ', $target)) === 2 => Avatar::where('avatar_name', $target)->first(),
            strlen($target) === 36 => Avatar::where('avatar_uuid', $target)->first(),
            default => null,
        };
        if ($avatar === null) {
            $this->failed('Unable to find avatar!');

            return;
        }
        $this->output->reset();
        (new RenewDetails($this->input, $this->output, $this->request, $this->module, $this->area))->detailsFor($avatar);
    }
}
