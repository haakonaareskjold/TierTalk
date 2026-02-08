<?php

use App\Models\TierTalkSession;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('identifies numeric answer options correctly', function () {
    $session = TierTalkSession::create([
        'title' => 'Test Session',
        'max_participants' => 10,
        'expires_at' => now()->addDay(),
    ]);

    $numericQuestion = $session->questions()->create([
        'question_text' => 'Rate this (1-5)',
        'answer_options' => ['1', '2', '3', '4', '5'],
        'order' => 0,
    ]);

    $nonNumericQuestion = $session->questions()->create([
        'question_text' => 'Yes or No?',
        'answer_options' => ['Yes', 'No'],
        'order' => 1,
    ]);

    $mixedQuestion = $session->questions()->create([
        'question_text' => 'Mixed options',
        'answer_options' => ['1', '2', 'Other'],
        'order' => 2,
    ]);

    expect($numericQuestion->hasNumericOptions())->toBeTrue();
    expect($nonNumericQuestion->hasNumericOptions())->toBeFalse();
    expect($mixedQuestion->hasNumericOptions())->toBeFalse();
});

it('calculates average for numeric votes', function () {
    $session = TierTalkSession::create([
        'title' => 'Test Session',
        'max_participants' => 10,
        'expires_at' => now()->addDay(),
    ]);

    $question = $session->questions()->create([
        'question_text' => 'Rate this (1-5)',
        'answer_options' => ['1', '2', '3', '4', '5'],
        'order' => 0,
    ]);

    $participant1 = $session->participants()->create(['username' => 'Alice']);
    $participant2 = $session->participants()->create(['username' => 'Bob']);
    $participant3 = $session->participants()->create(['username' => 'Charlie']);

    Vote::create(['question_id' => $question->id, 'participant_id' => $participant1->id, 'vote_value' => '5']);
    Vote::create(['question_id' => $question->id, 'participant_id' => $participant2->id, 'vote_value' => '3']);
    Vote::create(['question_id' => $question->id, 'participant_id' => $participant3->id, 'vote_value' => '4']);

    // (5 + 3 + 4) / 3 = 4
    expect($question->fresh()->vote_average)->toBe(4.0);
});

it('returns null average for non-numeric options', function () {
    $session = TierTalkSession::create([
        'title' => 'Test Session',
        'max_participants' => 10,
        'expires_at' => now()->addDay(),
    ]);

    $question = $session->questions()->create([
        'question_text' => 'Yes or No?',
        'answer_options' => ['Yes', 'No'],
        'order' => 0,
    ]);

    $participant = $session->participants()->create(['username' => 'Alice']);

    Vote::create(['question_id' => $question->id, 'participant_id' => $participant->id, 'vote_value' => 'Yes']);

    expect($question->fresh()->vote_average)->toBeNull();
});

it('returns null average when no votes exist', function () {
    $session = TierTalkSession::create([
        'title' => 'Test Session',
        'max_participants' => 10,
        'expires_at' => now()->addDay(),
    ]);

    $question = $session->questions()->create([
        'question_text' => 'Rate this (1-5)',
        'answer_options' => ['1', '2', '3', '4', '5'],
        'order' => 0,
    ]);

    expect($question->vote_average)->toBeNull();
});

it('groups voters by option', function () {
    $session = TierTalkSession::create([
        'title' => 'Test Session',
        'max_participants' => 10,
        'expires_at' => now()->addDay(),
    ]);

    $question = $session->questions()->create([
        'question_text' => 'Pick one',
        'answer_options' => ['A', 'B', 'C'],
        'order' => 0,
    ]);

    $alice = $session->participants()->create(['username' => 'Alice']);
    $bob = $session->participants()->create(['username' => 'Bob']);
    $charlie = $session->participants()->create(['username' => 'Charlie']);

    Vote::create(['question_id' => $question->id, 'participant_id' => $alice->id, 'vote_value' => 'A']);
    Vote::create(['question_id' => $question->id, 'participant_id' => $bob->id, 'vote_value' => 'A']);
    Vote::create(['question_id' => $question->id, 'participant_id' => $charlie->id, 'vote_value' => 'B']);

    $votersByOption = $question->fresh()->voters_by_option;

    expect($votersByOption)->toHaveKey('A');
    expect($votersByOption)->toHaveKey('B');
    expect($votersByOption['A'])->toContain('Alice', 'Bob');
    expect($votersByOption['B'])->toContain('Charlie');
});
