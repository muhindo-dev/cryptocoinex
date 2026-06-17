@extends('layouts.trade-app')
@section('title', 'Deposit · Live Account')

@push('styles')
<style>
  .lf-wrap{max-width:560px;margin:0 auto;}
  .lf-back{display:inline-flex;align-items:center;gap:7px;font-size:.76rem;color:var(--text-muted);text-decoration:none;margin-bottom:14px;font-weight:600;}
  .lf-back:hover{color:var(--gold);}
  .lf-num{display:flex;align-items:center;gap:10px;background:var(--bg-base);border:1px solid var(--border);
    border-radius:10px;padding:11px 13px;font-weight:700;color:var(--text-primary);font-size:.82rem;word-break:break-all;}
  .lf-num code{font-family:ui-monospace,monospace;flex:1;min-width:0;}
  .lf-copy{flex-shrink:0;background:var(--gold-muted);color:var(--gold);border:none;border-radius:7px;padding:6px 10px;
    font-size:.7rem;font-weight:800;cursor:pointer;}
  .lf-copy:hover{filter:brightness(1.1);}
  .lf-netchip{display:inline-block;font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.04em;
    padding:3px 9px;border-radius:6px;background:var(--gold-muted);color:var(--gold);margin-bottom:8px;}
  .lf-link{display:inline-flex;align-items:center;gap:7px;margin-top:10px;font-size:.78rem;font-weight:800;color:var(--gold);text-decoration:none;}
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
  .lf-drop{border:1.5px dashed var(--border);border-radius:11px;padding:18px;text-align:center;cursor:pointer;transition:.15s;background:var(--bg-base);}
  .lf-drop:hover{border-color:var(--gold);}
  .lf-drop i{font-size:1.5rem;color:var(--text-dim);}
  .lf-drop .t{font-size:.8rem;font-weight:700;margin-top:7px;}
  .lf-drop .s{font-size:.66rem;color:var(--text-muted);margin-top:3px;}
  .lf-preview{margin-top:10px;border-radius:10px;max-height:200px;border:1px solid var(--border);display:none;}
  .lf-submit{width:100%;background:linear-gradient(135deg,#16d291,#0fa873);color:#04130d;border:none;border-radius:11px;
    padding:14px;font-size:.9rem;font-weight:800;cursor:pointer;transition:.15s;}
  .lf-submit:hover{filter:brightness(1.07);}
  .lf-howto{font-size:.78rem;color:var(--text-muted);line-height:1.7;white-space:pre-line;}
  .lf-warn{display:flex;gap:9px;background:rgba(245,59,87,.07);border:1px solid rgba(245,59,87,.2);border-radius:9px;
    padding:10px 12px;font-size:.72rem;color:var(--text-muted);line-height:1.5;margin-top:12px;}
  .lf-warn i{color:var(--red);margin-top:1px;}
</style>
@endpush

@section('content')
<div class="lf-wrap">
  <a href="{{ route('trade.live') }}" class="lf-back"><i class="fas fa-arrow-left"></i> Back to Live Account</a>

  {{-- Crypto payment instructions --}}
  <div class="ta-card" style="margin-bottom:16px;">
    <h2 style="margin:0 0 4px;font-size:1rem;"><i class="fab fa-bitcoin" style="color:var(--gold)"></i> Pay with crypto (USDT)</h2>
    <p style="font-size:.78rem;color:var(--text-muted);margin:0 0 12px;">Send the exact USD amount in USDT, then record it below with a screenshot.</p>

    <span class="lf-netchip">{{ $cryptoNetwork }}</span>
    @if($cryptoAddress)
      <div class="lf-num">
        <code id="lfAddr">{{ $cryptoAddress }}</code>
        <button type="button" class="lf-copy" onclick="lfCopy()"><i class="fas fa-copy"></i> Copy</button>
      </div>
    @else
      <div class="lf-num" style="color:var(--text-muted);">Payment address not configured yet — please contact support.</div>
    @endif

    @if($paymentLink)
      <a href="{{ $paymentLink }}" target="_blank" rel="noopener" class="lf-link"><i class="fas fa-arrow-up-right-from-square"></i> Or pay via secure payment link</a>
    @endif

    <div class="lf-howto" style="margin-top:13px;">{{ $instructions }}</div>

    <div class="lf-warn"><i class="fas fa-triangle-exclamation"></i>
      <span>Only send <strong>{{ $cryptoNetwork }}</strong> to this address. Sending a different asset or network may result in permanent loss of funds.</span></div>
  </div>

  {{-- Deposit request form --}}
  <div class="ta-card">
    <h2 style="margin:0 0 14px;font-size:1rem;"><i class="fas fa-circle-down" style="color:var(--green)"></i> Record your deposit</h2>

    @if($errors->any())
    <div style="background:var(--red-muted);color:var(--red);border:1px solid rgba(245,59,87,.3);padding:10px 13px;border-radius:9px;font-size:.78rem;margin-bottom:14px;">
      <i class="fas fa-circle-exclamation"></i> Please fix the errors below.
    </div>
    @endif

    <form method="POST" action="{{ route('trade.live.deposit.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="lf-field">
        <label class="lf-label" for="amount">Amount (USD)</label>
        <div class="lf-money">
          <span class="sym">$</span>
          <input type="number" step="1" min="{{ $min }}" id="amount" name="amount" value="{{ old('amount') }}"
                 class="lf-input lf-amt" placeholder="0" required>
        </div>
        <div class="lf-hint">Enter the exact USD value you sent.@if($min>0) Minimum ${{ number_format($min) }}.@endif</div>
        @error('amount')<div class="lf-err">{{ $message }}</div>@enderror
      </div>

      <div class="lf-field">
        <label class="lf-label" for="reference">Transaction hash / reference <span class="opt">(optional)</span></label>
        <input type="text" id="reference" name="reference" value="{{ old('reference') }}"
               class="lf-input" placeholder="e.g. 0x… or TRON txid">
        <div class="lf-hint">The transaction ID from your wallet, if you have it.</div>
        @error('reference')<div class="lf-err">{{ $message }}</div>@enderror
      </div>

      <div class="lf-field">
        <label class="lf-label">Proof of payment screenshot</label>
        <div class="lf-drop" id="lfDrop" onclick="document.getElementById('proof').click()">
          <i class="fas fa-cloud-arrow-up"></i>
          <div class="t" id="lfDropT">Tap to upload your payment screenshot</div>
          <div class="s">PNG, JPG or WEBP · up to 5&nbsp;MB</div>
        </div>
        <input type="file" id="proof" name="proof" accept="image/png,image/jpeg,image/webp" style="display:none" required onchange="lfPreview(this)">
        <img id="lfPreview" class="lf-preview" alt="proof preview">
        @error('proof')<div class="lf-err">{{ $message }}</div>@enderror
      </div>

      <div class="lf-field">
        <label class="lf-label" for="note">Note to our team <span class="opt">(optional)</span></label>
        <textarea id="note" name="note" rows="2" class="lf-input" placeholder="Anything we should know?">{{ old('note') }}</textarea>
        @error('note')<div class="lf-err">{{ $message }}</div>@enderror
      </div>

      <button type="submit" class="lf-submit"><i class="fas fa-paper-plane"></i> Submit deposit request</button>
      <p style="font-size:.68rem;color:var(--text-dim);text-align:center;margin:12px 0 0;line-height:1.6;">
        We verify every payment against the blockchain and your screenshot before crediting. You'll be notified by email and in-app once approved.
      </p>
    </form>
  </div>
</div>

@push('scripts')
<script>
  function lfCopy(){
    var a = document.getElementById('lfAddr'); if(!a) return;
    navigator.clipboard.writeText(a.textContent.trim()).then(function(){
      var b = event.target.closest('.lf-copy'); var old = b.innerHTML;
      b.innerHTML = '<i class="fas fa-check"></i> Copied'; setTimeout(function(){ b.innerHTML = old; }, 1500);
    });
  }
  function lfPreview(input){
    var f = input.files && input.files[0]; if(!f) return;
    document.getElementById('lfDropT').textContent = f.name;
    var img = document.getElementById('lfPreview');
    img.src = URL.createObjectURL(f); img.style.display = 'block';
  }
</script>
@endpush
@endsection
