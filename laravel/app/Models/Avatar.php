<?php

namespace App\Models;

use App\Casts\UnixTimestamp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * A Second Life avatar known to the system (clients, resellers, staff, the bot).
 */
class Avatar extends Model
{
    protected $table = 'avatars';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'last_used' => UnixTimestamp::class,
        ];
    }

    /** @return HasMany<Rental, $this> */
    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class, 'avatar_id');
    }

    /** @return HasMany<Transaction, $this> */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'avatar_id');
    }

    /** @return HasOne<Reseller, $this> */
    public function reseller(): HasOne
    {
        return $this->hasOne(Reseller::class, 'avatar_id');
    }

    /** @return HasOne<Ban, $this> */
    public function ban(): HasOne
    {
        return $this->hasOne(Ban::class, 'avatar_id');
    }

    /** @return HasOne<Staff, $this> */
    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class, 'avatar_id');
    }

    /** @return HasMany<Message, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'avatar_id');
    }
}
