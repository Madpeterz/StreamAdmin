<?php

namespace App\Api;

use App\Models\Avatar;
use App\Models\Region;
use App\Models\Reseller;
use App\Models\SlObject;

/**
 * Who is calling, worked out from the key class and the identity in the request body.
 * For the owner class the avatar is always the system owner; the HUD's avatar is its wearer.
 */
final class Caller
{
    public function __construct(
        public readonly ApiClass $class,
        public readonly Avatar $avatar,
        public readonly ?Region $region,
        public readonly ?Reseller $reseller,
        public readonly ?SlObject $object,
    ) {}

    public function isOwner(): bool
    {
        return $this->class === ApiClass::Owner;
    }
}
