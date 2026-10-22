<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Expiry notice level (hours remaining) and its message.
 */
class Notice extends Model
{
    protected $table = 'notices';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'send_object_im' => 'boolean',
            'use_bot' => 'boolean',
            'send_notecard' => 'boolean',
        ];
    }

    /** @return BelongsTo<NoticeNotecard, $this> */
    public function noticeNotecard(): BelongsTo
    {
        return $this->belongsTo(NoticeNotecard::class, 'notice_notecard_id');
    }

    /** @return HasMany<Rental, $this> */
    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class, 'notice_id');
    }
}
