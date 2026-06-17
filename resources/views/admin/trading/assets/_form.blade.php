{{-- Shared form partial for create/edit asset --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

  <div class="ad-card">
    <div class="ad-card-header"><span class="ad-card-title">Identity</span></div>
    <div class="ad-card-body">

      <div class="ad-form-group">
        <label class="ad-label">Symbol <span style="color:var(--ad-danger)">*</span></label>
        <input class="ad-input @error('symbol') is-invalid @enderror" type="text" name="symbol"
               value="{{ old('symbol', $asset?->symbol) }}" placeholder="BTCUSDT" maxlength="30">
        @error('symbol')<div class="ad-field-error">{{ $message }}</div>@enderror
      </div>

      <div class="ad-form-group">
        <label class="ad-label">Name <span style="color:var(--ad-danger)">*</span></label>
        <input class="ad-input @error('name') is-invalid @enderror" type="text" name="name"
               value="{{ old('name', $asset?->name) }}" placeholder="Bitcoin / USDT" maxlength="100">
        @error('name')<div class="ad-field-error">{{ $message }}</div>@enderror
      </div>

      <div class="ad-form-group">
        <label class="ad-label">Asset Class <span style="color:var(--ad-danger)">*</span></label>
        <select class="ad-input @error('asset_class') is-invalid @enderror" name="asset_class">
          @foreach(['crypto','forex','stock','sim'] as $class)
            <option value="{{ $class }}" {{ old('asset_class', $asset?->asset_class) === $class ? 'selected' : '' }}>
              {{ ucfirst($class) }}
            </option>
          @endforeach
        </select>
        @error('asset_class')<div class="ad-field-error">{{ $message }}</div>@enderror
      </div>

      <div class="ad-form-group">
        <label class="ad-label">Payout % <span style="color:var(--ad-danger)">*</span></label>
        <input class="ad-input @error('payout_percent') is-invalid @enderror" type="number"
               step="0.01" name="payout_percent"
               value="{{ old('payout_percent', $asset?->payout_percent ?? 80) }}" min="1" max="200">
        @error('payout_percent')<div class="ad-field-error">{{ $message }}</div>@enderror
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div class="ad-form-group">
          <label class="ad-label">Min Stake (USD)</label>
          <input class="ad-input @error('min_stake') is-invalid @enderror" type="number" name="min_stake"
                 value="{{ old('min_stake', $asset?->min_stake ?? 10) }}" min="1">
          @error('min_stake')<div class="ad-field-error">{{ $message }}</div>@enderror
        </div>
        <div class="ad-form-group">
          <label class="ad-label">Max Stake (USD)</label>
          <input class="ad-input @error('max_stake') is-invalid @enderror" type="number" name="max_stake"
                 value="{{ old('max_stake', $asset?->max_stake ?? 1000) }}" min="1">
          @error('max_stake')<div class="ad-field-error">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="ad-form-group">
        <label class="ad-label">Allowed Expiries (seconds, comma-separated)</label>
        <input class="ad-input @error('allowed_expiries') is-invalid @enderror" type="text"
               name="allowed_expiries"
               value="{{ old('allowed_expiries', $asset ? implode(', ', $asset->allowed_expiries ?? []) : '30,60,300') }}"
               placeholder="30, 60, 300">
        @error('allowed_expiries')<div class="ad-field-error">{{ $message }}</div>@enderror
      </div>

      <div class="ad-form-group">
        <label class="ad-label" style="display:flex;align-items:center;gap:0.5rem;">
          <input type="hidden" name="enabled" value="0">
          <input type="checkbox" name="enabled" value="1"
                 {{ old('enabled', $asset?->enabled ?? true) ? 'checked' : '' }}>
          Enabled (visible to students)
        </label>
      </div>

    </div>
  </div>

  <div class="ad-card">
    <div class="ad-card-header"><span class="ad-card-title">Market Data</span></div>
    <div class="ad-card-body">

      <div class="ad-form-group">
        <label class="ad-label" style="display:flex;align-items:center;gap:0.5rem;">
          <input type="hidden" name="supports_live" value="0">
          <input type="checkbox" name="supports_live" value="1"
                 {{ old('supports_live', $asset?->supports_live ?? false) ? 'checked' : '' }}>
          Supports live Binance feed
        </label>
      </div>

      <div class="ad-form-group">
        <label class="ad-label">Live symbol (Binance)</label>
        <input class="ad-input @error('live_symbol') is-invalid @enderror" type="text"
               name="live_symbol" value="{{ old('live_symbol', $asset?->live_symbol) }}"
               placeholder="BTCUSDT" maxlength="30">
        @error('live_symbol')<div class="ad-field-error">{{ $message }}</div>@enderror
      </div>

      <hr style="border-color:var(--ad-border);margin:1rem 0;">
      <p style="font-size:0.78rem;color:var(--ad-muted);margin-bottom:0.75rem;">
        GBM simulation parameters
      </p>

      <div class="ad-form-group">
        <label class="ad-label">Sim Start Price <span style="color:var(--ad-danger)">*</span></label>
        <input class="ad-input @error('sim_start_price') is-invalid @enderror" type="number"
               step="0.00000001" name="sim_start_price"
               value="{{ old('sim_start_price', $asset?->sim_start_price ?? 30000) }}" min="0.0001">
        @error('sim_start_price')<div class="ad-field-error">{{ $message }}</div>@enderror
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div class="ad-form-group">
          <label class="ad-label">Drift (μ)</label>
          <input class="ad-input @error('sim_drift') is-invalid @enderror" type="number"
                 step="0.00001" name="sim_drift"
                 value="{{ old('sim_drift', $asset?->sim_drift ?? 0.0001) }}">
          @error('sim_drift')<div class="ad-field-error">{{ $message }}</div>@enderror
        </div>
        <div class="ad-form-group">
          <label class="ad-label">Volatility (σ)</label>
          <input class="ad-input @error('sim_volatility') is-invalid @enderror" type="number"
                 step="0.00001" name="sim_volatility"
                 value="{{ old('sim_volatility', $asset?->sim_volatility ?? 0.002) }}" min="0">
          @error('sim_volatility')<div class="ad-field-error">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="ad-form-group">
        <label class="ad-label">Sim Seed (integer) <span style="color:var(--ad-danger)">*</span></label>
        <input class="ad-input @error('sim_seed') is-invalid @enderror" type="number"
               name="sim_seed"
               value="{{ old('sim_seed', $asset?->sim_seed ?? rand(1000, 99999)) }}">
        @error('sim_seed')<div class="ad-field-error">{{ $message }}</div>@enderror
        <div style="font-size:0.72rem;color:var(--ad-muted);margin-top:0.25rem;">
          Unique integer per asset — determines the random price path.
        </div>
      </div>

    </div>
  </div>

</div>
