<?php

namespace App\Events\Trading;

use App\Models\Trading\Trade;
use Illuminate\Foundation\Events\Dispatchable;

class TradeSettled
{
    use Dispatchable;

    public function __construct(public readonly Trade $trade) {}
}
