<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Streaming software (Shoutcast, Icecast).
 */
class ServerType extends Model
{
    protected $table = 'server_types';

    public $timestamps = false;

    protected $guarded = ['id'];
}
