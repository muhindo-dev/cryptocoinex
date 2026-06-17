<?php

namespace App\Models\Trading;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepositRequest extends Model
{
    protected $table = 'deposit_requests';

    protected $fillable = [
        'user_id', 'amount', 'reference', 'payer_phone', 'paid_to', 'note', 'proof_path',
        'status', 'reviewed_by', 'admin_note', 'reviewed_at', 'live_transaction_id',
    ];

    /** Public URL to the uploaded proof-of-payment screenshot, if any. */
    public function getProofUrlAttribute(): ?string
    {
        return $this->proof_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->proof_path) : null;
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
