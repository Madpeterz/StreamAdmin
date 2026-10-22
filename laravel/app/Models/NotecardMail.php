<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Static notecard waiting to be sent by the notice server.
 */
class NotecardMail extends Model
{
    protected $table = 'notecard_mails';

    public $timestamps = false;

    protected $guarded = ['id'];

    /** @return BelongsTo<Avatar, $this> */
    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Avatar::class, 'avatar_id');
    }

    /** @return BelongsTo<NoticeNotecard, $this> */
    public function noticeNotecard(): BelongsTo
    {
        return $this->belongsTo(NoticeNotecard::class, 'notice_notecard_id');
    }
}
