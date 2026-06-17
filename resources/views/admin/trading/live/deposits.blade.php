@extends('layouts.admin')
@section('title', 'Live Deposits')

@push('styles')
<style>
  .lv-tabs{display:flex;gap:6px;flex-wrap:wrap;}
  .lv-tab{padding:7px 14px;border-radius:8px;font-size:.78rem;font-weight:700;text-decoration:none;border:1px solid var(--ad-border);
    color:var(--ad-muted);background:transparent;}
  .lv-tab.on{background:var(--ad-accent);border-color:var(--ad-accent);color:#1a1206;}
  .lv-tab .c{opacity:.7;margin-left:4px;}
  .lv-review{margin-top:8px;}
  .lv-review summary{list-style:none;cursor:pointer;display:inline-flex;align-items:center;gap:6px;font-size:.74rem;font-weight:700;
    color:var(--ad-accent);}
  .lv-review summary::-webkit-details-marker{display:none;}
  .lv-panel{margin-top:10px;padding:12px;border:1px solid var(--ad-border);border-radius:10px;background:rgba(255,255,255,.02);
    display:flex;flex-direction:column;gap:9px;max-width:420px;}
  .lv-panel textarea{width:100%;background:var(--ad-bg);border:1px solid var(--ad-border);border-radius:8px;padding:9px 11px;
    color:var(--ad-text,#f4f7fc);font-size:.82rem;font-family:inherit;resize:vertical;}
  .lv-panel input[type=text]{width:100%;background:var(--ad-bg);border:1px solid var(--ad-border);border-radius:8px;padding:9px 11px;
    color:var(--ad-text,#f4f7fc);font-size:.82rem;font-family:inherit;}
  .lv-acts{display:flex;gap:8px;}
  .lv-ref{font-family:ui-monospace,monospace;font-size:.78rem;color:var(--ad-text,#f4f7fc);background:rgba(255,255,255,.05);
    padding:2px 8px;border-radius:6px;}
</style>
@endpush

@section('content')
<div class="ad-page-header">
  <div>
    <h1>Deposit Requests</h1>
    <div class="ad-breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span>
      <a href="{{ route('admin.trading.live.overview') }}">Live Account</a> <span>/</span> Deposits
    </div>
  </div>
</div>

<div class="ad-card">
  <div class="ad-card-header" style="justify-content:flex-start;">
    <div class="lv-tabs">
      @foreach(['pending'=>'Pending','approved'=>'Approved','declined'=>'Declined'] as $key=>$label)
      <a href="{{ route('admin.trading.live.deposits', ['status'=>$key]) }}" class="lv-tab {{ $status===$key?'on':'' }}">
        {{ $label }}<span class="c">{{ $counts[$key] }}</span></a>
      @endforeach
    </div>
  </div>

  <div class="ad-table-wrap">
    <table class="ad-table">
      <thead>
        <tr><th>Student</th><th>Amount</th><th>Reference</th><th>Proof</th><th>Submitted</th><th>Action</th></tr>
      </thead>
      <tbody>
        @forelse($requests as $r)
        <tr>
          <td>
            <strong>{{ $r->user->name ?? '—' }}</strong>
            <div style="font-size:.72rem;color:var(--ad-muted);">{{ $r->user->email ?? '' }}</div>
            @if($r->note)<div style="font-size:.72rem;color:var(--ad-muted);margin-top:3px;"><i class="fas fa-comment-dots"></i> {{ $r->note }}</div>@endif
          </td>
          <td style="font-weight:700;color:var(--ad-accent);white-space:nowrap;">{{ \App\Support\Money::format($r->amount, $currency) }}</td>
          <td>@if($r->reference)<span class="lv-ref">{{ Str::limit($r->reference, 18) }}</span>@else<span style="color:var(--ad-muted);">—</span>@endif</td>
          <td>
            @if($r->proof_url)
              <a href="{{ $r->proof_url }}" target="_blank" rel="noopener" title="View payment screenshot">
                <img src="{{ $r->proof_url }}" alt="proof" style="width:46px;height:46px;object-fit:cover;border-radius:7px;border:1px solid var(--ad-border);">
              </a>
            @else
              <span style="color:var(--ad-muted);font-size:.74rem;">none</span>
            @endif
          </td>
          <td style="font-size:.74rem;color:var(--ad-muted);white-space:nowrap;">{{ $r->created_at->format('d M Y H:i') }}</td>
          <td style="min-width:200px;">
            @if($r->status === 'pending')
              <details class="lv-review">
                <summary><i class="fas fa-gavel"></i> Review</summary>
                <div class="lv-panel">
                  <div style="font-size:.74rem;color:var(--ad-muted);">Check the payment screenshot and confirm the crypto arrived before approving — this credits real money.</div>
                  @if($r->reference)<div style="font-size:.72rem;color:var(--ad-muted);">Ref: <span class="lv-ref">{{ $r->reference }}</span></div>@endif
                  @if($r->proof_url)<a href="{{ $r->proof_url }}" target="_blank" rel="noopener" class="btn-ad btn-ad-ghost btn-ad-sm"><i class="fas fa-image"></i> View proof screenshot</a>@endif
                  {{-- Approve --}}
                  <form method="POST" action="{{ route('admin.trading.live.deposits.approve', $r) }}"
                        onsubmit="return confirm('Approve and credit {{ \App\Support\Money::format($r->amount, $currency) }} to {{ $r->user->name }}?')">
                    @csrf
                    <div class="lv-acts">
                      <button class="btn-ad btn-ad-primary btn-ad-sm" type="submit"><i class="fas fa-check"></i> Approve &amp; credit</button>
                    </div>
                  </form>
                  {{-- Decline --}}
                  <form method="POST" action="{{ route('admin.trading.live.deposits.decline', $r) }}"
                        onsubmit="return confirm('Decline this deposit request?')">
                    @csrf
                    <textarea name="admin_note" rows="2" placeholder="Reason (optional, shown to student)"></textarea>
                    <div class="lv-acts">
                      <button class="btn-ad btn-ad-outline btn-ad-sm" type="submit" style="color:var(--ad-danger);border-color:var(--ad-danger);"><i class="fas fa-xmark"></i> Decline</button>
                    </div>
                  </form>
                </div>
              </details>
            @else
              <span class="badge-ad {{ $r->status==='approved'?'badge-success':'badge-closed' }}">{{ ucfirst($r->status) }}</span>
              <div style="font-size:.7rem;color:var(--ad-muted);margin-top:3px;">
                by {{ $r->reviewer->name ?? 'system' }} · {{ $r->reviewed_at?->format('d M H:i') }}
              </div>
              @if($r->admin_note)<div style="font-size:.7rem;color:var(--ad-muted);">“{{ $r->admin_note }}”</div>@endif
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;color:var(--ad-muted);padding:2.2rem;">No {{ $status }} deposit requests.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($requests->hasPages())<div style="padding:1rem 1.5rem;">{{ $requests->links() }}</div>@endif
</div>
@endsection
