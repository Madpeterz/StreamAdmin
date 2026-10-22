<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Notice level a client opted out of for one rental.
 */
class RentalNoticeOptOut extends Model
{
    protected $table = 'rental_notice_opt_outs';

    public $timestamps = false;

    protected $guarded = ['id'];

    /** @return BelongsTo<Rental, $this> */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class, 'rental_id');
    }

    /** @return BelongsTo<Notice, $this> */
    public function notice(): BelongsTo
    {
        return $this->belongsTo(Notice::class, 'notice_id');
    }
}
