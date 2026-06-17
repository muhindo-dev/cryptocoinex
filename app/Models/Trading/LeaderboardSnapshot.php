<?php

namespace App\Models\Trading;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaderboardSnapshot extends Model
{
    public $timestamps = false;

    protected $table = 'trading_leaderboard_snapshots';

    protected $fillable = [
        'user_id', 'period', 'period_date', 'rank', 'trades_count',
        'win_rate', 'net_pnl', 'peak_balance', 'score', 'computed_at',
    ];

    protected $casts = [
        'period_date' => 'date',
        'rank' => 'integer',
        'trades_count' => 'integer',
        'win_rate' => 'decimal:2',
        'net_pnl' => 'integer',
        'peak_balance' => 'integer',
        'score' => 'integer',
        'computed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
