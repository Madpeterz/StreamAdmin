<?php

namespace App\Api\Http\Controllers;

use App\Api\ApiException;
use App\Models\SlConfig;
use App\Services\VendorRentals;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The renter HUD. The caller's avatar is the HUD's wearer, who can only see and pay their own
 * rentals. Renewals need details, and are off while the rental time limit is on.
 */
class HudController extends ApiController
{
    public function __construct(private readonly VendorRentals $rentals) {}

    public function config(): JsonResponse
    {
        $config = SlConfig::findOrFail(1);

        return $this->ok('ok', [
            'allowDiscord' => (bool) $config->hud_allow_discord,
            'allowGroup' => (bool) $config->hud_allow_group,
            'allowDetails' => (bool) $config->hud_allow_details,
            'allowRenewal' => (bool) ($config->hud_allow_renewal && $config->hud_allow_details && ! $config->limit_time),
            'groupLink' => $config->hud_group_link,
            'discordLink' => $config->hud_discord_link,
        ]);
    }

    public function rentals(Request $request): JsonResponse
    {
        $this->requireDetails();
        $result = $this->rentals->rentalsOf($this->caller($request)->avatar);

        return $this->ok($result['message'], $result['data']);
    }

    public function cost(Request $request): JsonResponse
    {
        $this->requireDetails();
        $this->requireRenewals();
        $data = $this->data($request, ['rentalUid' => ['required', 'string', 'max:16']]);

        return $this->ok('ok', $this->rentals->cost($data['rentalUid'], $this->caller($request)->avatar));
    }

    public function renew(Request $request): JsonResponse
    {
        $this->requireDetails();
        $this->requireRenewals();
        if (SlConfig::findOrFail(1)->limit_time) {
            throw ApiException::disabled('Hud renewals are disabled while the rental time limit is enforced');
        }
        $data = $this->data($request, [
            'rentalUid' => ['required', 'string', 'max:16'],
            'amountPaid' => ['required', 'integer', 'min:1'],
            'transactionKey' => ['required', 'uuid'],
        ]);
        $wearer = $this->caller($request)->avatar;
        $result = $this->rentals->renew($this->caller($request), $data['rentalUid'], (int) $data['amountPaid'], $wearer, $data['transactionKey'], $wearer);

        return $this->ok($result['message'], $result['data']);
    }

    public function sendDetails(Request $request): JsonResponse
    {
        $this->requireDetails();
        $data = $this->data($request, ['rentalUid' => ['required', 'string', 'max:16']]);

        return $this->ok($this->rentals->sendDetails($data['rentalUid'], $this->caller($request)->avatar));
    }

    private function requireDetails(): void
    {
        if (! SlConfig::findOrFail(1)->hud_allow_details) {
            throw ApiException::disabled('Hud details are currently disabled');
        }
    }

    private function requireRenewals(): void
    {
        if (! SlConfig::findOrFail(1)->hud_allow_renewal) {
            throw ApiException::disabled('Hud renewals are currently disabled');
        }
    }
}
