<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $tier_talk_session_id
 * @property string $username
 * @property string $token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TierTalkSession $tierTalkSession
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Vote> $votes
 */
class Participant extends Model
{
    protected $fillable = [
        'tier_talk_session_id',
        'username',
        'token',
    ];

    protected static function booted(): void
    {
        static::creating(function (Participant $participant) {
            if (empty($participant->token)) {
                $participant->token = Str::random(64);
            }
        });
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

    public function hasVotedOn(Question $question): bool
    {
        return $this->votes()->where('question_id', $question->id)->exists();
    }

    public function getVoteFor(Question $question): ?Vote
    {
        return $this->votes()->where('question_id', $question->id)->first();
    }
}
