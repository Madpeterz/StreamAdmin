<?php

namespace App\LegacyApi\Endpoints\Secondlifeapi\Renew;

use App\LegacyApi\SlEndpoint;
use App\Models\Avatar;
use App\Models\Rental;
use App\Support\Swaps;

/**
 * What renewing a rental costs: price per period, who to pay, and how the period reads
 * ("Day", "Week" or "Month" with a stack size). The message is the time left.
 */
class Costandtime extends SlEndpoint
{
    public function process(): void
    {
        $this->costOf(null);
    }

    /** @param  Avatar|null  $onlyFor  the HUD may only price its wearer's own rentals */
    public function costOf(?Avatar $onlyFor): void
    {
        $rental = Rental::where('rental_uid', $this->input->string('rentalUid'))->first();
        if ($rental === null) {
            $this->failed('Unable to find rental');

            return;
        }
        $package = $rental->package;
        if ($package === null) {
            $this->failed('Unable to load package');

            return;
        }
        if ($onlyFor !== null && $onlyFor->id !== $rental->avatar_id) {
            $this->failed('Unable to renew someone elses rental via your hud, please use the proxy pay!');

            return;
        }
        $systemOwner = Avatar::find($this->slConfig()->owner_avatar_id);
        if ($systemOwner === null) {
            $this->failed('Unable to load system owner');

            return;
        }
        $this->set('systemowner', $systemOwner->avatar_uuid);
        $this->set('cost', $package->cost);
        [$word, $daysPerStack] = match (true) {
            $package->days % 7 === 0 => ['Week', 7],
            $package->days % 31 === 0, $package->days % 30 === 0 => ['Month', 31],
            default => ['Day', 1],
        };
        $this->set('word', $word);
        $this->set('stacksize', (int) ceil($package->days / $daysPerStack));
        $this->ok(Swaps::timeLeft($rental->expire_unixtime));
    }
}
