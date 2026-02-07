<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TierTalkSession extends Model
{
    protected $fillable = [
        'host_token',
        'slug',
        'title',
        'max_participants',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'max_participants' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (TierTalkSession $session) {
            if (empty($session->host_token)) {
                $session->host_token = Str::random(64);
            }
            if (empty($session->slug)) {
                $session->slug = Str::random(12);
            }
        });
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast() || $this->status === 'ended';
    }

    public function isActive(): bool
    {
        return !$this->isExpired();
    }

    public function canAcceptParticipants(): bool
    {
        return $this->isActive() && $this->participants()->count() < $this->max_participants;
    }

    public function getShareUrlAttribute(): string
    {
        return route('session.join', $this->slug);
    }

    public function getHostUrlAttribute(): string
    {
        return route('session.host', ['slug' => $this->slug, 'token' => $this->host_token]);
    }
}
