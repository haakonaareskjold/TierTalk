<?php

use App\Livewire\CreateSession;
use App\Models\TierTalkSession;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/create', CreateSession::class)->name('session.create');

Route::get('/s/{slug}', function (string $slug) {
    $session = TierTalkSession::where('slug', $slug)->firstOrFail();

    return view('session.join', ['session' => $session]);
})->name('session.join');

Route::get('/s/{slug}/vote', function (string $slug) {
    $session = TierTalkSession::where('slug', $slug)->firstOrFail();

    return view('session.vote', ['session' => $session]);
})->name('session.vote');

Route::get('/s/{slug}/host', function (string $slug, \Illuminate\Http\Request $request) {
    $token = $request->query('token');
    $session = TierTalkSession::where('slug', $slug)
        ->where('host_token', $token)
        ->firstOrFail();

    return view('session.host', ['session' => $session]);
})->name('session.host');
