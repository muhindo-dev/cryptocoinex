<?php

namespace App\Models\Trading;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * One immutable line in the Live Account ledger. {@see amount} is signed:
 * positive credits (deposit, profit), negative debits (withdrawal).
 */
class LiveTransaction extends Model
{
    use LogsActivity;

    public $timestamps = false;

    protected $table = 'live_transactions';

    protected $fillable = [
        'live_wallet_id', 'type', 'amount', 'balance_after',
        'description', 'source_type', 'source_id', 'accrual_date', 'meta', 'created_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'balance_after' => 'integer',
        'accrual_date' => 'date',
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(LiveWallet::class, 'live_wallet_id');
    }

    /** Human label for the transaction type. */
    public function getTitleAttribute(): string
    {
        return match ($this->type) {
            'deposit' => 'Deposit',
            'withdrawal' => 'Withdrawal',
            'profit' => 'Managed-trading return',
            'adjustment' => 'Adjustment',
            default => ucfirst($this->type),
        };
    }

    public function getIsCreditAttribute(): bool
    {
        return $this->amount >= 0;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('live_wallet')
            ->logOnly(['type', 'amount', 'balance_after'])
            ->dontSubmitEmptyLogs();
    }
}
