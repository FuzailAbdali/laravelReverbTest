<?php

namespace App\Http\Controllers;

use App\Events\ScoreUpdated;
use App\Models\MatchScore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScoreController extends Controller
{
    public function update(Request $request, int $matchId): JsonResponse
    {
        $payload = $request->validate([
            'home_team' => ['required', 'string', 'max:255'],
            'away_team' => ['required', 'string', 'max:255'],
            'home_score' => ['required', 'integer', 'min:0'],
            'away_score' => ['required', 'integer', 'min:0'],
        ]);

        $score = MatchScore::updateOrCreate(
            ['match_id' => $matchId],
            [
                'home_team' => $payload['home_team'],
                'away_team' => $payload['away_team'],
                'home_score' => $payload['home_score'],
                'away_score' => $payload['away_score'],
                'updated_by' => optional($request->user())->id,
            ]
        );

        broadcast(new ScoreUpdated(
            matchId: $matchId,
            homeTeam: $score->home_team,
            awayTeam: $score->away_team,
            homeScore: $score->home_score,
            awayScore: $score->away_score,
            updatedBy: (string) ($request->user()->name ?? 'system'),
        ))->toOthers();

        return response()->json([
            'status' => 'ok',
            'data' => $score,
        ]);
    }
}
