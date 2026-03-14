<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CameraSource extends Model
{
    protected $fillable = [
        'name',
        'stream_key',
        'ingest_mode',
        'source_rtsp_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
