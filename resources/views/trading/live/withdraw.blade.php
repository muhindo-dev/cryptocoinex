@extends('layouts.trade-app')
@section('title', 'Withdraw · Live Account')

@push('styles')
<style>
  .lf-wrap{max-width:520px;margin:0 auto;}
  .lf-back{display:inline-flex;align-items:center;gap:7px;font-size:.76rem;color:var(--text-muted);text-decoration:none;margin-bottom:14px;font-weight:600;}
  .lf-back:hover{color:var(--gold);}
  .lf-avail{display:flex;justify-content:space-between;align-items:center;background:var(--bg-elevated);border:1px solid var(--border);
    border-radius:11px;padding:14px 16px;margin-bottom:16px;}
  .lf-avail .v{font-size:1.5rem;font-weight:900;color:var(--gold);font-variant-numeric:tabular-nums;}
  .lf-field{margin-bottom:15px;}
  .lf-label{display:block;font-size:.74rem;font-weight:800;color:var(--text-primary);margin-bottom:6px;}
  .lf-label .opt{color:var(--text-dim);font-weight:600;}
  .lf-input{width:100%;background:var(--bg-base);border:1px solid var(--border);border-radius:10px;padding:12px 14px;
    color:var(--text-primary);font-size:.92rem;font-family:inherit;transition:.15s;}
  .lf-input:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px var(--gold-muted);}
  .lf-amt{font-size:1.35rem;font-weight:900;font-variant-numeric:tabular-nums;}
  .lf-money{position:relative;}
  .lf-money .sym{position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:1.2rem;font-weight:900;color:var(--text-muted);}
  .lf-money input{padding-left:30px;}
  .lf-hint{font-size:.68rem;color:var(--text-muted);margin-top:5px;}
  .lf-err{font-size:.72rem;color:var(--red);margin-top:5px;font-weight:600;}
  .lf-max{font-size:.7rem;color:var(--gold);font-weight:800;cursor:pointer;background:none;border:none;}
  .lf-submit{width:100%;background:var(--bg-elevated);border:1px solid var(--gold);color:var(--gold);border-radius:11px;
    padding:14px;font-size:.9rem;font-weight:800;cursor:pointer;transition:.15s;}
  .lf-submit:hover{background:var(--gold);color:#1a1206;}
</style>
@endpush

@section('content')
<div class="lf-wrap">
  <a href="{{ route('trade.live') }}" class="lf-back"><i class="fas fa-arrow-left"></i> Back to Live Account</a>

  <div class="ta-card">
    <h2 style="margin:0 0 14px;font-size:1rem;"><i class="fas fa-arrow-up-from-bracket" style="color:var(--gold)"></i> Withdraw to crypto wallet</h2>

    <div class="lf-avail">
      <div>
        <div style="font-size:.64rem;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);font-weight:800;">Available to withdraw</div>
        <div class="v">${{ number_format($available) }}</div>
      </div>
      <i class="fas fa-wallet" style="font-size:1.6rem;color:var(--text-dim);"></i>
    </div>

    @if($available < $min)
    <div style="background:var(--bg-elevated);border:1px dashed var(--border);border-radius:10px;padding:16px;text-align:center;color:var(--text-muted);font-size:.82rem;">
      You need at least <strong style="color:var(--gold)">${{ number_format($min) }}</strong> available to withdraw.
      Keep your funds growing — they earn daily returns.
    </div>
    @else
    <form method="POST" action="{{ route('trade.live.withdraw.store') }}">
      @csrf
      <div class="lf-field">
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <label class="lf-label" for="amount" style="margin:0;">Amount (USD)</label>
          <button type="button" class="lf-max" onclick="document.getElementById('amount').value={{ $available }}">Max</button>
        </div>
        <div class="lf-money">
          <span class="sym">$</span>
          <input type="number" step="1" min="{{ $min }}" max="{{ $available }}" id="amount" name="amount" value="{{ old('amount') }}"
                 class="lf-input lf-amt" placeholder="0" required>
        </div>
        <div class="lf-hint">@if($min>0)Minimum ${{ number_format($min) }}. @endif Max ${{ number_format($available) }} · paid in USD.</div>
        @error('amount')<div class="lf-err">{{ $message }}</div>@enderror
      </div>

      <div class="lf-field">
        <label class="lf-label" for="payout_address">Your USD wallet address</label>
        <input type="text" id="payout_address" name="payout_address" value="{{ old('payout_address') }}"
               class="lf-input" placeholder="Paste your wallet address" required>
        <div class="lf-hint">Double-check this — funds sent to a wrong address can't be recovered.</div>
        @error('payout_address')<div class="lf-err">{{ $message }}</div>@enderror
      </div>

      <div class="lf-field">
        <label class="lf-label" for="payout_network">Network</label>
        <input type="text" id="payout_network" name="payout_network" value="{{ old('payout_network', $network) }}"
               class="lf-input" placeholder="e.g. USD (TRC20)">
        <div class="lf-hint">The network your wallet address is on.</div>
        @error('payout_network')<div class="lf-err">{{ $message }}</div>@enderror
      </div>

      <div class="lf-field">
        <label class="lf-label" for="note">Note <span class="opt">(optional)</span></label>
        <textarea id="note" name="note" rows="2" class="lf-input" placeholder="Anything we should know?">{{ old('note') }}</textarea>
        @error('note')<div class="lf-err">{{ $message }}</div>@enderror
      </div>

      <button type="submit" class="lf-submit"><i class="fas fa-paper-plane"></i> Submit withdrawal request</button>
      <p style="font-size:.68rem;color:var(--text-dim);text-align:center;margin:12px 0 0;line-height:1.6;">
        Your balance only changes once we've sent the USD and approved the request. We'll email you when it's done.
      </p>
    </form>
    @endif
  </div>
</div>
@endsection
