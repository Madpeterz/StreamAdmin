<?php

namespace App\Services;

use App\Models\MarketplaceCoupon;
use App\Models\Package;
use App\Models\Reseller;
use App\Models\SlConfig;
use App\Models\Transaction;
use App\Models\TreeVendor;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Staff actions for transactions, resellers, tree vendors and marketplace coupons
 * (port of the 2.0.x Transactions, Reseller, Tree and Coupons controls).
 */
class SalesService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** Removing a payment does not change any rental's time. */
    public function deleteTransaction(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $this->audit->log('Transactions', $transaction->transaction_uid, '---', 'L$'.$transaction->amount, $transaction->avatar?->avatar_name);
            $transaction->delete();
        });
    }

    /** @param array{allowed: bool, rate: int} $attributes */
    public function updateReseller(Reseller $reseller, array $attributes): void
    {
        DB::transaction(function () use ($reseller, $attributes) {
            $reseller->update($attributes);
            $this->audit->logChanges('Reseller', (string) $reseller->id, $reseller);
        });
    }

    /** The reseller's sales move to the system owner's reseller entry. */
    public function deleteReseller(Reseller $reseller): void
    {
        $ownerAvatarId = SlConfig::find(1)?->owner_avatar_id;
        if ($reseller->avatar_id === $ownerAvatarId) {
            throw new RuntimeException('The system owner\'s reseller entry cannot be removed.');
        }
        $owner = Reseller::where('avatar_id', $ownerAvatarId)->first();
        if ($owner === null && $reseller->transactions()->exists()) {
            throw new RuntimeException('Unable to find the system owner\'s reseller entry to move this reseller\'s sales to.');
        }
        DB::transaction(function () use ($reseller, $owner) {
            $reseller->transactions()->update(['reseller_id' => $owner?->id]);
            $this->audit->log('Reseller', (string) $reseller->id, '---', $reseller->avatar?->avatar_name);
            $reseller->delete();
        });
    }

    /** @param array<string, mixed> $attributes */
    public function saveTreeVendor(?TreeVendor $vendor, array $attributes): TreeVendor
    {
        return DB::transaction(function () use ($vendor, $attributes) {
            if ($vendor === null) {
                $vendor = TreeVendor::create($attributes + [
                    'texture_waiting' => '00000000-0000-0000-0000-000000000000',
                    'texture_inuse' => '00000000-0000-0000-0000-000000000000',
                ]);
                $this->audit->log('Tree', (string) $vendor->id, '+++', null, $vendor->name);

                return $vendor;
            }
            $vendor->update($attributes);
            $this->audit->logChanges('Tree', (string) $vendor->id, $vendor);

            return $vendor;
        });
    }

    public function addTreePackage(TreeVendor $vendor, Package $package): void
    {
        if ($vendor->packages()->whereKey($package->id)->exists()) {
            throw new RuntimeException('This package is already on this tree vendor.');
        }
        $vendor->packages()->attach($package->id);
        $this->audit->log('Tree', (string) $vendor->id, 'link package', 'Tree:'.$vendor->name, 'package: '.$package->name);
    }

    public function removeTreePackage(TreeVendor $vendor, Package $package): void
    {
        $vendor->packages()->detach($package->id);
        $this->audit->log('Tree', (string) $vendor->id, 'unlink package', 'Tree:'.$vendor->name, 'package: '.$package->name);
    }

    public function deleteTreeVendor(TreeVendor $vendor): void
    {
        DB::transaction(function () use ($vendor) {
            $vendor->packages()->detach();
            $this->audit->log('Tree', (string) $vendor->id, '---', $vendor->name);
            $vendor->delete();
        });
    }

    /** @param array{listing_id: int, cost: int, credit: int} $attributes */
    public function saveCoupon(?MarketplaceCoupon $coupon, array $attributes): MarketplaceCoupon
    {
        return DB::transaction(function () use ($coupon, $attributes) {
            if ($coupon === null) {
                $coupon = MarketplaceCoupon::create($attributes + ['claims' => 0, 'last_claim' => 0]);
                $this->audit->log('Coupons', (string) $coupon->id, '+++', null, 'listing '.$coupon->listing_id);

                return $coupon;
            }
            $coupon->update($attributes);
            $this->audit->logChanges('Coupons', (string) $coupon->id, $coupon);

            return $coupon;
        });
    }

    public function deleteCoupon(MarketplaceCoupon $coupon): void
    {
        DB::transaction(function () use ($coupon) {
            $this->audit->log('Coupons', (string) $coupon->id, '---', 'listing '.$coupon->listing_id);
            $coupon->delete();
        });
    }
}
