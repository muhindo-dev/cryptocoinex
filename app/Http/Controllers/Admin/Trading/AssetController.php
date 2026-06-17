<?php

namespace App\Http\Controllers\Admin\Trading;

use App\Http\Controllers\Controller;
use App\Models\Trading\Asset;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index()
    {
        $assets = Asset::orderBy('asset_class')->orderBy('name')->get();

        return view('admin.trading.assets.index', compact('assets'));
    }

    public function create()
    {
        return view('admin.trading.assets.create');
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        Asset::create($data);

        return redirect()->route('admin.trading.assets.index')
            ->with('success', 'Asset created.');
    }

    public function edit(Asset $asset)
    {
        return view('admin.trading.assets.edit', compact('asset'));
    }

    public function update(Request $request, Asset $asset)
    {
        $data = $this->validatedData($request);
        $asset->update($data);

        return redirect()->route('admin.trading.assets.index')
            ->with('success', 'Asset updated.');
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();

        return redirect()->route('admin.trading.assets.index')
            ->with('success', 'Asset deleted.');
    }

    /**
     * Toggle the enabled flag inline (Alpine.js, no page reload).
     */
    public function toggle(Asset $asset)
    {
        $asset->update(['enabled' => ! $asset->enabled]);

        return response()->json(['enabled' => $asset->enabled]);
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'symbol' => ['required', 'string', 'max:30'],
            'name' => ['required', 'string', 'max:100'],
            'asset_class' => ['required', 'in:crypto,forex,stock,sim'],
            'payout_percent' => ['required', 'numeric', 'min:1', 'max:200'],
            'min_stake' => ['required', 'integer', 'min:1'],
            'max_stake' => ['required', 'integer', 'min:1'],
            'allowed_expiries' => ['required', 'string'],
            'supports_live' => ['nullable', 'boolean'],
            'live_symbol' => ['nullable', 'string', 'max:30'],
            'sim_start_price' => ['required', 'numeric', 'min:0.0001'],
            'sim_drift' => ['required', 'numeric'],
            'sim_volatility' => ['required', 'numeric', 'min:0'],
            'sim_seed' => ['required', 'integer'],
            'enabled' => ['nullable', 'boolean'],
        ]);

        // Parse comma-separated expiries into array
        $data['allowed_expiries'] = array_map(
            'intval',
            array_filter(array_map('trim', explode(',', $data['allowed_expiries'])))
        );
        $data['supports_live'] = $request->boolean('supports_live');
        $data['enabled'] = $request->boolean('enabled');

        return $data;
    }
}
