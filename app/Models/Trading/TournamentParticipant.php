<?php

namespace App\Models\Trading;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentParticipant extends Model
{
    public $timestamps = false;

    protected $table = 'trading_tournament_participants';

    protected $fillable = [
        'tournament_id', 'user_id', 'joined_at', 'final_balance', 'final_pnl', 'final_rank',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'final_balance' => 'integer',
        'final_pnl' => 'integer',
        'final_rank' => 'integer',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
