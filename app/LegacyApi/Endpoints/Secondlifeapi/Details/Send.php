<?php

namespace App\LegacyApi\Endpoints\Secondlifeapi\Details;

use App\LegacyApi\SlEndpoint;
use App\Models\Avatar;
use App\Models\DetailRequest;
use App\Models\Rental;

/** Queue a rental's stream details to be sent again (also the HUD's Details/Resend). */
class Send extends SlEndpoint
{
    public function process(): void
    {
        $this->send(null);
    }

    /** @param  Avatar|null  $onlyFor  the HUD may only ask for its wearer's own rentals */
    public function send(?Avatar $onlyFor): void
    {
        $rentalUid = $this->input->string('rentalUid');
        if ($rentalUid === null || $rentalUid === '') {
            $this->failed('No rental requested please check your API docs');

            return;
        }
        $rental = Rental::where('rental_uid', $rentalUid)->first();
        if ($rental === null) {
            $this->failed('Unable to find rental');

            return;
        }
        if ($onlyFor !== null && $rental->avatar_id !== $onlyFor->id) {
            $this->failed("You can not request someone else's rental details via the hud!");

            return;
        }
        if (DetailRequest::where('rental_id', $rental->id)->exists()) {
            $this->ok('You already have a pending details request - please wait for it or contact support.');

            return;
        }
        DetailRequest::create(['rental_id' => $rental->id]);
        $this->ok('Details request accepted, it should be with you shortly!');
    }
}
