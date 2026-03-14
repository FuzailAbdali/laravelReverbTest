<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchScore extends Model
{
    protected $fillable = [
        'match_id',
        'home_team',
        'away_team',
        'home_score',
        'away_score',
        'updated_by',
    ];
}
