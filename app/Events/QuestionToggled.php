<?php

namespace App\Events;

use App\Models\Question;
use App\Models\TierTalkSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuestionToggled implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public TierTalkSession $session,
        public Question $question,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('session.'.$this->session->id),
        ];
    }
}
