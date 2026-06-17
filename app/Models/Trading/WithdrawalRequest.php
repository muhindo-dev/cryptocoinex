<?php

namespace App\Models\Trading;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalRequest extends Model
{
    protected $table = 'withdrawal_requests';

    protected $fillable = [
        'user_id', 'amount', 'payout_address', 'payout_network', 'payout_phone', 'payout_name', 'note',
        'status', 'reviewed_by', 'admin_note', 'payout_reference', 'reviewed_at', 'live_transaction_id',
    ];

    /** Where the payout is sent — crypto address (preferred) or legacy phone. */
    public function getDestinationAttribute(): string
    {
        return $this->payout_address ?: ($this->payout_phone ?: '—');
    }

    protected $casts = [
        'amount' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(LiveTransaction::class, 'live_transaction_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
