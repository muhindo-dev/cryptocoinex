<?php

namespace App\Http\Controllers\Admin\Trading;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller
{
    /**
     * GET /admin/trading/activity — paginated audit trail.
     */
    public function index(Request $request)
    {
        $log = $request->get('log');   // optional filter: trade|wallet

        $activities = Activity::query()
            ->when($log, fn ($q) => $q->where('log_name', $log))
            ->with(['causer', 'subject'])
            ->latest('id')
            ->paginate(40)
            ->withQueryString();

        $logNames = Activity::query()
            ->select('log_name')
            ->distinct()
            ->whereNotNull('log_name')
            ->pluck('log_name');

        return view('admin.trading.activity', compact('activities', 'logNames', 'log'));
    }
}
