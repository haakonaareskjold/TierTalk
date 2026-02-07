<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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

    public function tierTalkSession(): BelongsTo
    {
        return $this->belongsTo(TierTalkSession::class);
    }

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
