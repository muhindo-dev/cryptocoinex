@extends('layouts.admin')
@section('title', 'Trading Settings')

@section('content')
<div class="ad-page-header">
  <div>
    <h1>Trading Settings</h1>
    <div class="ad-breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> Trading <span>/</span> Settings
    </div>
  </div>
</div>

@if(session('success'))
  <div class="alert-ad alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif

@if($errors->any())
  <div class="alert-ad alert-danger" style="margin-bottom:1rem;">
    <ul style="margin:0;padding-left:1.2rem;">
      @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('admin.trading.settings.update') }}" style="max-width:640px;">
  @csrf

  <div class="ad-card" style="margin-bottom:1.5rem;">
    <div class="ad-card-header"><span class="ad-card-title">Wallet & Balance</span></div>
    <div class="ad-card-body">

      <div class="ad-form-group">
        <label class="ad-label">Default Starting Balance (USD)</label>
        <input class="ad-input @error('default_start_balance') is-invalid @enderror"
               type="number" name="default_start_balance" min="1"
               value="{{ old('default_start_balance', $settings['default_start_balance'] ?? 10000) }}">
        @error('default_start_balance')<div class="ad-field-error">{{ $message }}</div>@enderror
        <div style="font-size:0.75rem;color:var(--ad-muted);margin-top:0.25rem;">
          Given to new students and used when an admin resets a wallet.
        </div>
      </div>

      <div class="ad-form-group">
        <label class="ad-label" style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
          <input type="hidden" name="allow_student_reset" value="0">
          <input type="checkbox" name="allow_student_reset" value="1"
                 {{ ($settings['allow_student_reset'] ?? 'false') === 'true' ? 'checked' : '' }}>
          Allow students to self-reset their own wallet
        </label>
        <div style="font-size:0.75rem;color:var(--ad-muted);margin-top:0.25rem;">
          If enabled, a "Reset Practice Account" button is shown on the student's wallet page.
        </div>
      </div>

    </div>
  </div>

  <div class="ad-card" style="margin-bottom:1.5rem;">
    <div class="ad-card-header"><span class="ad-card-title">Market Mode</span></div>
    <div class="ad-card-body">

      <div class="ad-form-group">
        <label class="ad-label">Default Mode</label>
        <select class="ad-input @error('default_mode') is-invalid @enderror" name="default_mode">
          <option value="sim" {{ ($settings['default_mode'] ?? 'sim') === 'sim' ? 'selected' : '' }}>
            Simulated (GBM — always available)
          </option>
          <option value="live" {{ ($settings['default_mode'] ?? 'sim') === 'live' ? 'selected' : '' }}>
            Live (Binance real-time)
          </option>
        </select>
        @error('default_mode')<div class="ad-field-error">{{ $message }}</div>@enderror
      </div>

      <div class="ad-form-group">
        <label class="ad-label" style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
          <input type="hidden" name="live_mode_enabled" value="0">
          <input type="checkbox" name="live_mode_enabled" value="1"
                 {{ ($settings['live_mode_enabled'] ?? 'false') === 'true' ? 'checked' : '' }}>
          Enable live Binance data globally
        </label>
        <div style="font-size:0.75rem;color:var(--ad-muted);margin-top:0.25rem;">
          Master switch. When off, all assets fall back to simulation regardless of per-asset setting.
        </div>
      </div>

    </div>
  </div>

  <div class="ad-card" style="margin-bottom:1.5rem;">
    <div class="ad-card-header"><span class="ad-card-title">Settlement</span></div>
    <div class="ad-card-body">

      <div class="ad-form-group">
        <label class="ad-label">Tie Policy</label>
        <select class="ad-input @error('tie_policy') is-invalid @enderror" name="tie_policy">
          <option value="refund" {{ ($settings['tie_policy'] ?? 'refund') === 'refund' ? 'selected' : '' }}>
            Refund — return stake on exact tie
          </option>
          <option value="loss" {{ ($settings['tie_policy'] ?? 'refund') === 'loss' ? 'selected' : '' }}>
            Loss — treat exact tie as a loss
          </option>
        </select>
        @error('tie_policy')<div class="ad-field-error">{{ $message }}</div>@enderror
        <div style="font-size:0.75rem;color:var(--ad-muted);margin-top:0.25rem;">
          Applied when exit price equals entry price exactly.
        </div>
      </div>

    </div>
  </div>

  <button type="submit" class="btn-ad btn-ad-primary">
    <i class="fas fa-save"></i> Save Settings
  </button>
</form>
@endsection
