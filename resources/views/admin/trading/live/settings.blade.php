@extends('layouts.admin')
@section('title', 'Live Account Settings')

@section('content')
<div class="ad-page-header">
  <div>
    <h1>Live Account Settings</h1>
    <div class="ad-breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span>
      <a href="{{ route('admin.trading.live.overview') }}">Live Account</a> <span>/</span> Settings
    </div>
  </div>
</div>

@if($errors->any())
  <div class="alert-ad alert-danger" style="margin-bottom:1rem;">
    <ul style="margin:0;padding-left:1.2rem;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

<form method="POST" action="{{ route('admin.trading.live.settings.update') }}" style="max-width:720px;">
  @csrf

  {{-- Status & currency --}}
  <div class="ad-card" style="margin-bottom:1.5rem;">
    <div class="ad-card-header"><span class="ad-card-title">Status &amp; Currency</span></div>
    <div class="ad-card-body">
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:1rem;">
        <div class="ad-form-group">
          <label class="ad-label">Live Account module</label>
          <select class="ad-input" name="live_account_enabled">
            <option value="true"  {{ ($settings['live_account_enabled'] ?? 'true')==='true'  ? 'selected' : '' }}>Enabled — students can deposit, trade &amp; withdraw</option>
            <option value="false" {{ ($settings['live_account_enabled'] ?? 'true')==='false' ? 'selected' : '' }}>Disabled — hidden from students</option>
          </select>
        </div>
        <div class="ad-form-group">
          <label class="ad-label">Currency</label>
          <input class="ad-input" type="text" name="live_account_currency"
                 value="{{ old('live_account_currency', $settings['live_account_currency'] ?? 'USD') }}">
        </div>
      </div>
      <div style="font-size:.74rem;color:var(--ad-muted);margin-top:.5rem;">
        Profits are paid out via <a href="{{ route('admin.trading.live.distributions.create') }}" style="color:var(--ad-accent);">profit distributions</a>, not an automatic rate. Amounts are whole units — no decimals.
      </div>
    </div>
  </div>

  {{-- Limits --}}
  <div class="ad-card" style="margin-bottom:1.5rem;">
    <div class="ad-card-header"><span class="ad-card-title">Limits</span></div>
    <div class="ad-card-body">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div class="ad-form-group">
          <label class="ad-label">Minimum deposit</label>
          <input class="ad-input" type="number" min="0" name="live_account_min_deposit"
                 value="{{ old('live_account_min_deposit', $settings['live_account_min_deposit'] ?? '0') }}">
        </div>
        <div class="ad-form-group">
          <label class="ad-label">Minimum withdrawal</label>
          <input class="ad-input" type="number" min="0" name="live_account_min_withdrawal"
                 value="{{ old('live_account_min_withdrawal', $settings['live_account_min_withdrawal'] ?? '0') }}">
        </div>
      </div>
    </div>
  </div>

  {{-- Crypto payment details --}}
  <div class="ad-card" style="margin-bottom:1.5rem;">
    <div class="ad-card-header"><span class="ad-card-title">Crypto Payment Details</span></div>
    <div class="ad-card-body">
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:1rem;">
        <div class="ad-form-group">
          <label class="ad-label">Receiving wallet address (USD)</label>
          <input class="ad-input" type="text" name="live_account_crypto_address"
                 value="{{ old('live_account_crypto_address', $settings['live_account_crypto_address'] ?? '') }}"
                 placeholder="e.g. TXk9...8aQ2" style="font-family:ui-monospace,monospace;">
          <div style="font-size:.74rem;color:var(--ad-muted);margin-top:.25rem;">The address students send USD to. Shown on the deposit screen.</div>
        </div>
        <div class="ad-form-group">
          <label class="ad-label">Network</label>
          <input class="ad-input" type="text" name="live_account_crypto_network"
                 value="{{ old('live_account_crypto_network', $settings['live_account_crypto_network'] ?? 'USD (TRC20)') }}"
                 placeholder="USD (TRC20)">
        </div>
      </div>
      <div class="ad-form-group">
        <label class="ad-label">Payment link <span style="color:var(--ad-muted);font-weight:400;">(optional)</span></label>
        <input class="ad-input" type="url" name="live_account_payment_link"
               value="{{ old('live_account_payment_link', $settings['live_account_payment_link'] ?? '') }}"
               placeholder="https://… hosted checkout / pay link">
        <div style="font-size:.74rem;color:var(--ad-muted);margin-top:.25rem;">If set, students see a "Pay via secure link" button.</div>
      </div>
      <div class="ad-form-group">
        <label class="ad-label">Payment instructions</label>
        <textarea class="ad-input" name="live_account_payment_instructions" rows="9"
                  style="font-family:inherit;line-height:1.6;">{{ old('live_account_payment_instructions', $settings['live_account_payment_instructions'] ?? '') }}</textarea>
        <div style="font-size:.74rem;color:var(--ad-muted);margin-top:.25rem;">
          Explain exactly how to pay and that a screenshot is required. Line breaks are preserved.
        </div>
      </div>
    </div>
  </div>

  <button type="submit" class="btn-ad btn-ad-primary"><i class="fas fa-save"></i> Save settings</button>
</form>
@endsection
