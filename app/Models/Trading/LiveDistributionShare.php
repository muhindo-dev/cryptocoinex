<?php

namespace App\Models\Trading;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One member's slice of a {@see LiveDistribution} — an immutable record of what
 * they received and why, for follow-up.
 */
class LiveDistributionShare extends Model
{
    public $timestamps = false;

    protected $table = 'live_distribution_shares';

    protected $fillable = [
        'live_distribution_id', 'user_id', 'live_wallet_id',
        'base_balance', 'percentage', 'amount', 'live_transaction_id', 'created_at',
    ];

    protected $casts = [
        'base_balance' => 'integer',
        'percentage' => 'decimal:4',
        'amount' => 'integer',
        'created_at' => 'datetime',
    ];

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(LiveDistribution::class, 'live_distribution_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(LiveTransaction::class, 'live_transaction_id');
    }
}
