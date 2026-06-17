<?php

namespace App\Models\Trading;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A single profit-distribution run: a pool amount split across live-account
 * holders in proportion to their balance.
 */
class LiveDistribution extends Model
{
    protected $table = 'live_distributions';

    protected $fillable = [
        'total_amount', 'total_base', 'members_count', 'currency', 'note', 'created_by',
    ];

    protected $casts = [
        'total_amount' => 'integer',
        'total_base' => 'integer',
        'members_count' => 'integer',
    ];

    public function shares(): HasMany
    {
        return $this->hasMany(LiveDistributionShare::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
