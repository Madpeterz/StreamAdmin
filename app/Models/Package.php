<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A stream product: listeners, bitrate, price and rental period.
 */
class Package extends Model
{
    protected $table = 'packages';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'autodj' => 'boolean',
            'enable_group_invite' => 'boolean',
            'enforce_custom_max_streams' => 'boolean',
        ];
    }

    /** @return BelongsTo<Template, $this> */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'template_id');
    }

    /** @return BelongsTo<ServerType, $this> */
    public function serverType(): BelongsTo
    {
        return $this->belongsTo(ServerType::class, 'server_type_id');
    }

    /** @return BelongsTo<NoticeNotecard, $this> */
    public function welcomeNotecard(): BelongsTo
    {
        return $this->belongsTo(NoticeNotecard::class, 'welcome_notecard_id');
    }

    /** @return BelongsTo<NoticeNotecard, $this> */
    public function setupNotecard(): BelongsTo
    {
        return $this->belongsTo(NoticeNotecard::class, 'setup_notecard_id');
    }

    /** @return HasMany<Stream, $this> */
    public function streams(): HasMany
    {
        return $this->hasMany(Stream::class, 'package_id');
    }

    /** @return HasMany<Rental, $this> */
    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class, 'package_id');
    }
}
