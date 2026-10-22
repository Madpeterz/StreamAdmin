<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Dynamic notecard waiting to be built and sent by the bot.
 */
class Notecard extends Model
{
    protected $table = 'notecards';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'as_notice' => 'boolean',
        ];
    }

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
