<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('scores.public', fn () => true);

Broadcast::channel('scores.match.{matchId}', function ($user, $matchId) {
    return $user->can('view-match', (int) $matchId);
});
