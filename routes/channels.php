<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('score.match.{matchId}', function ($user, int $matchId) {
    // Replace with your own match membership/role check.
    return in_array($matchId, $user->allowed_match_ids ?? [], true);
Broadcast::channel('private-match.{matchId}', function ($user, int $matchId) {
    return isset($user->team_id, $user->allowed_match_ids)
        && in_array($matchId, (array) $user->allowed_match_ids, true);
});
