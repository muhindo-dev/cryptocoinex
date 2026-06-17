<?php

namespace App\Models\Trading;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class WalletEntry extends Model
{
    use LogsActivity;

    public $timestamps = false;

    public $updatable = false;

    protected $table = 'trading_wallet_entries';

    protected $fillable = [
        'wallet_id', 'trade_id', 'type', 'amount', 'balance_after', 'meta', 'created_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'balance_after' => 'integer',
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('wallet')
            ->logOnly(['type', 'amount', 'balance_after'])
            ->dontSubmitEmptyLogs();
    }
}
