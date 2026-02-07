<?php

use App\Events\ParticipantJoined;
use App\Livewire\JoinSession;
use App\Models\Participant;
use App\Models\TierTalkSession;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

function createActiveSession(array $attributes = []): TierTalkSession
{
    return TierTalkSession::create(array_merge([
        'title' => 'Test Session',
        'max_participants' => 10,
        'expires_at' => now()->addHours(2),
    ], $attributes));
}

it('renders the join session component', function () {
    $session = createActiveSession();

    Livewire::test(JoinSession::class, ['session' => $session])
        ->assertStatus(200);
});

it('can join session with valid username', function () {
    Event::fake([ParticipantJoined::class]);
    $session = createActiveSession();

    Livewire::test(JoinSession::class, ['session' => $session])
        ->set('username', 'TestUser')
        ->call('join')
        ->assertRedirect(route('session.vote', $session->slug));

    expect(Participant::where('tier_talk_session_id', $session->id)->where('username', 'TestUser')->exists())->toBeTrue();
    Event::assertDispatched(ParticipantJoined::class);
});

it('stores participant token in session', function () {
    $session = createActiveSession();

    Livewire::test(JoinSession::class, ['session' => $session])
        ->set('username', 'TestUser')
        ->call('join');

    $participant = Participant::where('username', 'TestUser')->first();
    expect(session('participant_token'))->toBe($participant->token);
});

it('fails validation with short username', function () {
    $session = createActiveSession();

    Livewire::test(JoinSession::class, ['session' => $session])
        ->set('username', 'A')
        ->call('join')
        ->assertHasErrors(['username']);
});

it('fails validation with empty username', function () {
    $session = createActiveSession();

    Livewire::test(JoinSession::class, ['session' => $session])
        ->set('username', '')
        ->call('join')
        ->assertHasErrors(['username']);
});

it('cannot join with duplicate username', function () {
    $session = createActiveSession();
    $session->participants()->create(['username' => 'ExistingUser']);

    Livewire::test(JoinSession::class, ['session' => $session])
        ->set('username', 'ExistingUser')
        ->call('join')
        ->assertHasErrors(['username']);

    expect($session->participants()->where('username', 'ExistingUser')->count())->toBe(1);
});

it('cannot join expired session', function () {
    $session = createActiveSession([
        'expires_at' => now()->subHour(),
    ]);

    Livewire::test(JoinSession::class, ['session' => $session])
        ->assertSet('sessionExpired', true)
        ->set('username', 'TestUser')
        ->call('join')
        ->assertNoRedirect();

    expect(Participant::where('username', 'TestUser')->exists())->toBeFalse();
});

it('cannot join ended session', function () {
    $session = createActiveSession([
        'status' => 'ended',
    ]);

    Livewire::test(JoinSession::class, ['session' => $session])
        ->assertSet('sessionExpired', true)
        ->set('username', 'TestUser')
        ->call('join')
        ->assertNoRedirect();
});

it('cannot join full session', function () {
    $session = createActiveSession([
        'max_participants' => 2,
    ]);

    $session->participants()->create(['username' => 'User1']);
    $session->participants()->create(['username' => 'User2']);

    Livewire::test(JoinSession::class, ['session' => $session])
        ->assertSet('sessionFull', true)
        ->set('username', 'User3')
        ->call('join')
        ->assertNoRedirect();

    expect(Participant::where('username', 'User3')->exists())->toBeFalse();
});

it('updates session full flag when capacity reached during join', function () {
    $session = createActiveSession([
        'max_participants' => 1,
    ]);

    Livewire::test(JoinSession::class, ['session' => $session])
        ->assertSet('sessionFull', false)
        ->set('username', 'User1')
        ->call('join')
        ->assertRedirect();

    Livewire::test(JoinSession::class, ['session' => $session])
        ->assertSet('sessionFull', true);
});

it('gives participant unique token', function () {
    $session = createActiveSession();

    Livewire::test(JoinSession::class, ['session' => $session])
        ->set('username', 'User1')
        ->call('join');

    Livewire::test(JoinSession::class, ['session' => $session])
        ->set('username', 'User2')
        ->call('join');

    $participants = Participant::all();
    expect($participants)->toHaveCount(2)
        ->and($participants[0]->token)->not->toBe($participants[1]->token)
        ->and(strlen($participants[0]->token))->toBe(64);
});
