@extends('layouts.trade-app')
@section('title', 'Identity Verification')

@push('styles')
<style>
  .kyc-wrap{max-width:600px;margin:0 auto;}
  .kyc-hero{display:flex;align-items:center;gap:16px;margin-bottom:18px;}
  .kyc-shield{width:56px;height:56px;border-radius:15px;flex-shrink:0;display:flex;align-items:center;justify-content:center;
    font-size:1.5rem;background:var(--gold-muted);color:var(--gold);}
  .kyc-shield.ok{background:var(--green-muted);color:var(--green);}
  .kyc-shield.wait{background:rgba(245,158,11,.14);color:var(--gold);}
  .kyc-shield.bad{background:var(--red-muted);color:var(--red);}
  .kyc-title{font-size:1.15rem;font-weight:900;}
  .kyc-badge{display:inline-flex;align-items:center;gap:6px;font-size:.62rem;font-weight:800;text-transform:uppercase;
    letter-spacing:.05em;padding:4px 10px;border-radius:20px;margin-top:5px;}
  .kyc-badge.unverified{background:var(--bg-hover);color:var(--text-muted);}
  .kyc-badge.pending{background:rgba(245,158,11,.14);color:var(--gold);}
  .kyc-badge.approved{background:var(--green-muted);color:var(--green);}
  .kyc-badge.declined,.kyc-badge.resubmit{background:var(--red-muted);color:var(--red);}
  .kyc-steps{display:flex;gap:8px;margin-bottom:18px;}
  .kyc-step{flex:1;text-align:center;font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;
    color:var(--text-dim);padding-top:24px;position:relative;}
  .kyc-step::before{content:"";position:absolute;top:0;left:50%;transform:translateX(-50%);width:18px;height:18px;border-radius:50%;
    background:var(--bg-elevated);border:2px solid var(--border);}
  .kyc-step.done{color:var(--green);} .kyc-step.done::before{background:var(--green);border-color:var(--green);}
  .kyc-step.active{color:var(--gold);} .kyc-step.active::before{background:var(--gold);border-color:var(--gold);}
  .kyc-step::after{content:"";position:absolute;top:8px;left:-50%;width:100%;height:2px;background:var(--border);z-index:-1;}
  .kyc-step:first-child::after{display:none;}
  .kyc-step.done::after{background:var(--green);}
  .kf-field{margin-bottom:15px;}
  .kf-label{display:block;font-size:.74rem;font-weight:800;margin-bottom:6px;}
  .kf-label .opt{color:var(--text-dim);font-weight:600;}
  .kf-input{width:100%;background:var(--bg-base);border:1px solid var(--border);border-radius:10px;padding:12px 14px;
    color:var(--text-primary);font-size:.92rem;font-family:inherit;transition:.15s;}
  .kf-input:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px var(--gold-muted);}
  .kf-err{font-size:.72rem;color:var(--red);margin-top:5px;font-weight:600;}
  .kf-drop{border:1.5px dashed var(--border);border-radius:12px;padding:18px;text-align:center;cursor:pointer;transition:.15s;}
  .kf-drop:hover{border-color:var(--gold);}
  .kf-drop i{font-size:1.4rem;color:var(--text-muted);}
  .kf-drop .hint{font-size:.72rem;color:var(--text-muted);margin-top:6px;}
  .kf-drop .name{font-size:.78rem;color:var(--gold);font-weight:700;margin-top:6px;display:none;}
  .kf-prev{max-width:200px;max-height:140px;border-radius:9px;margin:10px auto 0;display:none;border:1px solid var(--border);}
  .kf-submit{width:100%;background:linear-gradient(135deg,#16d291,#0fa873);color:#04130d;border:none;border-radius:11px;
    padding:14px;font-size:.9rem;font-weight:800;cursor:pointer;}
  .kf-submit:hover{filter:brightness(1.07);}
  .kyc-note{background:var(--red-muted);border:1px solid rgba(245,59,87,.3);border-radius:10px;padding:12px 14px;
    font-size:.8rem;color:var(--text-primary);margin-bottom:16px;line-height:1.5;}
  .kyc-info-row{display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--border);font-size:.82rem;}
  .kyc-info-row:last-child{border-bottom:none;}
  .kyc-info-row .l{color:var(--text-muted);}
</style>
@endpush

@section('content')
@php
  $st = $user->kyc_status;
  $map = [
    'unverified' => ['shield'=>'', 'icon'=>'fa-id-card', 'label'=>'Not verified'],
    'pending'    => ['shield'=>'wait', 'icon'=>'fa-hourglass-half', 'label'=>'Under review'],
    'approved'   => ['shield'=>'ok', 'icon'=>'fa-circle-check', 'label'=>'Verified'],
    'declined'   => ['shield'=>'bad', 'icon'=>'fa-circle-xmark', 'label'=>'Declined'],
    'resubmit'   => ['shield'=>'bad', 'icon'=>'fa-rotate', 'label'=>'Action needed'],
  ];
  $m = $map[$st] ?? $map['unverified'];
@endphp

<div class="kyc-wrap">
  @if(session('success'))
  <div style="background:var(--green-muted);color:var(--green);border:1px solid rgba(0,201,123,.3);padding:11px 15px;border-radius:10px;font-size:.82rem;margin-bottom:16px;">
    <i class="fas fa-circle-check"></i> {{ session('success') }}
  </div>
  @endif
  @if(session('error'))
  <div style="background:var(--red-muted);color:var(--red);border:1px solid rgba(245,59,87,.3);padding:11px 15px;border-radius:10px;font-size:.82rem;margin-bottom:16px;">
    <i class="fas fa-triangle-exclamation"></i> {{ session('error') }}
  </div>
  @endif

  {{-- Header --}}
  <div class="kyc-hero">
    <div class="kyc-shield {{ $m['shield'] }}"><i class="fas {{ $m['icon'] }}"></i></div>
    <div>
      <div class="kyc-title">Identity Verification</div>
      <span class="kyc-badge {{ $st }}"><i class="fas fa-circle" style="font-size:.5em;"></i> {{ $m['label'] }}</span>
    </div>
  </div>

  {{-- Progress --}}
  <div class="kyc-steps">
    <div class="kyc-step {{ in_array($st,['pending','approved','declined','resubmit'])?'done':'active' }}">Submit</div>
    <div class="kyc-step {{ $st==='approved'?'done':($st==='pending'?'active':($st==='declined'||$st==='resubmit'?'active':'')) }}">Review</div>
    <div class="kyc-step {{ $st==='approved'?'done':'' }}">Verified</div>
  </div>

  {{-- ── Approved ── --}}
  @if($st === 'approved')
  <div class="ta-card">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
      <i class="fas fa-circle-check" style="color:var(--green);font-size:1.3rem;"></i>
      <div><div style="font-weight:800;">You're verified ✓</div>
        <div style="font-size:.76rem;color:var(--text-muted);">You can now go Live to trade and use deposits & withdrawals.</div></div>
    </div>
    @if($submission)
    <div class="kyc-info-row"><span class="l">Name</span><span>{{ $submission->full_name }}</span></div>
    <div class="kyc-info-row"><span class="l">Document</span><span>{{ $submission->document_label }}</span></div>
    <div class="kyc-info-row"><span class="l">Verified on</span><span>{{ $user->kyc_verified_at?->format('d M Y') }}</span></div>
    @endif
    <a href="{{ route('trade.index') }}" class="kf-submit" style="display:block;text-align:center;text-decoration:none;margin-top:16px;">
      <i class="fas fa-bolt"></i> Go to the trading screen</a>
  </div>

  {{-- ── Pending ── --}}
  @elseif($st === 'pending')
  <div class="ta-card" style="text-align:center;padding:30px 20px;">
    <i class="fas fa-hourglass-half" style="font-size:2rem;color:var(--gold);"></i>
    <div style="font-weight:800;margin-top:12px;">Your verification is under review</div>
    <p style="font-size:.8rem;color:var(--text-muted);max-width:380px;margin:8px auto 0;line-height:1.6;">
      Our team is checking your documents. You'll get a notification the moment it's reviewed — usually within a few hours.
    </p>
    @if($submission)
    <div style="max-width:320px;margin:18px auto 0;text-align:left;">
      <div class="kyc-info-row"><span class="l">Submitted</span><span>{{ $submission->created_at->format('d M Y, H:i') }}</span></div>
      <div class="kyc-info-row"><span class="l">Document</span><span>{{ $submission->document_label }}</span></div>
    </div>
    @endif
  </div>

  {{-- ── Needs action / first time ── --}}
  @else
    @if($submission && $submission->admin_note)
    <div class="kyc-note">
      <strong>{{ $st === 'resubmit' ? 'Please redo your verification.' : 'Your last verification was declined.' }}</strong><br>
      {{ $submission->admin_note }}
    </div>
    @endif

    <div class="ta-card">
      <p style="font-size:.82rem;color:var(--text-muted);line-height:1.6;margin:0 0 16px;">
        To use real-money features — going Live to trade, deposits and withdrawals — we need to verify your identity.
        Provide your legal name and an official document (passport, national ID or driver's licence), then upload a clear photo of it.
      </p>

      <form method="POST" action="{{ route('trade.kyc.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="kf-field">
          <label class="kf-label" for="full_name">Full legal name</label>
          <input class="kf-input" type="text" id="full_name" name="full_name" value="{{ old('full_name', $user->name) }}" required>
          @error('full_name')<div class="kf-err">{{ $message }}</div>@enderror
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="kf-field">
            <label class="kf-label" for="document_type">Document type</label>
            <select class="kf-input" id="document_type" name="document_type" required>
              @foreach(['passport'=>'Passport','national_id'=>'National ID','drivers_license'=>"Driver's licence",'other'=>'Other official document'] as $v=>$l)
                <option value="{{ $v }}" {{ old('document_type')===$v?'selected':'' }}>{{ $l }}</option>
              @endforeach
            </select>
            @error('document_type')<div class="kf-err">{{ $message }}</div>@enderror
          </div>
          <div class="kf-field">
            <label class="kf-label" for="document_number">Document number</label>
            <input class="kf-input" type="text" id="document_number" name="document_number" value="{{ old('document_number') }}" required>
            @error('document_number')<div class="kf-err">{{ $message }}</div>@enderror
          </div>
        </div>

        <div class="kf-field">
          <label class="kf-label">Photo of your document</label>
          <label class="kf-drop" for="document" id="kfDrop">
            <i class="fas fa-cloud-arrow-up"></i>
            <div class="hint">Tap to upload — JPG, PNG, WEBP or PDF (max 8 MB)</div>
            <div class="name" id="kfName"></div>
            <img class="kf-prev" id="kfPrev" alt="preview">
          </label>
          <input type="file" id="document" name="document" accept="image/*,application/pdf" style="display:none;" required>
          @error('document')<div class="kf-err">{{ $message }}</div>@enderror
          <div style="font-size:.68rem;color:var(--text-dim);margin-top:6px;">
            <i class="fas fa-lock"></i> Stored securely and only seen by our review team.
          </div>
        </div>

        <div class="kf-field">
          <label class="kf-label" for="message">Message <span class="opt">(optional)</span></label>
          <textarea class="kf-input" id="message" name="message" rows="2" placeholder="Anything we should know?">{{ old('message') }}</textarea>
          @error('message')<div class="kf-err">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="kf-submit"><i class="fas fa-paper-plane"></i> Submit for verification</button>
      </form>
    </div>
  @endif
</div>

@push('scripts')
<script>
(function(){
  var input = document.getElementById('document');
  if (!input) return;
  input.addEventListener('change', function(){
    var f = this.files[0], nameEl = document.getElementById('kfName'), prev = document.getElementById('kfPrev');
    if (!f) return;
    nameEl.textContent = f.name; nameEl.style.display = 'block';
    if (f.type.indexOf('image') === 0) {
      prev.src = URL.createObjectURL(f); prev.style.display = 'block';
    } else { prev.style.display = 'none'; }
  });
})();
</script>
@endpush
@endsection
