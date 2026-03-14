<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScoreUpdated implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public int $matchId,
        public int $homeScore,
        public int $awayScore,
        public string $updatedBy
        public string $homeTeam,
        public string $awayTeam,
        public int $homeScore,
        public int $awayScore,
        public ?string $updatedBy = null,
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('score.public'),
            new PrivateChannel("score.match.{$this->matchId}"),
            new Channel('scoreboard.' . $this->matchId),
            new PrivateChannel('private-match.' . $this->matchId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'score.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'match_id' => $this->matchId,
            'home_team' => $this->homeTeam,
            'away_team' => $this->awayTeam,
            'home_score' => $this->homeScore,
            'away_score' => $this->awayScore,
            'updated_by' => $this->updatedBy,
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
