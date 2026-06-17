<?php

namespace App\Models\Trading;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    protected $table = 'trading_assets';

    protected $fillable = [
        'symbol', 'name', 'asset_class', 'payout_percent',
        'min_stake', 'max_stake', 'allowed_expiries', 'supports_live',
        'live_symbol', 'sim_start_price', 'sim_drift', 'sim_volatility',
        'sim_seed', 'enabled',
        'description', 'icon_url', 'display_order', 'is_featured',
        'category', 'difficulty', 'tags',
    ];

    protected $casts = [
        'allowed_expiries' => 'array',
        'supports_live' => 'boolean',
        'enabled' => 'boolean',
        'is_featured' => 'boolean',
        'payout_percent' => 'decimal:2',
        'sim_start_price' => 'decimal:8',
        'sim_drift' => 'decimal:5',
        'sim_volatility' => 'decimal:5',
        'tags' => 'array',
    ];

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }
}
