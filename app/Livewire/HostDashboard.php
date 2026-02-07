<?php

namespace App\Livewire;

use App\Events\ParticipantKicked;
use App\Events\QuestionAdded;
use App\Events\QuestionReset;
use App\Events\SessionEnded;
use App\Models\TierTalkSession;
use App\Models\Question;
use Livewire\Attributes\On;
use Livewire\Component;

class HostDashboard extends Component
{
    public TierTalkSession $session;
    public string $newQuestion = '';

    /**
     * @var array<int, string>
     */
    public array $newOptions = ['Yes', 'No'];

    public function mount(TierTalkSession $session): void
    {
        $this->session = $session;
    }

    public function addNewOption(): void
    {
        $this->newOptions[] = '';
    }

    public function removeNewOption(int $index): void
    {
        if (count($this->newOptions) > 2) {
            unset($this->newOptions[$index]);
            $this->newOptions = array_values($this->newOptions);
        }
    }

    public function addQuestion(): void
    {
        $this->validate([
            'newQuestion' => 'required|string|min:3|max:500',
            'newOptions' => 'required|array|min:2',
            'newOptions.*' => 'required|string|min:1|max:100',
        ]);

        $maxOrder = $this->session->questions()->max('order') ?? -1;

        $question = $this->session->questions()->create([
            'question_text' => $this->newQuestion,
            'answer_options' => array_values(array_filter($this->newOptions)),
            'order' => $maxOrder + 1,
        ]);

        QuestionAdded::dispatch($this->session, $question);

        $this->newQuestion = '';
        $this->newOptions = ['Yes', 'No'];
    }

    public function resetQuestion(int $questionId): void
    {
        $question = $this->session->questions()->findOrFail($questionId);
        $question->resetVotes();

        QuestionReset::dispatch($this->session, $question);
    }

    public function toggleQuestion(int $questionId): void
    {
        $question = $this->session->questions()->findOrFail($questionId);
        $question->update(['is_active' => !$question->is_active]);
    }

    public function deleteQuestion(int $questionId): void
    {
        $this->session->questions()->where('id', $questionId)->delete();
    }

    public function kickParticipant(int $participantId): void
    {
        $participant = $this->session->participants()->findOrFail($participantId);
        $token = $participant->token;

        // Delete participant's votes and the participant record
        $participant->votes()->delete();
        $participant->delete();

        ParticipantKicked::dispatch($this->session, $participantId, $token);
    }

    public function endSession(): void
    {
        $this->session->update(['status' => 'ended']);

        SessionEnded::dispatch($this->session);

        $this->redirect(route('home'));
    }

    #[On('echo:session.{session.id},VoteCast')]
    public function refreshVotes(): void
    {
        $this->session->refresh();
    }

    #[On('echo:session.{session.id},ParticipantJoined')]
    public function refreshParticipants(): void
    {
        $this->session->refresh();
    }

    public function render()
    {
        return view('livewire.host-dashboard', [
            'questions' => $this->session->questions()->with('votes')->get(),
            'participants' => $this->session->participants,
        ]);
    }
}
