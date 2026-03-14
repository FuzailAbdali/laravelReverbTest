<?php

namespace App\Http\Controllers;

use App\Events\ScoreUpdated;
use App\Models\Score;
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

        $score = Score::updateOrCreate(
            ['match_id' => $matchId],
            [
                'home_score' => $data['home_score'],
                'away_score' => $data['away_score'],
                'updated_by' => $request->user()?->email ?? 'system',
            ]
        );

        broadcast(new ScoreUpdated(
            matchId: $score->match_id,
            homeScore: $score->home_score,
            awayScore: $score->away_score,
            updatedBy: $score->updated_by,
        ))->toOthers();

        return response()->json([
            'status' => 'ok',
            'score' => $score,
        ]);
    }
}
