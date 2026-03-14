<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    protected $fillable = [
        'match_id',
        'home_score',
        'away_score',
        'updated_by',
    ];
}
