<?php

namespace App\Events;

use App\Models\Participant;
use App\Models\TierTalkSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ParticipantJoined implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public TierTalkSession $session,
        public Participant $participant,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('session.'.$this->session->id),
        ];
    }

        /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'participant' => [
                'id' => $this->participant->id,
                'username' => $this->participant->username,
            ],
            'participant_count' => $this->session->participants()->count(),
        ];
    }
}
