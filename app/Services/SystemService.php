<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Avatar;
use App\Models\Ban;
use App\Models\BotConfig;
use App\Models\Message;
use App\Models\NotecardMail;
use App\Models\Reseller;
use App\Models\SlConfig;
use App\Models\SlObject;
use App\Models\Staff;
use App\Models\TexturePack;
use App\Models\Transaction;
use App\Support\Uid;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Staff actions for avatars, bans, staff accounts, texture packs and system settings
 * (port of the 2.0.x Avatar, Banlist, Staff, Textureconfig and Slconfig controls).
 */
class SystemService
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function createAvatar(string $name, string $uuid): Avatar
    {
        return DB::transaction(function () use ($name, $uuid) {
            $avatar = Avatar::create([
                'avatar_name' => $name,
                'avatar_uuid' => $uuid,
                'avatar_uid' => Uid::unique(Avatar::class, 'avatar_uid'),
                'last_used' => time(),
                'credits' => 0,
            ]);
            $this->audit->log('Avatar', (string) $avatar->id, '+++', null, $name);

            return $avatar;
        });
    }

    /** @param array<string, mixed> $attributes */
    public function updateAvatar(Avatar $avatar, array $attributes): void
    {
        DB::transaction(function () use ($avatar, $attributes) {
            $avatar->update($attributes);
            $this->audit->logChanges('Avatar', (string) $avatar->id, $avatar);
        });
    }

    /** Why this avatar cannot be removed, or null when it can. */
    public function avatarInUse(Avatar $avatar): ?string
    {
        $config = SlConfig::find(1);

        return match (true) {
            $avatar->id === $config?->owner_avatar_id => 'It is the system owner.',
            BotConfig::where('avatar_id', $avatar->id)->exists() => 'It is the bot avatar.',
            Staff::where('avatar_id', $avatar->id)->exists() => 'It is linked to a staff account.',
            Reseller::where('avatar_id', $avatar->id)->exists() => 'It is a reseller.',
            $avatar->rentals()->exists() => 'It has rentals.',
            default => null,
        };
    }

    /**
     * Its payments and audit log entries move to the system owner; its ban, objects and unsent
     * mail go with it.
     */
    public function deleteAvatar(Avatar $avatar): void
    {
        if ($reason = $this->avatarInUse($avatar)) {
            throw new RuntimeException('This avatar cannot be removed. '.$reason);
        }
        $ownerId = SlConfig::findOrFail(1)->owner_avatar_id;
        DB::transaction(function () use ($avatar, $ownerId) {
            Transaction::where('avatar_id', $avatar->id)->update(['avatar_id' => $ownerId]);
            Transaction::where('target_avatar_id', $avatar->id)->update(['target_avatar_id' => $ownerId]);
            AuditLog::where('avatar_id', $avatar->id)->update(['avatar_id' => $ownerId]);
            Ban::where('avatar_id', $avatar->id)->delete();
            SlObject::where('avatar_id', $avatar->id)->delete();
            Message::where('avatar_id', $avatar->id)->delete();
            NotecardMail::where('avatar_id', $avatar->id)->delete();
            $this->audit->log('Avatar', (string) $avatar->id, '---', $avatar->avatar_name);
            $avatar->delete();
        });
    }

    public function ban(Avatar $avatar): Ban
    {
        if (Ban::where('avatar_id', $avatar->id)->exists()) {
            throw new RuntimeException($avatar->avatar_name.' is already banned.');
        }
        if ($avatar->id === SlConfig::find(1)?->owner_avatar_id) {
            throw new RuntimeException('The system owner cannot be banned.');
        }

        return DB::transaction(function () use ($avatar) {
            $ban = Ban::create(['avatar_id' => $avatar->id]);
            $this->audit->log('Banlist', (string) $ban->id, '+++', null, $avatar->avatar_name);

            return $ban;
        });
    }

    public function unban(Ban $ban): void
    {
        DB::transaction(function () use ($ban) {
            $this->audit->log('Banlist', (string) $ban->id, '---', $ban->avatar?->avatar_name);
            $ban->delete();
        });
    }

    public function createStaff(string $username, Avatar $avatar, string $password): Staff
    {
        return DB::transaction(function () use ($username, $avatar, $password) {
            $staff = Staff::create([
                'username' => $username,
                'avatar_id' => $avatar->id,
                'password' => $password,
                'owner_level' => false,
                'email_reset_expires' => 0,
            ]);
            $this->audit->log('Staff', (string) $staff->id, '+++', null, $avatar->avatar_name);

            return $staff;
        });
    }

    public function updateStaff(Staff $staff, string $username, ?string $password): void
    {
        DB::transaction(function () use ($staff, $username, $password) {
            if ($username !== $staff->username) {
                $this->audit->log('Staff', (string) $staff->id, 'Username changed', $staff->username, $username);
            }
            $staff->username = $username;
            if ($password !== null && $password !== '') {
                // a new password also signs them out everywhere
                $staff->forceFill(['password' => $password, 'remember_token' => Str::random(60)]);
                $this->audit->log('Staff', (string) $staff->id, 'Password changed', null, $username);
            }
            $staff->save();
        });
    }

    public function deleteStaff(Staff $staff, Staff $by): void
    {
        if ($staff->owner_level) {
            throw new RuntimeException('Owner level staff cannot be removed here.');
        }
        if ($staff->is($by)) {
            throw new RuntimeException('You cannot remove yourself.');
        }
        DB::transaction(function () use ($staff) {
            // their sessions end on the next request, as the account no longer exists
            $this->audit->log('Staff', (string) $staff->id, '---', $staff->avatar?->avatar_name);
            $staff->delete();
        });
    }

    /** @param array<string, mixed> $attributes */
    public function saveTexturePack(?TexturePack $pack, array $attributes): TexturePack
    {
        return DB::transaction(function () use ($pack, $attributes) {
            if ($pack === null) {
                $pack = TexturePack::create($attributes);
                $this->audit->log('TexturePack', (string) $pack->id, '+++', null, $pack->name);

                return $pack;
            }
            $pack->update($attributes);
            $this->audit->logChanges('TexturePack', (string) $pack->id, $pack);

            return $pack;
        });
    }

    public function deleteTexturePack(TexturePack $pack): void
    {
        DB::transaction(function () use ($pack) {
            $this->audit->log('TexturePack', (string) $pack->id, '---', $pack->name);
            $pack->delete();
        });
    }

    /**
     * Applies the 2.0.x rules: HUD links are switched off when empty, renewals need details, and
     * HUD renewals are off while the rental time limit is on (they would bypass it).
     *
     * @param  array<string, mixed>  $attributes
     */
    public function saveSettings(array $attributes): SlConfig
    {
        foreach (['discord', 'group'] as $link) {
            if (! $attributes["hud_allow_{$link}"]) {
                $attributes["hud_{$link}_link"] = '';
            }
            if (trim((string) $attributes["hud_{$link}_link"]) === '') {
                $attributes["hud_allow_{$link}"] = false;
            }
        }
        if (! $attributes['hud_allow_details'] || $attributes['limit_time']) {
            $attributes['hud_allow_renewal'] = false;
        }
        $config = SlConfig::findOrFail(1);

        return DB::transaction(function () use ($config, $attributes) {
            $config->update($attributes);
            $this->audit->logChanges('Slconfig', (string) $config->id, $config);

            return $config;
        });
    }

    /** Length of every link code and the inbound secret this app generates. */
    public const KEY_LENGTH = 16;

    /** The link codes and the inbound secret (sl_configs columns). */
    private const KEY_COLUMNS = ['sl_link_code', 'owner_link_code', 'hud_link_code', 'public_link_code', 'http_inbound_secret'];

    /** The value the 2.0.x installer put in every key. */
    private const INSTALL_KEY = 'install';

    /** New link codes and inbound secret: every vendor, HUD and script must be given the new ones. */
    public function reissueKeys(): void
    {
        DB::transaction(function () {
            SlConfig::findOrFail(1)->update($this->newKeys(self::KEY_COLUMNS));
            $this->audit->log('Slconfig', '1', 'keys reissued');
        });
    }

    /**
     * A fresh install ships every key as "install", which anyone can guess. The first staff login
     * replaces those with random ones; keys that were already changed are left alone.
     */
    public function replaceInstallKeys(): void
    {
        $replaced = [];
        DB::transaction(function () use (&$replaced) {
            foreach (self::KEY_COLUMNS as $column) {
                // conditional update, so two logins at once cannot replace a key twice
                if (SlConfig::whereKey(1)->where($column, self::INSTALL_KEY)->update([$column => Str::random(self::KEY_LENGTH)]) > 0) {
                    $replaced[] = $column;
                }
            }
            if ($replaced !== []) {
                $this->audit->log('Slconfig', '1', 'install keys replaced', null, implode(', ', $replaced));
            }
        });
    }

    /**
     * @param  list<string>  $columns
     * @return array<string, string>
     */
    private function newKeys(array $columns): array
    {
        return array_combine($columns, array_map(fn () => Str::random(self::KEY_LENGTH), $columns));
    }

    public function savePaymentKey(string $key): void
    {
        DB::transaction(function () use ($key) {
            SlConfig::findOrFail(1)->update(['payment_key' => $key]);
            $this->audit->log('Slconfig', '1', 'payment key changed');
        });
    }
}
