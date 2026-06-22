@extends('layouts.admin')
@section('title', 'Live Withdrawals')

@push('styles')
<style>
  .lv-tabs{display:flex;gap:6px;flex-wrap:wrap;}
  .lv-tab{padding:7px 14px;border-radius:8px;font-size:.78rem;font-weight:700;text-decoration:none;border:1px solid var(--ad-border);
    color:var(--ad-muted);background:transparent;}
  .lv-tab.on{background:var(--ad-accent);border-color:var(--ad-accent);color:#1a1206;}
  .lv-tab .c{opacity:.7;margin-left:4px;}
  .lv-review summary{list-style:none;cursor:pointer;display:inline-flex;align-items:center;gap:6px;font-size:.74rem;font-weight:700;color:var(--ad-accent);}
  .lv-review summary::-webkit-details-marker{display:none;}
  .lv-panel{margin-top:10px;padding:12px;border:1px solid var(--ad-border);border-radius:10px;background:rgba(255,255,255,.02);
    display:flex;flex-direction:column;gap:9px;max-width:440px;}
  .lv-panel textarea,.lv-panel input[type=text]{width:100%;background:var(--ad-bg);border:1px solid var(--ad-border);border-radius:8px;
    padding:9px 11px;color:var(--ad-text,#f4f7fc);font-size:.82rem;font-family:inherit;}
  .lv-phone{font-weight:700;color:var(--ad-text,#f4f7fc);}
</style>
@endpush

@section('content')
<div class="ad-page-header">
  <div>
    <h1>Withdrawal Requests</h1>
    <div class="ad-breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span>
      <a href="{{ route('admin.trading.live.overview') }}">Live Account</a> <span>/</span> Withdrawals
    </div>
  </div>
</div>

<div class="ad-card">
  <div class="ad-card-header" style="justify-content:flex-start;">
    <div class="lv-tabs">
      @foreach(['pending'=>'Pending','approved'=>'Approved','declined'=>'Declined'] as $key=>$label)
      <a href="{{ route('admin.trading.live.withdrawals', ['status'=>$key]) }}" class="lv-tab {{ $status===$key?'on':'' }}">
        {{ $label }}<span class="c">{{ $counts[$key] }}</span></a>
      @endforeach
    </div>
  </div>

  <div class="ad-table-wrap">
    <table class="ad-table">
      <thead>
        <tr><th>Student</th><th>Amount</th><th>Send to</th><th>Submitted</th><th>Action</th></tr>
      </thead>
      <tbody>
        @forelse($requests as $r)
        <tr>
          <td>
            <strong>{{ $r->user->name ?? '—' }}</strong>
            <div style="font-size:.72rem;color:var(--ad-muted);">{{ $r->user->email ?? '' }}</div>
            @if($r->note)<div style="font-size:.72rem;color:var(--ad-muted);margin-top:3px;"><i class="fas fa-comment-dots"></i> {{ $r->note }}</div>@endif
          </td>
          <td style="font-weight:700;color:var(--ad-danger);white-space:nowrap;">{{ \App\Support\Money::format($r->amount, $currency) }}</td>
          <td style="max-width:220px;">
            <span class="lv-phone" style="font-family:ui-monospace,monospace;font-size:.74rem;word-break:break-all;">{{ $r->destination }}</span>
            @if($r->payout_network)<div style="font-size:.7rem;color:var(--ad-muted);">{{ $r->payout_network }}</div>@endif
          </td>
          <td style="font-size:.74rem;color:var(--ad-muted);white-space:nowrap;">{{ $r->created_at->format('d M Y H:i') }}</td>
          <td style="min-width:220px;">
            @if($r->status === 'pending')
              <details class="lv-review">
                <summary><i class="fas fa-gavel"></i> Process</summary>
                <div class="lv-panel">
                  <div style="font-size:.74rem;color:var(--ad-muted);">Send <strong>{{ \App\Support\Money::format($r->amount, $currency) }}</strong> in {{ $r->payout_network ?: 'USD' }} to <span class="lv-phone" style="font-family:ui-monospace,monospace;word-break:break-all;">{{ $r->destination }}</span>, then approve to record the debit.</div>
                  {{-- Approve --}}
                  <form method="POST" action="{{ route('admin.trading.live.withdrawals.approve', $r) }}"
                        onsubmit="return confirm('Confirm you have SENT {{ \App\Support\Money::format($r->amount, $currency) }} to this wallet address? This debits the student\'s balance.')">
                    @csrf
                    <input type="text" name="payout_reference" placeholder="Payout reference (optional)">
                    <button class="btn-ad btn-ad-primary btn-ad-sm" type="submit" style="margin-top:8px;"><i class="fas fa-check"></i> Mark paid &amp; debit</button>
                  </form>
                  {{-- Decline --}}
                  <form method="POST" action="{{ route('admin.trading.live.withdrawals.decline', $r) }}"
                        onsubmit="return confirm('Decline this withdrawal request? No funds are touched.')">
                    @csrf
                    <textarea name="admin_note" rows="2" placeholder="Reason (optional, shown to student)"></textarea>
                    <button class="btn-ad btn-ad-outline btn-ad-sm" type="submit" style="color:var(--ad-danger);border-color:var(--ad-danger);"><i class="fas fa-xmark"></i> Decline</button>
                  </form>
                </div>
              </details>
            @else
              <span class="badge-ad {{ $r->status==='approved'?'badge-success':'badge-closed' }}">{{ $r->status==='approved'?'Paid':'Declined' }}</span>
              <div style="font-size:.7rem;color:var(--ad-muted);margin-top:3px;">
                by {{ $r->reviewer->name ?? 'system' }} · {{ $r->reviewed_at?->format('d M H:i') }}
                @if($r->payout_reference) · ref {{ $r->payout_reference }}@endif
              </div>
              @if($r->admin_note)<div style="font-size:.7rem;color:var(--ad-muted);">“{{ $r->admin_note }}”</div>@endif
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;color:var(--ad-muted);padding:2.2rem;">No {{ $status }} withdrawal requests.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($requests->hasPages())<div style="padding:1rem 1.5rem;">{{ $requests->links() }}</div>@endif
</div>
@endsection
