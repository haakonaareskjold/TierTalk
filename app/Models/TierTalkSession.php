<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $host_token
 * @property string $slug
 * @property string|null $title
 * @property int $max_participants
 * @property string $status
 * @property \Illuminate\Support\Carbon $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $show_average_to_all
 * @property bool $show_hover_to_all
 * @property string|null $username
 * @property-read string $share_url
 * @property-read string $host_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Question> $questions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Participant> $participants
 */
class TierTalkSession extends Model
{
    use MassPrunable;

    protected $fillable = [
        'host_token',
        'slug',
        'title',
        'username',
        'max_participants',
        'status',
        'expires_at',
        'show_average_to_all',
        'show_hover_to_all',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'max_participants' => 'integer',
        'show_average_to_all' => 'boolean',
        'show_hover_to_all' => 'boolean',
    ];

    protected $attributes = [
        'show_average_to_all' => true,
        'show_hover_to_all' => false,
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

    /**
     * @return HasMany<Question, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    /**
     * @return HasMany<Participant, $this>
     */
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
        return ! $this->isExpired();
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

    /**
     * Get the prunable model query.
     *
     * @return \Illuminate\Database\Eloquent\Builder<self>
     */
    public function prunable(): Builder
    {
        return static::where('expires_at', '<=', now()->minus(days: 1));
    }
}
