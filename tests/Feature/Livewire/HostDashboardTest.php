<?php

use App\Events\ParticipantKicked;
use App\Events\QuestionAdded;
use App\Events\QuestionReset;
use App\Events\SessionEnded;
use App\Events\VoteCast;
use App\Livewire\HostDashboard;
use App\Models\Participant;
use App\Models\Question;
use App\Models\TierTalkSession;
use App\Models\Vote;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

function createHostSession(array $attributes = []): TierTalkSession
{
    return TierTalkSession::create(array_merge([
        'title' => 'Test Session',
        'max_participants' => 10,
        'expires_at' => now()->addHours(2),
    ], $attributes));
}

it('renders the host dashboard', function () {
    $session = createHostSession();

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->assertStatus(200)
        ->assertSee('Test Session');
});

it('creates host participant on mount', function () {
    $session = createHostSession();

    Livewire::test(HostDashboard::class, ['session' => $session]);

    expect(Participant::where('tier_talk_session_id', $session->id)->where('username', '🎯 Host')->exists())->toBeTrue();
});

it('reuses host participant on subsequent mounts', function () {
    $session = createHostSession();

    Livewire::test(HostDashboard::class, ['session' => $session]);
    Livewire::test(HostDashboard::class, ['session' => $session]);

    expect(Participant::where('username', '🎯 Host')->count())->toBe(1);
});

it('can add question', function () {
    Event::fake([QuestionAdded::class]);
    $session = createHostSession();

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->set('newQuestion', 'What is your favorite food?')
        ->set('newOptions', ['Pizza', 'Burger', 'Sushi'])
        ->call('addQuestion');

    expect(Question::where('tier_talk_session_id', $session->id)->where('question_text', 'What is your favorite food?')->exists())->toBeTrue();

    $question = Question::where('question_text', 'What is your favorite food?')->first();
    expect($question->answer_options)->toBe(['Pizza', 'Burger', 'Sushi']);

    Event::assertDispatched(QuestionAdded::class);
});

it('resets new question form after adding', function () {
    $session = createHostSession();

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->set('newQuestion', 'Test question?')
        ->set('newOptions', ['A', 'B'])
        ->call('addQuestion')
        ->assertSet('newQuestion', '')
        ->assertSet('newOptions', ['Yes', 'No']);
});

it('fails validation with short question', function () {
    $session = createHostSession();

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->set('newQuestion', 'ab')
        ->call('addQuestion')
        ->assertHasErrors(['newQuestion']);
});

it('fails validation with less than two options', function () {
    $session = createHostSession();

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->set('newQuestion', 'Valid question?')
        ->set('newOptions', ['Only one'])
        ->call('addQuestion')
        ->assertHasErrors(['newOptions']);
});

it('can add new option', function () {
    $session = createHostSession();

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->assertCount('newOptions', 2)
        ->call('addNewOption')
        ->assertCount('newOptions', 3);
});

it('can remove option', function () {
    $session = createHostSession();

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->call('addNewOption')
        ->assertCount('newOptions', 3)
        ->call('removeNewOption', 0)
        ->assertCount('newOptions', 2);
});

it('cannot remove option when only two remain', function () {
    $session = createHostSession();

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->assertCount('newOptions', 2)
        ->call('removeNewOption', 0)
        ->assertCount('newOptions', 2);
});

it('can reset question votes', function () {
    Event::fake([QuestionReset::class]);
    $session = createHostSession();
    $question = $session->questions()->create([
        'question_text' => 'Test?',
        'answer_options' => ['Yes', 'No'],
        'order' => 0,
    ]);
    $participant = $session->participants()->create(['username' => 'User1']);
    Vote::create([
        'question_id' => $question->id,
        'participant_id' => $participant->id,
        'vote_value' => 'Yes',
    ]);

    expect($question->votes()->count())->toBe(1);

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->call('resetQuestion', $question->id);

    expect($question->fresh()->votes()->count())->toBe(0);
    Event::assertDispatched(QuestionReset::class);
});

it('can toggle question active status', function () {
    $session = createHostSession();
    $question = $session->questions()->create([
        'question_text' => 'Test?',
        'answer_options' => ['Yes', 'No'],
        'is_active' => true,
        'order' => 0,
    ]);

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->call('toggleQuestion', $question->id);

    expect($question->fresh()->is_active)->toBeFalse();

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->call('toggleQuestion', $question->id);

    expect($question->fresh()->is_active)->toBeTrue();
});

it('can delete question', function () {
    $session = createHostSession();
    $question = $session->questions()->create([
        'question_text' => 'Test?',
        'answer_options' => ['Yes', 'No'],
        'order' => 0,
    ]);

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->call('deleteQuestion', $question->id);

    expect(Question::find($question->id))->toBeNull();
});

it('can kick participant', function () {
    Event::fake([ParticipantKicked::class]);
    $session = createHostSession();
    $participant = $session->participants()->create(['username' => 'BadUser']);
    $question = $session->questions()->create([
        'question_text' => 'Test?',
        'answer_options' => ['Yes', 'No'],
        'order' => 0,
    ]);
    Vote::create([
        'question_id' => $question->id,
        'participant_id' => $participant->id,
        'vote_value' => 'Yes',
    ]);

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->call('kickParticipant', $participant->id);

    expect(Participant::find($participant->id))->toBeNull();
    expect(Vote::where('participant_id', $participant->id)->exists())->toBeFalse();
    Event::assertDispatched(ParticipantKicked::class);
});

it('can end session', function () {
    Event::fake([SessionEnded::class]);
    $session = createHostSession();

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->call('endSession')
        ->assertRedirect(route('home'));

    expect($session->fresh()->status)->toBe('ended');
    Event::assertDispatched(SessionEnded::class);
});

it('allows host to vote on question', function () {
    Event::fake([VoteCast::class]);
    $session = createHostSession();
    $question = $session->questions()->create([
        'question_text' => 'Test?',
        'answer_options' => ['Yes', 'No'],
        'order' => 0,
    ]);

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->call('vote', $question->id, 'Yes');

    $hostParticipant = Participant::where('username', '🎯 Host')->first();
    expect(Vote::where('question_id', $question->id)
        ->where('participant_id', $hostParticipant->id)
        ->where('vote_value', 'Yes')
        ->exists())->toBeTrue();

    Event::assertDispatched(VoteCast::class);
});

it('prevents host from voting twice on same question', function () {
    $session = createHostSession();
    $question = $session->questions()->create([
        'question_text' => 'Test?',
        'answer_options' => ['Yes', 'No'],
        'order' => 0,
    ]);

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->call('vote', $question->id, 'Yes')
        ->call('vote', $question->id, 'No');

    $hostParticipant = Participant::where('username', '🎯 Host')->first();
    expect(Vote::where('participant_id', $hostParticipant->id)->count())->toBe(1)
        ->and(Vote::where('participant_id', $hostParticipant->id)->first()->vote_value)->toBe('Yes');
});

it('prevents host from voting with invalid option', function () {
    $session = createHostSession();
    $question = $session->questions()->create([
        'question_text' => 'Test?',
        'answer_options' => ['Yes', 'No'],
        'order' => 0,
    ]);

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->call('vote', $question->id, 'InvalidOption');

    $hostParticipant = Participant::where('username', '🎯 Host')->first();
    expect(Vote::where('participant_id', $hostParticipant->id)->exists())->toBeFalse();
});

it('prevents host from voting on inactive question', function () {
    $session = createHostSession();
    $question = $session->questions()->create([
        'question_text' => 'Test?',
        'answer_options' => ['Yes', 'No'],
        'is_active' => false,
        'order' => 0,
    ]);

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->call('vote', $question->id, 'Yes');

    $hostParticipant = Participant::where('username', '🎯 Host')->first();
    expect(Vote::where('participant_id', $hostParticipant->id)->exists())->toBeFalse();
});

it('displays share url', function () {
    $session = createHostSession();

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->assertSee($session->share_url);
});

it('displays participant count', function () {
    $session = createHostSession(['max_participants' => 20]);
    $session->participants()->create(['username' => 'User1']);
    $session->participants()->create(['username' => 'User2']);

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->assertSee('3/20 Participants');
});

it('orders questions correctly', function () {
    $session = createHostSession();

    $session->questions()->create([
        'question_text' => 'First?',
        'answer_options' => ['A', 'B'],
        'order' => 0,
    ]);

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->set('newQuestion', 'Second?')
        ->set('newOptions', ['C', 'D'])
        ->call('addQuestion');

    $questions = $session->fresh()->questions;
    expect($questions[0]->question_text)->toBe('First?')
        ->and($questions[0]->order)->toBe(0)
        ->and($questions[1]->question_text)->toBe('Second?')
        ->and($questions[1]->order)->toBe(1);
});
