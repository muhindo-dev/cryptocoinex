<?php

namespace App\Models\Trading;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    protected $table = 'trading_wallets';

    protected $fillable = [
        'user_id', 'balance', 'currency_label',
        'peak_balance', 'total_credited', 'total_debited', 'resets_count',
    ];

    protected $casts = [
        'balance' => 'integer',
        'peak_balance' => 'integer',
        'total_credited' => 'integer',
        'total_debited' => 'integer',
        'resets_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(WalletEntry::class);
    }
}
