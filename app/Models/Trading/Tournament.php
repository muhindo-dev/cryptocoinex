<?php

namespace App\Models\Trading;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tournament extends Model
{
    protected $table = 'trading_tournaments';

    protected $fillable = [
        'name', 'description', 'asset_id', 'starting_balance',
        'starts_at', 'ends_at', 'status', 'winner_user_id',
    ];

    protected $casts = [
        'starting_balance' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(TournamentParticipant::class);
    }

    /** Live status derived from the clock (the stored status is updated by the finalizer). */
    public function liveStatus(): string
    {
        $now = now();
        if ($this->status === 'ended') {
            return 'ended';
        }
        if ($now->lt($this->starts_at)) {
            return 'upcoming';
        }
        if ($now->gt($this->ends_at)) {
            return 'ended';
        }

        return 'active';
    }

    public function isJoinable(): bool
    {
        return in_array($this->liveStatus(), ['upcoming', 'active'], true);
    }
}
