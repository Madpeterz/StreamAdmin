<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Vendor that offers several packages.
 */
class TreeVendor extends Model
{
    protected $table = 'tree_vendors';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'hide_soldout' => 'boolean',
        ];
    }

    /** @return BelongsToMany<Package, $this> */
    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'tree_vendor_packages');
    }
}
