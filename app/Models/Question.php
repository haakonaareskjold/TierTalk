<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'tier_talk_session_id',
        'question_text',
        'answer_options',
        'is_active',
        'order',
    ];

    protected $casts = [
        'answer_options' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get default answer options if none are set.
     *
     * @return array<int, string>
     */
    public function getAnswerChoicesAttribute(): array
    {
        return $this->answer_options ?? ['1', '2', '3', '4', '5'];
    }

    public function tierTalkSession(): BelongsTo
    {
        return $this->belongsTo(TierTalkSession::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function getVoteCountsAttribute(): array
    {
        return $this->votes()
            ->selectRaw('vote_value, COUNT(*) as count')
            ->groupBy('vote_value')
            ->pluck('count', 'vote_value')
            ->toArray();
    }

    public function hasVoteFrom(Participant $participant): bool
    {
        return $this->votes()->where('participant_id', $participant->id)->exists();
    }

    public function getVoteFrom(Participant $participant): ?Vote
    {
        return $this->votes()->where('participant_id', $participant->id)->first();
    }

    public function resetVotes(): void
    {
        $this->votes()->delete();
    }

    /**
     * Check if all answer options are numeric.
     */
    public function hasNumericOptions(): bool
    {
        foreach ($this->answer_choices as $option) {
            if (! is_numeric($option)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the average of numeric votes.
     */
    public function getVoteAverageAttribute(): ?float
    {
        if (! $this->hasNumericOptions()) {
            return null;
        }

        $votes = $this->votes;

        if ($votes->isEmpty()) {
            return null;
        }

        $sum = $votes->sum(fn ($vote) => (float) $vote->vote_value);

        return round($sum / $votes->count(), 2);
    }

    /**
     * Get votes grouped by value with participant information.
     *
     * @return array<string, array<int, string>>
     */
    public function getVotersByOptionAttribute(): array
    {
        return $this->votes
            ->load('participant')
            ->groupBy('vote_value')
            ->map(fn ($votes) => $votes->pluck('participant.username')->toArray())
            ->toArray();
    }
}
