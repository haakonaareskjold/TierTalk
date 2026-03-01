<?php

use App\Livewire\CreateSession;
use App\Models\TierTalkSession;
use Livewire\Livewire;

it('renders the create session component', function () {
    Livewire::test(CreateSession::class)
        ->assertStatus(200)
        ->assertSee('Create a TierTalk Session');
});

it('can create session with valid data', function () {
    Livewire::test(CreateSession::class)
        ->set('title', 'Test Session')
        ->set('maxParticipants', 10)
        ->set('expirationHours', 2)
        ->set('questions', [
            ['text' => 'What is your favorite color?', 'options' => ['Red', 'Blue', 'Green']],
        ])
        ->call('createSession')
        ->assertRedirect();

    expect(TierTalkSession::where('title', 'Test Session')->where('max_participants', 10)->exists())->toBeTrue();
    expect(\App\Models\Question::where('question_text', 'What is your favorite color?')->exists())->toBeTrue();
});

it('creates session with default title when empty', function () {
    Livewire::test(CreateSession::class)
        ->set('title', '')
        ->set('maxParticipants', 20)
        ->set('expirationHours', 1)
        ->set('questions', [
            ['text' => 'Test question?', 'options' => ['Yes', 'No']],
        ])
        ->call('createSession')
        ->assertRedirect();

    expect(TierTalkSession::where('title', 'TierTalk Session')->exists())->toBeTrue();
});

it('fails validation without questions', function () {
    Livewire::test(CreateSession::class)
        ->set('questions', [])
        ->call('createSession')
        ->assertHasErrors(['questions']);
});

it('fails validation with short question text', function () {
    Livewire::test(CreateSession::class)
        ->set('questions', [
            ['text' => 'ab', 'options' => ['Yes', 'No']],
        ])
        ->call('createSession')
        ->assertHasErrors(['questions.0.text']);
});

it('fails validation with less than two options', function () {
    Livewire::test(CreateSession::class)
        ->set('questions', [
            ['text' => 'Valid question?', 'options' => ['Only one']],
        ])
        ->call('createSession')
        ->assertHasErrors(['questions.0.options']);
});

it('fails validation with invalid max participants', function () {
    Livewire::test(CreateSession::class)
        ->set('maxParticipants', 1)
        ->set('questions', [
            ['text' => 'Valid question?', 'options' => ['Yes', 'No']],
        ])
        ->call('createSession')
        ->assertHasErrors(['maxParticipants']);

    Livewire::test(CreateSession::class)
        ->set('maxParticipants', 101)
        ->set('questions', [
            ['text' => 'Valid question?', 'options' => ['Yes', 'No']],
        ])
        ->call('createSession')
        ->assertHasErrors(['maxParticipants']);
});

it('can add question', function () {
    Livewire::test(CreateSession::class)
        ->assertCount('questions', 1)
        ->call('addQuestion')
        ->assertCount('questions', 2);
});

it('can remove question', function () {
    Livewire::test(CreateSession::class)
        ->call('addQuestion')
        ->assertCount('questions', 2)
        ->call('removeQuestion', 0)
        ->assertCount('questions', 1);
});

it('cannot remove last question', function () {
    Livewire::test(CreateSession::class)
        ->assertCount('questions', 1)
        ->call('removeQuestion', 0)
        ->assertCount('questions', 1);
});

it('can add option to question', function () {
    $component = Livewire::test(CreateSession::class);
    $questions = $component->get('questions');
    $initialOptionsCount = count($questions[0]['options']);

    $component->call('addOption', 0);
    $questions = $component->get('questions');

    expect($questions[0]['options'])->toHaveCount($initialOptionsCount + 1);
});

it('can remove option from question', function () {
    Livewire::test(CreateSession::class)
        ->call('addOption', 0)
        ->call('removeOption', 0, 0)
        ->assertSet('questions.0.options', fn ($options) => count($options) === 2);
});

it('cannot remove option when only two remain', function () {
    $component = Livewire::test(CreateSession::class);
    $questions = $component->get('questions');
    expect($questions[0]['options'])->toHaveCount(2);

    $component->call('removeOption', 0, 0);
    $questions = $component->get('questions');

    expect($questions[0]['options'])->toHaveCount(2);
});

it('creates session with unique slug and host token', function () {
    Livewire::test(CreateSession::class)
        ->set('questions', [
            ['text' => 'Test question?', 'options' => ['Yes', 'No']],
        ])
        ->call('createSession');

    $session = TierTalkSession::first();

    expect($session->slug)->not->toBeEmpty()
        ->and($session->host_token)->not->toBeEmpty()
        ->and(strlen($session->slug))->toBe(12)
        ->and(strlen($session->host_token))->toBe(64);
});

it('saves multiple questions with correct order', function () {
    Livewire::test(CreateSession::class)
        ->set('questions', [
            ['text' => 'First question?', 'options' => ['A', 'B']],
            ['text' => 'Second question?', 'options' => ['C', 'D']],
            ['text' => 'Third question?', 'options' => ['E', 'F']],
        ])
        ->call('createSession');

    $session = TierTalkSession::with('questions')->first();

    expect($session->questions)->toHaveCount(3)
        ->and($session->questions[0]->question_text)->toBe('First question?')
        ->and($session->questions[0]->order)->toBe(0)
        ->and($session->questions[1]->question_text)->toBe('Second question?')
        ->and($session->questions[1]->order)->toBe(1)
        ->and($session->questions[2]->question_text)->toBe('Third question?')
        ->and($session->questions[2]->order)->toBe(2);
});

it('saves answer options correctly', function () {
    Livewire::test(CreateSession::class)
        ->set('questions', [
            ['text' => 'Who is better?', 'options' => ['Messi', 'Ronaldo', 'Neither']],
        ])
        ->call('createSession');

    $session = TierTalkSession::with('questions')->first();
    $question = $session->questions->first();

    expect($question->answer_options)->toBe(['Messi', 'Ronaldo', 'Neither']);
});
