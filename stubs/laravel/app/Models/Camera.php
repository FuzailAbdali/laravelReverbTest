<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Camera extends Model
{
    protected $fillable = [
        'name',
        'mode',
        'stream_key',
        'rtsp_url',
        'is_active',
        'notes',
    ];
}
