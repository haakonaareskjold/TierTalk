<?php

use App\Events\SessionUpdated;
use App\Livewire\HostDashboard;
use App\Models\TierTalkSession;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

it('has default delayed actions enabled on session creation', function () {
    $session = TierTalkSession::create([
        'title' => 'Test Session',
        'username' => 'John Doe',
        'max_participants' => 10,
        'expires_at' => now()->addHours(2),
    ]);

    expect($session->use_delayed_actions)->toBeTrue();
});

it('can toggle use_delayed_actions setting', function () {
    Event::fake([SessionUpdated::class]);
    $session = TierTalkSession::create([
        'title' => 'Test Session',
        'username' => 'John Doe',
        'max_participants' => 10,
        'expires_at' => now()->addHours(2),
    ]);

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->call('toggleUseDelayedActions');

    expect($session->fresh()->use_delayed_actions)->toBeFalse();
    Event::assertDispatched(SessionUpdated::class);

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->call('toggleUseDelayedActions');

    expect($session->fresh()->use_delayed_actions)->toBeTrue();
    Event::assertDispatched(SessionUpdated::class);
});

it('displays delayed actions control in host dashboard', function () {
    $session = TierTalkSession::create([
        'title' => 'Test Session',
        'username' => 'John Doe',
        'max_participants' => 10,
        'expires_at' => now()->addHours(2),
    ]);

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->assertSee('Use 5s delay with Undo');
});
