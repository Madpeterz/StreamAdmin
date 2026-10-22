<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Textures shown by vendors in each state.
 */
class TexturePack extends Model
{
    protected $table = 'texture_packs';

    public $timestamps = false;

    protected $guarded = ['id'];
}
