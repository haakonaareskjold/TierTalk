<?php

namespace App\Events;

use App\Models\TierTalkSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ParticipantKicked implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public TierTalkSession $session,
        public int $participantId,
        public string $participantToken
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('session.'.$this->session->id),
        ];
    }

    /**
     * @return array<string, mixed>
     */
        /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'participant_id' => $this->participantId,
            'participant_token' => $this->participantToken,
        ];
    }
}
