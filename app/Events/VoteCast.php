<?php

namespace App\Events;

use App\Models\Question;
use App\Models\TierTalkSession;
use App\Models\Vote;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoteCast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public TierTalkSession $session,
        public Question $question,
        public Vote $vote,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('session.' . $this->session->id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'question_id' => $this->question->id,
            'vote_counts' => $this->question->vote_counts,
            'total_votes' => $this->question->votes()->count(),
        ];
    }
}
