<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('private-match.{matchId}', function ($user, int $matchId) {
    // Replace this with actual ACL check.
    // Example: return $user->can('view-match', $matchId);
    return ! is_null($user);
});
