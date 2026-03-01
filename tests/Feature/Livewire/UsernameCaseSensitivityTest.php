<?php

use App\Livewire\JoinSession;
use App\Models\TierTalkSession;
use App\Models\Participant;
use Livewire\Livewire;

it('cannot join with duplicate username regardless of case', function () {
    $session = TierTalkSession::create([
        'title' => 'Test Session',
        'max_participants' => 10,
        'expires_at' => now()->addHours(2),
    ]);

    // Create an existing participant
    $session->participants()->create(['username' => 'OriginalUser']);

    // Attempt to join with same username but different case
    Livewire::test(JoinSession::class, ['session' => $session])
        ->set('username', 'originaluser')
        ->call('join')
        ->assertHasErrors(['username']);

    expect($session->participants()->count())->toBe(1);
});

it('cannot join with username that matches host username regardless of case', function () {
    $session = TierTalkSession::create([
        'title' => 'Test Session',
        'username' => 'TheHost',
        'max_participants' => 10,
        'expires_at' => now()->addHours(2),
    ]);

    // Attempt to join with same username as host but different case
    Livewire::test(JoinSession::class, ['session' => $session])
        ->set('username', 'thehost')
        ->call('join')
        ->assertHasErrors(['username']);

    expect($session->participants()->count())->toBe(0);
});

it('host dashboard ensures host participant exists case-insensitively', function () {
    $session = TierTalkSession::create([
        'title' => 'Test Session',
        'username' => 'HostUser',
        'max_participants' => 10,
        'expires_at' => now()->addHours(2),
    ]);

    // Create a participant with same name but different case
    $session->participants()->create(['username' => 'hostuser']);

    Livewire::test(\App\Livewire\HostDashboard::class, ['session' => $session])
        ->assertSet('hostParticipant.username', 'hostuser');

    expect($session->participants()->count())->toBe(1);
});
