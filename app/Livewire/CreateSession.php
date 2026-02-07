<?php

namespace App\Livewire;

use App\Models\TierTalkSession;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Create Session - TierTalk')]
class CreateSession extends Component
{
    public string $title = '';
    public int $maxParticipants = 20;
    public int $expirationHours = 1;

    /**
     * @var array<int, array{text: string, options: array<int, string>}>
     */
    public array $questions = [];

    public function mount(): void
    {
        $this->questions = [
            ['text' => '', 'options' => ['Yes', 'No']],
        ];
    }

    protected function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'maxParticipants' => 'required|integer|min:2|max:100',
            'expirationHours' => 'required|integer|min:1|max:24',
            'questions' => 'required|array|min:1',
            'questions.*.text' => 'required|string|min:3|max:500',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.options.*' => 'required|string|min:1|max:100',
        ];
    }

    protected $messages = [
        'questions.*.text.required' => 'Each question is required.',
        'questions.*.text.min' => 'Each question must be at least 3 characters.',
        'questions.*.options.required' => 'Each question needs at least 2 answer options.',
        'questions.*.options.min' => 'Each question needs at least 2 answer options.',
        'questions.*.options.*.required' => 'Answer option cannot be empty.',
    ];

    public function addQuestion(): void
    {
        $this->questions[] = ['text' => '', 'options' => ['Yes', 'No']];
    }

    public function removeQuestion(int $index): void
    {
        if (count($this->questions) > 1) {
            unset($this->questions[$index]);
            $this->questions = array_values($this->questions);
        }
    }

    public function addOption(int $questionIndex): void
    {
        $this->questions[$questionIndex]['options'][] = '';
    }

    public function removeOption(int $questionIndex, int $optionIndex): void
    {
        if (count($this->questions[$questionIndex]['options']) > 2) {
            unset($this->questions[$questionIndex]['options'][$optionIndex]);
            $this->questions[$questionIndex]['options'] = array_values($this->questions[$questionIndex]['options']);
        }
    }

    public function createSession(): void
    {
        $this->validate();

        $session = TierTalkSession::create([
            'title' => $this->title ?: 'TierTalk Session',
            'max_participants' => $this->maxParticipants,
            'expires_at' => now()->addHours($this->expirationHours),
        ]);

        foreach ($this->questions as $index => $question) {
            $session->questions()->create([
                'question_text' => $question['text'],
                'answer_options' => array_values(array_filter($question['options'])),
                'order' => $index,
            ]);
        }

        $this->redirect(route('session.host', [
            'slug' => $session->slug,
            'token' => $session->host_token
        ]));
    }

    public function render()
    {
        return view('livewire.create-session');
    }
}
