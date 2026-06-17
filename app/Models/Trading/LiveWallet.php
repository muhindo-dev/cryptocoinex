<?php

namespace App\Models\Trading;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A user's real-money Live Account wallet. The {@see balance} column is a cached
 * running total kept in lock-step with the {@see transactions} ledger by
 * {@see \App\Services\Trading\LiveWalletService}.
 */
class LiveWallet extends Model
{
    protected $table = 'live_wallets';

    protected $fillable = [
        'user_id', 'currency', 'balance',
        'total_deposited', 'total_withdrawn', 'total_profit', 'last_accrued_on',
    ];

    protected $casts = [
        'balance' => 'integer',
        'total_deposited' => 'integer',
        'total_withdrawn' => 'integer',
        'total_profit' => 'integer',
        'last_accrued_on' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LiveTransaction::class);
    }
}
