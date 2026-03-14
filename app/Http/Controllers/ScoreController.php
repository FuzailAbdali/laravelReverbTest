<?php

namespace App\Http\Controllers;

use App\Events\ScoreUpdated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScoreController extends Controller
{
    public function update(Request $request, int $matchId): JsonResponse
    {
        $payload = $request->validate([
            'home_score' => ['required', 'integer', 'min:0'],
            'away_score' => ['required', 'integer', 'min:0'],
        ]);

        broadcast(new ScoreUpdated(
            matchId: $matchId,
            homeScore: $payload['home_score'],
            awayScore: $payload['away_score'],
            updatedBy: (string) optional($request->user())->id ?: 'system'
        ));

        return response()->json([
            'ok' => true,
            'match_id' => $matchId,
            'home_score' => $payload['home_score'],
            'away_score' => $payload['away_score'],
        ]);
    }
}
