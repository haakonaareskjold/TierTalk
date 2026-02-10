<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $tier_talk_session_id
 * @property string $question_text
 * @property bool $is_active
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<int, string>|null $answer_options
 * @property-read array<int, string> $answer_choices
 * @property-read array<string, int> $vote_counts
 * @property-read float|null $vote_average
 * @property-read array<string, array<int, string>> $voters_by_option
 * @property-read \App\Models\TierTalkSession $tierTalkSession
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Vote> $votes
 */
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

    /**
     * @return BelongsTo<TierTalkSession, $this>
     */
    public function tierTalkSession(): BelongsTo
    {
        return $this->belongsTo(TierTalkSession::class);
    }

    /**
     * @return HasMany<Vote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * @return array<string, int>
     */
    public function getVoteCountsAttribute(): array
    {
        /** @var array<string, int> $counts */
        $counts = $this->votes()
            ->selectRaw('vote_value, COUNT(*) as count')
            ->groupBy('vote_value')
            ->pluck('count', 'vote_value')
            ->toArray();

        return $counts;
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
        /** @var array<string, array<int, string>> $voters */
        $voters = $this->votes
            ->load('participant')
            ->groupBy('vote_value')
            ->map(fn ($votes) => $votes->pluck('participant.username')->toArray())
            ->toArray();

        return $voters;
    }
}
