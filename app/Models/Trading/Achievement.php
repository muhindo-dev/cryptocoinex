<?php

namespace App\Models\Trading;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Achievement extends Model
{
    public $timestamps = false;

    protected $table = 'trading_achievements';

    protected $fillable = [
        'user_id', 'type', 'title', 'description', 'icon', 'meta', 'achieved_at', 'created_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'achieved_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
