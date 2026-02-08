<?php

use App\Events\VoteCast;
use App\Livewire\VotingInterface;
use App\Models\Participant;
use App\Models\TierTalkSession;
use App\Models\Vote;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

function createSessionWithQuestion(array $sessionAttrs = [], array $questionAttrs = []): array
{
    $session = TierTalkSession::create(array_merge([
        'title' => 'Test Session',
        'max_participants' => 10,
        'expires_at' => now()->addHours(2),
    ], $sessionAttrs));

    $question = $session->questions()->create(array_merge([
        'question_text' => 'Test Question?',
        'answer_options' => ['Yes', 'No', 'Maybe'],
        'order' => 0,
    ], $questionAttrs));

    return [$session, $question];
}

function createParticipant(TierTalkSession $session, string $username = 'TestUser'): Participant
{
    return $session->participants()->create(['username' => $username]);
}

it('renders the voting interface', function () {
    [$session] = createSessionWithQuestion();
    $participant = createParticipant($session);

    session(['participant_token' => $participant->token]);

    Livewire::test(VotingInterface::class, ['session' => $session])
        ->assertStatus(200)
        ->assertSee('Test Question?');
});

it('shows not joined message without participant token', function () {
    [$session] = createSessionWithQuestion();

    Livewire::test(VotingInterface::class, ['session' => $session])
        ->assertSee('Not Joined')
        ->assertSee('You need to join this session first');
});

it('can cast vote', function () {
    Event::fake([VoteCast::class]);
    [$session, $question] = createSessionWithQuestion();
    $participant = createParticipant($session);

    session(['participant_token' => $participant->token]);

    Livewire::test(VotingInterface::class, ['session' => $session])
        ->call('vote', $question->id, 'Yes');

    expect(Vote::where('question_id', $question->id)
        ->where('participant_id', $participant->id)
        ->where('vote_value', 'Yes')
        ->exists())->toBeTrue();

    Event::assertDispatched(VoteCast::class);
});

it('cannot vote twice on same question', function () {
    [$session, $question] = createSessionWithQuestion();
    $participant = createParticipant($session);

    Vote::create([
        'question_id' => $question->id,
        'participant_id' => $participant->id,
        'vote_value' => 'Yes',
    ]);

    session(['participant_token' => $participant->token]);

    Livewire::test(VotingInterface::class, ['session' => $session])
        ->call('vote', $question->id, 'No');

    expect(Vote::where('question_id', $question->id)->count())->toBe(1)
        ->and(Vote::where('question_id', $question->id)->first()->vote_value)->toBe('Yes');
});

it('cannot vote with invalid option', function () {
    [$session, $question] = createSessionWithQuestion();
    $participant = createParticipant($session);

    session(['participant_token' => $participant->token]);

    Livewire::test(VotingInterface::class, ['session' => $session])
        ->call('vote', $question->id, 'InvalidOption');

    expect(Vote::where('question_id', $question->id)->where('participant_id', $participant->id)->exists())->toBeFalse();
});

it('cannot vote on inactive question', function () {
    [$session, $question] = createSessionWithQuestion([], ['is_active' => false]);
    $participant = createParticipant($session);

    session(['participant_token' => $participant->token]);

    Livewire::test(VotingInterface::class, ['session' => $session])
        ->call('vote', $question->id, 'Yes');

    expect(Vote::where('question_id', $question->id)->exists())->toBeFalse();
});

it('cannot vote on expired session', function () {
    [$session, $question] = createSessionWithQuestion([
        'expires_at' => now()->subHour(),
    ]);
    $participant = createParticipant($session);

    session(['participant_token' => $participant->token]);

    Livewire::test(VotingInterface::class, ['session' => $session])
        ->assertSet('sessionEnded', true)
        ->call('vote', $question->id, 'Yes');

    expect(Vote::where('question_id', $question->id)->exists())->toBeFalse();
});

it('shows session ended message for expired session', function () {
    [$session] = createSessionWithQuestion([
        'expires_at' => now()->subHour(),
    ]);

    Livewire::test(VotingInterface::class, ['session' => $session])
        ->assertSee('Session Ended')
        ->assertSee('Thank you for participating');
});

it('shows vote submitted after voting', function () {
    [$session, $question] = createSessionWithQuestion();
    $participant = createParticipant($session);

    Vote::create([
        'question_id' => $question->id,
        'participant_id' => $participant->id,
        'vote_value' => 'Yes',
    ]);

    session(['participant_token' => $participant->token]);

    Livewire::test(VotingInterface::class, ['session' => $session])
        ->assertSee('Vote submitted!');
});

it('shows live results after voting', function () {
    [$session, $question] = createSessionWithQuestion();
    $participant1 = createParticipant($session, 'User1');
    $participant2 = createParticipant($session, 'User2');

    Vote::create([
        'question_id' => $question->id,
        'participant_id' => $participant1->id,
        'vote_value' => 'Yes',
    ]);
    Vote::create([
        'question_id' => $question->id,
        'participant_id' => $participant2->id,
        'vote_value' => 'No',
    ]);

    session(['participant_token' => $participant1->token]);

    Livewire::test(VotingInterface::class, ['session' => $session])
        ->assertSee('Live Results')
        ->assertSee('2 votes');
});

it('displays custom answer options', function () {
    [$session, $question] = createSessionWithQuestion([], [
        'answer_options' => ['Messi', 'Ronaldo'],
    ]);
    $participant = createParticipant($session);

    session(['participant_token' => $participant->token]);

    Livewire::test(VotingInterface::class, ['session' => $session])
        ->assertSee('Messi')
        ->assertSee('Ronaldo');
});

it('only shows active questions', function () {
    $session = TierTalkSession::create([
        'title' => 'Test Session',
        'username' => 'John Doe',
        'max_participants' => 10,
        'expires_at' => now()->addHours(2),
    ]);

    $session->questions()->create([
        'question_text' => 'Active Question?',
        'answer_options' => ['Yes', 'No'],
        'is_active' => true,
        'order' => 0,
    ]);

    $session->questions()->create([
        'question_text' => 'Inactive Question?',
        'answer_options' => ['Yes', 'No'],
        'is_active' => false,
        'order' => 1,
    ]);

    $participant = createParticipant($session);

    session(['participant_token' => $participant->token]);

    Livewire::test(VotingInterface::class, ['session' => $session])
        ->assertSee('Active Question?')
        ->assertDontSee('Inactive Question?');
});

it('shows waiting message when no questions', function () {
    $session = TierTalkSession::create([
        'title' => 'Test Session',
        'username' => 'John Doe',
        'max_participants' => 10,
        'expires_at' => now()->addHours(2),
    ]);
    $participant = createParticipant($session);

    session(['participant_token' => $participant->token]);

    Livewire::test(VotingInterface::class, ['session' => $session])
        ->assertSee('Waiting for questions from the host');
});

it('allows participant to vote on multiple questions', function () {
    $session = TierTalkSession::create([
        'title' => 'Test Session',
        'username' => 'John Doe',
        'max_participants' => 10,
        'expires_at' => now()->addHours(2),
    ]);

    $question1 = $session->questions()->create([
        'question_text' => 'Question 1?',
        'answer_options' => ['A', 'B'],
        'order' => 0,
    ]);

    $question2 = $session->questions()->create([
        'question_text' => 'Question 2?',
        'answer_options' => ['C', 'D'],
        'order' => 1,
    ]);

    $participant = createParticipant($session);

    session(['participant_token' => $participant->token]);

    Livewire::test(VotingInterface::class, ['session' => $session])
        ->call('vote', $question1->id, 'A')
        ->call('vote', $question2->id, 'D');

    expect(Vote::where('participant_id', $participant->id)->count())->toBe(2);
    expect(Vote::where('question_id', $question1->id)->where('vote_value', 'A')->exists())->toBeTrue();
    expect(Vote::where('question_id', $question2->id)->where('vote_value', 'D')->exists())->toBeTrue();
});
