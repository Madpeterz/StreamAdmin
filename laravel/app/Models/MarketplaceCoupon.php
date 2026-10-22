<?php

namespace App\Models;

use App\Casts\UnixTimestamp;
use Illuminate\Database\Eloquent\Model;

/**
 * Marketplace listing that tops up credits (ANS).
 */
class MarketplaceCoupon extends Model
{
    protected $table = 'marketplace_coupons';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'last_claim' => UnixTimestamp::class,
        ];
    }
}
