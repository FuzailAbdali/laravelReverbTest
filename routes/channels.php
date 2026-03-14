<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('score.match.{matchId}', function ($user, int $matchId) {
    // Replace with your own match membership/role check.
    return in_array($matchId, $user->allowed_match_ids ?? [], true);
});
