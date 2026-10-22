<?php

namespace App\LegacyApi;

use App\Models\Avatar;
use App\Models\Package;
use App\Models\Region;
use App\Models\Reseller;
use App\Models\SlObject;
use App\Models\Stream;
use App\Models\Transaction;
use App\Support\Uid;

/**
 * api.php endpoints: requests from vendors and server objects (port of 2.0.x SecondlifeAjax).
 *
 * The checks and their messages are part of the in-world contract (docs/lsl-contract.md): each
 * check is skipped once one has failed, except the version check, which always runs last and
 * overwrites the message.
 */
abstract class SlEndpoint extends Endpoint
{
    public const MIN_VERSION = '2.0.1.0';

    /** the fields every object sends, in request hash order; "k" fields must be UUIDs */
    private const REQUIRED = [
        'version' => 's', 'mode' => 's', 'objectuuid' => 'k', 'regionname' => 's', 'ownerkey' => 'k',
        'ownername' => 's', 'pos' => 's', 'objectname' => 's', 'objecttype' => 's',
    ];

    /** record the calling object, and make its owner a reseller (off for the HUD) */
    protected bool $trackObject = true;

    /** @var array<string, string> the REQUIRED values as sent (after htmlentities) */
    protected array $fields = ['version' => ''];

    protected int $unixtime = 0;

    protected string $hash = '';

    protected ?Avatar $ownerAvatar = null;

    protected ?Region $region = null;

    protected ?Reseller $reseller = null;

    protected ?SlObject $object = null;

    /** the calling object belongs to the system owner */
    protected bool $ownerOverride = false;

    public function boot(): void
    {
        $this->requiredValues();
        if ($this->loadOk) {
            $this->timeWindow();
        }
        if ($this->loadOk) {
            $this->hashCheck();
        }
        if (version_compare($this->fields['version'] ?? '', self::MIN_VERSION, '>=') === false) {
            $this->loadOk = false;
            $this->failed('Requires version: '.self::MIN_VERSION.' or higher given version '.($this->fields['version'] ?? ''));
        }
        if ($this->loadOk) {
            $this->failed('ready');
        } else {
            $this->set('status', false);
        }
    }

    /** the link code requests to this entry point are signed with */
    protected function linkCode(): string
    {
        return (string) $this->slConfig()->sl_link_code;
    }

    protected function hashFailedMessage(): string
    {
        return 'Unable to vaildate request to API endpoint';
    }

    private function requiredValues(): void
    {
        foreach (self::REQUIRED as $field => $type) {
            $value = $type === 'k' ? $this->input->uuid($field) : $this->input->stringMin($field, 1);
            if ($value === null) {
                $this->loadOk = false;
                $this->failed('Value: '.$field.' is missing');

                return;
            }
            $this->fields[$field] = $value;
        }
        $unixtime = $this->input->int('unixtime');
        if ($unixtime === null) {
            $this->loadOk = false;
            $this->failed('Missing unixtime value');

            return;
        }
        $this->unixtime = $unixtime;
        $hash = $this->input->string('hash');
        if ($hash === null) {
            $this->loadOk = false;
            $this->failed('Missing hash value');

            return;
        }
        $this->hash = $hash;
    }

    private function timeWindow(): void
    {
        if (! $this->inTimeWindow($this->unixtime)) {
            $this->loadOk = false;
            $this->failed('timewindow is out of scope');
        }
    }

    /**
     * A request that names the system owner as its owner must be signed with the owner link code,
     * not the vendor one every reseller holds. The HUD is signed by its wearer with the HUD code.
     */
    private function claimsOwner(): bool
    {
        if (! $this->trackObject) {
            return false;
        }
        $ownerUuid = $this->slConfig()->ownerAvatar?->avatar_uuid;

        return $ownerUuid !== null && strcasecmp($ownerUuid, $this->fields['ownerkey']) === 0;
    }

    private function hashCheck(): void
    {
        $owner = $this->claimsOwner();
        $code = $owner ? (string) $this->slConfig()->owner_link_code : $this->linkCode();
        $raw = $this->unixtime.$this->module.$this->area.implode('', array_intersect_key($this->fields, self::REQUIRED)).$code;
        // an empty owner code (never set) must not let anyone in as the owner
        if (($owner && strlen($code) < 8) || ! hash_equals(sha1($raw), $this->hash)) {
            $this->loadOk = false;
            $this->failed($this->hashFailedMessage());

            return;
        }
        if ($this->alreadySeen($this->hash, $this->unixtime)) {
            $this->loadOk = false;
            $this->failed('Request already used');

            return;
        }
        $this->identifyCaller();
    }

    /** grid check, then the owner avatar, region, reseller and object (2.0.x continueHashChecks) */
    private function identifyCaller(): void
    {
        $grid = $this->request->header('X-SecondLife-Shard');
        if ($grid !== 'Production') {
            $this->loadOk = false;
            $this->failed('wrong grid connected: '.($grid ?? 'none'));

            return;
        }
        $this->ownerAvatar = self::avatarFor($this->fields['ownerkey'], $this->fields['ownername']);
        if ($this->ownerAvatar === null) {
            $this->loadOk = false;
            $this->failed('Unable to load owner avatar for this object!');

            return;
        }
        if (strlen($this->fields['regionname']) < 3) {
            $this->loadOk = false;
            $this->failed('Unable to load region');

            return;
        }
        $this->region = Region::firstOrCreate(['name' => $this->fields['regionname']]);
        if (! $this->trackObject) {
            return;
        }

        $config = $this->slConfig();
        $this->reseller = Reseller::firstOrCreate(
            ['avatar_id' => $this->ownerAvatar->id],
            ['allowed' => (bool) $config->new_resellers, 'rate' => (int) $config->new_resellers_rate],
        );
        $this->ownerOverride = $config->owner_avatar_id === $this->ownerAvatar->id;
        if (! $this->reseller->allowed && ! $this->ownerOverride) {
            $this->loadOk = false;
            $this->failed('Unable to access this api - please contact owner');

            return;
        }
        $this->object = $this->trackObject();
    }

    /** 2.0.x ObjectHelper: a known object only has its name, region and last seen time updated. */
    private function trackObject(): SlObject
    {
        $object = SlObject::where('object_uuid', $this->fields['objectuuid'])->first();
        if ($object === null) {
            return SlObject::create([
                'avatar_id' => $this->ownerAvatar->id,
                'region_id' => $this->region->id,
                'object_uuid' => $this->fields['objectuuid'],
                'object_name' => $this->fields['objectname'],
                'object_mode' => $this->fields['objecttype'],
                'object_xyz' => $this->fields['pos'],
                'last_seen' => time(),
            ]);
        }
        $object->fill([
            'object_name' => $this->fields['objectname'],
            'region_id' => $this->region->id,
            'last_seen' => time(),
        ])->save();

        return $object;
    }

    /**
     * 2.0.x AvatarHelper::loadOrCreate: find by SL UUID (updating the name and last used time), or
     * create with a new UID when a name is given.
     */
    public static function avatarFor(?string $uuid, ?string $name = null): ?Avatar
    {
        if ($uuid === null || strlen($uuid) !== 36) {
            return null;
        }
        $avatar = Avatar::where('avatar_uuid', $uuid)->first();
        if ($avatar !== null) {
            if ($name !== null && $name !== $avatar->avatar_name) {
                $avatar->avatar_name = $name;
            }
            $avatar->last_used = time();
            $avatar->save();

            return $avatar;
        }
        if ($name === null) {
            return null;
        }

        return Avatar::create([
            'avatar_uid' => Uid::unique(Avatar::class, 'avatar_uid'),
            'avatar_name' => $name,
            'avatar_uuid' => $uuid,
            'last_used' => time(),
            'credits' => 0,
        ]);
    }

    /** 2.0.x TransactionsHelper::createTransaction */
    protected function recordPayment(Avatar $avatar, Package $package, Stream $stream, int $amount, bool $renewal = false, ?string $notes = null): Transaction
    {
        return Transaction::create([
            'avatar_id' => $avatar->id,
            'package_id' => $package->id,
            'stream_id' => $stream->id,
            'reseller_id' => $this->reseller->id,
            'region_id' => $this->region->id,
            'amount' => $amount,
            'unixtime' => time(),
            'notes' => $notes,
            'transaction_uid' => Uid::unique(Transaction::class, 'transaction_uid'),
            'renew' => $renewal,
        ]);
    }

    /**
     * Prepaid credit (from Marketplace coupons) is refunded against a payment made at one of the
     * system owner's vendors (2.0.x useCredits): the vendor pays back up to the amount paid, and a
     * negative transaction records it.
     */
    protected function useCredits(Avatar $systemOwner, Avatar $avatar, Package $package, Stream $stream, int $amountPaid, bool $renewal = false): void
    {
        $this->set('credit-return', 0);
        $this->set('credit-remaining', 0);
        if ($this->reseller?->avatar_id !== $systemOwner->id || $avatar->credits <= 0) {
            return;
        }
        $refund = min($avatar->credits, $amountPaid);
        $avatar->update(['credits' => $avatar->credits - $refund]);
        $this->recordPayment($avatar, $package, $stream, 0 - $refund, $renewal, 'refund of prepaid credits');
        $this->set('credit-return', $refund);
        $this->set('credit-remaining', $avatar->credits);
    }

    /** What a reseller passes on to the system owner: all but their rate, leaving them at least 1 L$ when possible. */
    protected function ownerShare(int $amountPaid): int
    {
        $rate = (int) $this->reseller->rate;
        if ($rate <= 0) {
            return $amountPaid;
        }
        $resellerCut = (int) floor($amountPaid / 100 * $rate);
        $leftOver = $amountPaid - $resellerCut;
        if ($resellerCut < 1 && $leftOver >= 2) {
            $leftOver--;
        }

        return $leftOver;
    }

    /** owner-only endpoints (2.0.x hasAccessOwner) */
    protected function hasAccessOwner(): bool
    {
        if (! $this->ownerOverride) {
            $this->failed('SystemAPI access only - please contact support');

            return false;
        }

        return true;
    }
}
