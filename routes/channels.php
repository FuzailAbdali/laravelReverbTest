<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('private-match.{matchId}', function ($user, int $matchId) {
    return isset($user->team_id, $user->allowed_match_ids)
        && in_array($matchId, (array) $user->allowed_match_ids, true);
});
