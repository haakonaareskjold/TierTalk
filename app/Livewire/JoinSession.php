<?php

namespace App\Livewire;

use App\Events\ParticipantJoined;
use App\Models\Participant;
use App\Models\TierTalkSession;
use Livewire\Component;

class JoinSession extends Component
{
    public TierTalkSession $session;

    public string $username = '';

    public bool $sessionFull = false;

    public bool $sessionExpired = false;

    public function mount(TierTalkSession $session): void
    {
        $this->session = $session;
        $this->sessionFull = ! $session->canAcceptParticipants();
        $this->sessionExpired = $session->isExpired();
    }

    public function join(): void
    {
        if ($this->sessionExpired || $this->sessionFull) {
            return;
        }

        $this->validate([
            'username' => 'required|string|min:2|max:50',
        ]);

        // Check if username is already taken in this session
        $exists = $this->session->participants()
            ->where('username', mb_strtolower($this->username))
            ->exists();

        if ($exists) {
            $this->addError('username', 'This username is already taken in this session.');

            return;
        }

        // Check capacity again
        if (! $this->session->canAcceptParticipants()) {
            $this->sessionFull = true;

            return;
        }

        $participant = $this->session->participants()->create([
            'username' => $this->username,
        ]);

        ParticipantJoined::dispatch($this->session, $participant);

        // Store participant token in session
        session(['participant_token' => $participant->token]);

        $this->redirect(route('session.vote', $this->session->slug));
    }

    public function render(): \Illuminate\View\View
    {
        /** @var \Illuminate\View\View $view */
        $view = view('livewire.join-session');

        return $view;
    }
}
