<?php

namespace App\Models\Trading;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Trade extends Model
{
    use LogsActivity;

    protected $table = 'trading_trades';

    protected $fillable = [
        'user_id', 'asset_id', 'mode', 'account', 'direction', 'stake', 'payout_percent',
        'entry_price', 'exit_price', 'opened_at', 'expires_at', 'settled_at',
        'expiry_seconds', 'status', 'payout_amount',
        'notes', 'tags', 'sentiment', 'device_type',
    ];

    protected $casts = [
        'stake' => 'integer',
        'payout_amount' => 'integer',
        'entry_price' => 'decimal:8',
        'exit_price' => 'decimal:8',
        'payout_percent' => 'decimal:2',
        'opened_at' => 'datetime',
        'expires_at' => 'datetime',
        'settled_at' => 'datetime',
        'tags' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function walletEntries(): HasMany
    {
        return $this->hasMany(WalletEntry::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('trade')
            ->logOnly(['status', 'direction', 'stake', 'entry_price', 'exit_price', 'payout_amount'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
