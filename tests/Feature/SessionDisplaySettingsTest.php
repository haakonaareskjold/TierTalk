<?php

use App\Livewire\HostDashboard;
use App\Models\TierTalkSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('has default display settings on session creation', function () {
    $session = TierTalkSession::create([
        'title' => 'Test Session',
        'max_participants' => 10,
        'expires_at' => now()->addDay(),
    ]);

    expect($session->show_average_to_all)->toBeTrue();
    expect($session->show_hover_to_all)->toBeFalse();
});

it('can toggle show_average_to_all setting', function () {
    $session = TierTalkSession::create([
        'title' => 'Test Session',
        'max_participants' => 10,
        'expires_at' => now()->addDay(),
    ]);

    expect($session->show_average_to_all)->toBeTrue();

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->call('toggleShowAverageToAll');

    expect($session->fresh()->show_average_to_all)->toBeFalse();

    Livewire::test(HostDashboard::class, ['session' => $session->fresh()])
        ->call('toggleShowAverageToAll');

    expect($session->fresh()->show_average_to_all)->toBeTrue();
});

it('can toggle show_hover_to_all setting', function () {
    $session = TierTalkSession::create([
        'title' => 'Test Session',
        'max_participants' => 10,
        'expires_at' => now()->addDay(),
    ]);

    expect($session->show_hover_to_all)->toBeFalse();

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->call('toggleShowHoverToAll');

    expect($session->fresh()->show_hover_to_all)->toBeTrue();

    Livewire::test(HostDashboard::class, ['session' => $session->fresh()])
        ->call('toggleShowHoverToAll');

    expect($session->fresh()->show_hover_to_all)->toBeFalse();
});

it('displays display settings controls in host dashboard', function () {
    $session = TierTalkSession::create([
        'title' => 'Test Session',
        'max_participants' => 10,
        'expires_at' => now()->addDay(),
    ]);

    Livewire::test(HostDashboard::class, ['session' => $session])
        ->assertSee('Display Settings for Participants')
        ->assertSee('Show average to all participants')
        ->assertSee('Show voter names on hover to all');
});
