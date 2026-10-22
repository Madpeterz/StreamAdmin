<?php

namespace App\LegacyApi\Endpoints\Secondlifeapi\Renew;

use App\LegacyApi\SlEndpoint;
use App\Models\Avatar;
use App\Models\Ban;
use App\Support\Swaps;

/**
 * An avatar's rentals for a renewal point: "{rentalUid}|||{port}|||{time left}" per rental.
 * Also used by Proxyrenew/Details and the HUD's Rentals/Details.
 */
class Details extends SlEndpoint
{
    public function process(): void
    {
        $uuid = $this->input->uuid('avatarUUID');
        if ($uuid === null) {
            $this->failed('Invaild avatar');

            return;
        }
        $this->set('dataset_count', 0);
        $avatar = Avatar::where('avatar_uuid', $uuid)->first();
        if ($avatar === null) {
            $this->failed('Unable to find avatar');

            return;
        }
        $this->detailsFor($avatar);
    }

    public function detailsFor(Avatar $avatar): void
    {
        if (Ban::where('avatar_id', $avatar->id)->exists()) {
            $this->failed('Unable to find avatar');

            return;
        }
        // 2.0.x loadByAvatarLink: newest first
        $rentals = $avatar->rentals()->with('stream')->orderByDesc('id')->get();
        if ($rentals->isEmpty()) {
            $this->ok('Unable to find any active rentals');

            return;
        }
        $dataset = $rentals->filter(fn ($rental) => $rental->stream !== null)
            ->map(fn ($rental) => $rental->rental_uid.'|||'.$rental->stream->port.'|||'.Swaps::timeLeft($rental->expire_unixtime))
            ->values()->all();
        if ($dataset === []) {
            $this->failed('Unable to find any streams linked to rentals, Please note if a stream is busy with an API request it will be hidden from this list!');

            return;
        }
        $this->set('dataset_count', count($dataset));
        $this->set('dataset', $dataset);
        $this->ok('Client account: '.$avatar->avatar_name);
    }
}
