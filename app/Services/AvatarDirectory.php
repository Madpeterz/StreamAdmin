<?php

namespace App\Services;

use App\Models\Avatar;
use App\Support\Uid;

/**
 * Finds the avatar behind a Second Life UUID, creating it the first time it is seen (as 2.0.x
 * AvatarHelper::loadOrCreate did) and keeping its name current.
 */
class AvatarDirectory
{
    public function findOrCreate(string $uuid, string $name): Avatar
    {
        $avatar = Avatar::where('avatar_uuid', $uuid)->first();
        if ($avatar !== null) {
            if ($name !== $avatar->avatar_name) {
                $avatar->avatar_name = $name;
            }
            $avatar->last_used = time();
            $avatar->save();

            return $avatar;
        }

        return Avatar::create([
            'avatar_uid' => Uid::unique(Avatar::class, 'avatar_uid'),
            'avatar_name' => $name,
            'avatar_uuid' => $uuid,
            'last_used' => time(),
            'credits' => 0,
        ]);
    }

    public function find(string $uuid): ?Avatar
    {
        return Avatar::where('avatar_uuid', $uuid)->first();
    }
}
