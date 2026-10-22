<?php

namespace App\LegacyApi\Endpoints\Hudapi\Rentals;

use App\LegacyApi\Endpoints\Secondlifeapi\Renew\Renewnow;
use App\LegacyApi\HudEndpoint;
use App\Models\Reseller;

/**
 * The HUD wearer renews one of their own rentals; the HUD has already taken the payment in SL,
 * on behalf of the system owner, so it is recorded against the owner's reseller entry.
 */
class Renew extends HudEndpoint
{
    public function process(): void
    {
        $config = $this->slConfig();
        if (! $config->hud_allow_details) {
            $this->failed('Hud details are currently disabled');

            return;
        }
        if (! $config->hud_allow_renewal) {
            $this->failed('Hud renewals are currently disabled');

            return;
        }
        if ($config->limit_time) {
            $this->failed('Hud renewals are disabled while the rental time limit is enforced');

            return;
        }
        $slTransactionUuid = $this->input->uuid('transactionUUID');
        if ($slTransactionUuid === null || $slTransactionUuid === '') {
            $this->failed('Invaild SL transaction ID');

            return;
        }
        $ownerReseller = Reseller::firstOrCreate(['avatar_id' => $config->owner_avatar_id], ['allowed' => true, 'rate' => 0]);
        $this->output->reset();
        (new Renewnow($this->input, $this->output, $this->request, $this->module, $this->area))
            ->actingFor($ownerReseller, $this->region, true)
            ->renew($this->ownerAvatar, $slTransactionUuid);
    }
}
