<?php

namespace App\Livewire;

use App\Events\VoteCast;
use App\Models\Participant;
use App\Models\TierTalkSession;
use App\Models\Question;
use Livewire\Attributes\On;
use Livewire\Component;

class VotingInterface extends Component
{
    public TierTalkSession $session;
    public ?Participant $participant = null;
    public bool $sessionEnded = false;

    public function mount(TierTalkSession $session): void
    {
        $this->session = $session;
        $this->sessionEnded = $session->isExpired();

        // Get participant from session token
        $token = session('participant_token');
        if ($token) {
            $this->participant = Participant::where('token', $token)
                ->where('tier_talk_session_id', $session->id)
                ->first();
        }
    }

    public function vote(int $questionId, string $value): void
    {
        if (!$this->participant || $this->sessionEnded) {
            return;
        }

        $question = $this->session->questions()
            ->where('id', $questionId)
            ->where('is_active', true)
            ->first();

        if (!$question) {
            return;
        }

        // Validate the vote value against allowed options
        $allowedOptions = $question->answer_choices;
        if (!in_array($value, $allowedOptions)) {
            return;
        }

        // Check if already voted
        if ($this->participant->hasVotedOn($question)) {
            return;
        }

        $vote = $question->votes()->create([
            'participant_id' => $this->participant->id,
            'vote_value' => $value,
        ]);

        VoteCast::dispatch($this->session, $question, $vote);
    }

    #[On('echo:session.{session.id},QuestionAdded')]
    public function onQuestionAdded(): void
    {
        $this->session->refresh();
    }

    #[On('echo:session.{session.id},QuestionReset')]
    public function onQuestionReset(array $data): void
    {
        // Refresh to allow re-voting
        $this->session->refresh();
    }

    #[On('echo:session.{session.id},SessionEnded')]
    public function onSessionEnded(): void
    {
        $this->sessionEnded = true;
    }

    #[On('echo:session.{session.id},VoteCast')]
    public function onVoteCast(): void
    {
        $this->session->refresh();
    }

    #[On('echo:session.{session.id},ParticipantKicked')]
    public function onParticipantKicked(array $data): void
    {
        // Check if the kicked participant is the current user
        $myToken = session('participant_token');
        if ($myToken && isset($data['participant_token']) && $data['participant_token'] === $myToken) {
            // Clear participant session and redirect to join page
            session()->forget('participant_token');
            $this->participant = null;
            $this->redirect(route('session.join', $this->session->slug));
        }
    }

    public function render()
    {
        $questions = $this->session->questions()
            ->with('votes')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        $votedQuestionIds = [];
        if ($this->participant) {
            $votedQuestionIds = $this->participant->votes()
                ->pluck('question_id')
                ->toArray();
        }

        return view('livewire.voting-interface', [
            'questions' => $questions,
            'votedQuestionIds' => $votedQuestionIds,
        ]);
    }
}
