<?php

namespace App\Auth;

use App\Models\Staff;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Accepts 2.0.x staff passwords until they are upgraded.
 *
 * 2.0.x stored phash = first 42 chars of sha256(password . id . psalt . ownerLevel), where
 * ownerLevel was a PHP bool turned into a string ("1" or ""). The first successful login stores a
 * bcrypt hash in `password` and clears the old hash.
 */
class LegacyStaffUserProvider extends EloquentUserProvider
{
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        /** @var Staff $user */
        if ($user->password !== null) {
            return parent::validateCredentials($user, $credentials);
        }

        return $this->validLegacyPassword($user, (string) $credentials['password']);
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        /** @var Staff $user */
        if ($user->password !== null) {
            parent::rehashPasswordIfRequired($user, $credentials, $force);

            return;
        }

        $user->forceFill([
            'password' => $credentials['password'],
            'phash' => null,
            'psalt' => null,
            'lhash' => null,
        ])->save();
    }

    private function validLegacyPassword(Staff $user, string $password): bool
    {
        if ($user->phash === null || $user->phash === '' || $user->psalt === null || $user->psalt === '') {
            return false;
        }
        $raw = $password.$user->id.$user->psalt.($user->owner_level ? '1' : '');

        return hash_equals((string) $user->phash, substr(hash('sha256', $raw), 0, 42));
    }
}
