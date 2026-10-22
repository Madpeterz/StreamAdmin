<?php

namespace App\Api\Http\Controllers;

use App\Api\ApiException;
use App\Models\Avatar;
use App\Services\AvatarDirectory;
use App\Services\VendorRentals;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Buying, renewing and looking up rentals from vendors and renewal points. */
class RentalController extends ApiController
{
    public function __construct(private readonly VendorRentals $rentals, private readonly AvatarDirectory $avatars) {}

    public function start(Request $request): JsonResponse
    {
        $data = $this->data($request, [
            'packageUid' => ['required', 'string', 'max:16'],
            'amountPaid' => ['required', 'integer', 'min:1'],
            'avatar' => ['required', 'array'],
            'avatar.key' => ['required', 'uuid'],
            'avatar.name' => ['required', 'string', 'min:1', 'max:255'],
        ]);
        $buyer = $this->avatars->findOrCreate($data['avatar']['key'], $this->text($data['avatar']['name']));
        $result = $this->rentals->start($this->caller($request), $data['packageUid'], $buyer, (int) $data['amountPaid']);

        return $this->ok($result['message'], $result['data']);
    }

    public function renew(Request $request): JsonResponse
    {
        $data = $this->data($request, [
            'rentalUid' => ['required', 'string', 'max:16'],
            'amountPaid' => ['required', 'integer', 'min:1'],
            'payer' => ['required', 'array'],
            'payer.key' => ['required', 'uuid'],
            'payer.name' => ['required', 'string', 'min:1', 'max:255'],
        ]);
        $payer = $this->avatars->findOrCreate($data['payer']['key'], $this->text($data['payer']['name']));
        $result = $this->rentals->renew($this->caller($request), $data['rentalUid'], (int) $data['amountPaid'], $payer);

        return $this->ok($result['message'], $result['data']);
    }

    public function cost(Request $request): JsonResponse
    {
        $data = $this->data($request, ['rentalUid' => ['required', 'string', 'max:16']]);

        return $this->ok('ok', $this->rentals->cost($data['rentalUid']));
    }

    /** An avatar's rentals, by its key. */
    public function list(Request $request): JsonResponse
    {
        $data = $this->data($request, ['avatarKey' => ['required', 'uuid']]);
        $avatar = $this->avatars->find($data['avatarKey']) ?? throw ApiException::notFound('Unable to find avatar');
        $result = $this->rentals->rentalsOf($avatar);

        return $this->ok($result['message'], $result['data']);
    }

    /** Someone else's rentals to pay for, found by "First Last" name or key. */
    public function find(Request $request): JsonResponse
    {
        $data = $this->data($request, ['target' => ['required', 'string', 'min:3', 'max:255']]);
        $target = $this->text($data['target']);
        $avatar = match (true) {
            count(explode(' ', $target)) === 2 => Avatar::where('avatar_name', $target)->first(),
            strlen($target) === 36 => Avatar::where('avatar_uuid', $target)->first(),
            default => null,
        } ?? throw ApiException::notFound('Unable to find avatar');
        $result = $this->rentals->rentalsOf($avatar);

        return $this->ok($result['message'], $result['data']);
    }

    public function sendDetails(Request $request): JsonResponse
    {
        $data = $this->data($request, ['rentalUid' => ['required', 'string', 'max:16']]);

        return $this->ok($this->rentals->sendDetails($data['rentalUid']));
    }

    public function hasRental(Request $request): JsonResponse
    {
        $data = $this->data($request, ['avatarKey' => ['required', 'uuid']]);
        $has = $this->rentals->hasRental($this->avatars->find($data['avatarKey']));

        return $this->ok($has ? 'some' : 'none', ['hasRental' => $has]);
    }
}
