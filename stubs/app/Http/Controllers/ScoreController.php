<?php

namespace App\Http\Controllers;

use App\Events\ScoreUpdated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScoreController extends Controller
{
    public function update(Request $request, int $matchId): JsonResponse
    {
        $data = $request->validate([
            'home_score' => ['required', 'integer', 'min:0'],
            'away_score' => ['required', 'integer', 'min:0'],
        ]);

        // Persist to DB here (Match model, etc.) then broadcast.
        broadcast(new ScoreUpdated(
            matchId: $matchId,
            homeScore: (int) $data['home_score'],
            awayScore: (int) $data['away_score'],
            updatedBy: optional($request->user())->id,
        ))->toOthers();

        return response()->json([
            'ok' => true,
            'match_id' => $matchId,
            'home_score' => (int) $data['home_score'],
            'away_score' => (int) $data['away_score'],
        ]);
    }
}
