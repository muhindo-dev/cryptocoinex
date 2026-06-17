@extends('layouts.admin')
@section('title', 'New Distribution')

@push('styles')
<style>
  .dz-amt{font-size:1.5rem;font-weight:800;font-variant-numeric:tabular-nums;}
  .dz-sum{display:flex;gap:1.5rem;flex-wrap:wrap;margin:.6rem 0 0;font-size:.8rem;color:var(--ad-muted);}
  .dz-sum b{color:var(--ad-text,#f4f7fc);}
  .dz-mismatch{color:var(--ad-danger);font-weight:700;}
  .dz-ok{color:var(--ad-success);font-weight:700;}
  td.dz-share{font-weight:700;color:var(--ad-success);font-variant-numeric:tabular-nums;white-space:nowrap;}
</style>
@endpush

@section('content')
<div class="ad-page-header">
  <div>
    <h1>New Profit Distribution</h1>
    <div class="ad-breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span>
      <a href="{{ route('admin.trading.live.overview') }}">Live Account</a> <span>/</span>
      <a href="{{ route('admin.trading.live.distributions.index') }}">Distributions</a> <span>/</span> New
    </div>
  </div>
</div>

@if(session('error'))<div class="alert-ad alert-danger" style="margin-bottom:1rem;">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert-ad alert-danger" style="margin-bottom:1rem;"><ul style="margin:0;padding-left:1.2rem;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

@if($wallets->isEmpty())
  <div class="ad-card"><div class="ad-card-body" style="text-align:center;color:var(--ad-muted);padding:2.5rem;">
    <i class="fas fa-users-slash" style="font-size:1.6rem;opacity:.4;display:block;margin-bottom:10px;"></i>
    No members hold a live balance yet, so there's nothing to distribute. Members appear here once they have funds.
  </div></div>
@else
<form method="POST" action="{{ route('admin.trading.live.distributions.store') }}" style="max-width:860px;" id="dzForm">
  @csrf

  <div class="ad-card" style="margin-bottom:1.25rem;">
    <div class="ad-card-header"><span class="ad-card-title">Pool to distribute</span></div>
    <div class="ad-card-body">
      <div style="display:grid;grid-template-columns:1fr 2fr;gap:1.25rem;align-items:start;">
        <div class="ad-form-group" style="margin:0;">
          <label class="ad-label">Total amount ({{ $currency }})</label>
          <input class="ad-input dz-amt" type="number" min="1" step="1" name="total_amount" id="dzAmount"
                 value="{{ old('total_amount') }}" placeholder="0" autocomplete="off" required>
        </div>
        <div class="ad-form-group" style="margin:0;">
          <label class="ad-label">Note <span style="color:var(--ad-muted);font-weight:400;">(optional · shown to members)</span></label>
          <input class="ad-input" type="text" name="note" value="{{ old('note') }}" maxlength="300"
                 placeholder="e.g. Q1 trading profits">
        </div>
      </div>
      <div class="dz-sum">
        <span>Eligible members: <b>{{ $wallets->count() }}</b></span>
        <span>Total live balance: <b>{{ \App\Support\Money::format($totalBase, $currency) }}</b></span>
        <span id="dzCheck"></span>
      </div>
    </div>
  </div>

  <div class="ad-card" style="margin-bottom:1.25rem;">
    <div class="ad-card-header"><span class="ad-card-title">Who receives what</span>
      <span style="font-size:.74rem;color:var(--ad-muted);">live preview</span></div>
    <div class="ad-table-wrap">
      <table class="ad-table">
        <thead><tr><th>Member</th><th>Live balance</th><th>Share %</th><th style="text-align:right;">Receives</th></tr></thead>
        <tbody id="dzRows">
          @foreach($wallets as $w)
          <tr data-base="{{ (int) $w->balance }}">
            <td><strong>{{ $w->user->name ?? '—' }}</strong><div style="font-size:.72rem;color:var(--ad-muted);">{{ $w->user->email ?? '' }}</div></td>
            <td style="white-space:nowrap;">{{ \App\Support\Money::format($w->balance, $currency) }}</td>
            <td class="dz-pct" style="color:var(--ad-muted);">{{ rtrim(rtrim(number_format($w->balance / max(1,$totalBase) * 100, 2), '0'), '.') }}%</td>
            <td class="dz-share" style="text-align:right;">—</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="ad-card" style="margin-bottom:1.25rem;border-color:rgba(245,166,35,.35);">
    <div class="ad-card-body">
      <label class="ad-label" style="display:flex;align-items:flex-start;gap:.6rem;cursor:pointer;font-weight:400;">
        <input type="checkbox" name="confirm" value="1" style="margin-top:3px;" required>
        <span style="font-size:.84rem;color:var(--ad-text,#f4f7fc);">
          I confirm this credits <strong id="dzConfirmAmt">real funds</strong> to every member above. This cannot be undone.
        </span>
      </label>
    </div>
  </div>

  <button type="submit" class="btn-ad btn-ad-primary" id="dzSubmit" disabled>
    <i class="fas fa-paper-plane"></i> Distribute now
  </button>
</form>

@push('scripts')
<script>
(function () {
  const amountEl = document.getElementById('dzAmount');
  const rows = Array.from(document.querySelectorAll('#dzRows tr'));
  const bases = rows.map(r => parseInt(r.dataset.base, 10) || 0);
  const totalBase = bases.reduce((a, b) => a + b, 0);
  const fmt = n => '{{ $currency }} ' + Number(n).toLocaleString('en-US');
  const checkEl = document.getElementById('dzCheck');
  const submitEl = document.getElementById('dzSubmit');
  const confirmEl = document.querySelector('input[name=confirm]');
  const confirmAmt = document.getElementById('dzConfirmAmt');

  /* Mirror the server's largest-remainder apportionment exactly. */
  function compute(total) {
    if (!total || total <= 0 || totalBase <= 0) return rows.map(() => 0);
    const exact = bases.map(b => total * b / totalBase);
    const shares = exact.map(Math.floor);
    let leftover = total - shares.reduce((a, b) => a + b, 0);
    const order = exact.map((e, i) => [i, e - Math.floor(e)]).sort((a, b) => b[1] - a[1]);
    for (let k = 0; k < leftover; k++) shares[order[k][0]] += 1;
    return shares;
  }

  function render() {
    const total = parseInt(amountEl.value, 10) || 0;
    const shares = compute(total);
    let sum = 0;
    rows.forEach((r, i) => { r.querySelector('.dz-share').textContent = total ? fmt(shares[i]) : '—'; sum += shares[i]; });
    confirmAmt.textContent = total ? fmt(total) : 'real funds';
    if (!total) { checkEl.textContent = ''; submitEl.disabled = true; }
    else if (sum === total) { checkEl.innerHTML = '<span class="dz-ok">✓ Splits exactly to ' + fmt(total) + '</span>'; submitEl.disabled = !confirmEl.checked; }
    else { checkEl.innerHTML = '<span class="dz-mismatch">Mismatch: ' + fmt(sum) + '</span>'; submitEl.disabled = true; }
  }

  amountEl.addEventListener('input', render);
  confirmEl.addEventListener('change', render);
  render();
})();
</script>
@endpush
@endif
@endsection
