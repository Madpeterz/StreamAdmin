<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Text template for stream details (IM and notecard).
 */
class Template extends Model
{
    protected $table = 'templates';

    public $timestamps = false;

    protected $guarded = ['id'];

    /** @return HasMany<Package, $this> */
    public function packages(): HasMany
    {
        return $this->hasMany(Package::class, 'template_id');
    }
}
