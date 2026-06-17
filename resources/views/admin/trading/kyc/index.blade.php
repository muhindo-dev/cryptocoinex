@extends('layouts.admin')
@section('title', 'Identity Verifications')

@push('styles')
<style>
  .kv-tabs{display:flex;gap:6px;flex-wrap:wrap;}
  .kv-tab{padding:7px 14px;border-radius:8px;font-size:.78rem;font-weight:700;text-decoration:none;border:1px solid var(--ad-border);color:var(--ad-muted);}
  .kv-tab.on{background:var(--ad-accent);border-color:var(--ad-accent);color:#1a1206;}
  .kv-tab .c{opacity:.7;margin-left:4px;}
  .kv-doc{width:52px;height:52px;border-radius:8px;object-fit:cover;border:1px solid var(--ad-border);}
  .kv-review summary{list-style:none;cursor:pointer;display:inline-flex;align-items:center;gap:6px;font-size:.74rem;font-weight:700;color:var(--ad-accent);}
  .kv-review summary::-webkit-details-marker{display:none;}
  .kv-panel{margin-top:10px;padding:13px;border:1px solid var(--ad-border);border-radius:10px;background:rgba(255,255,255,.02);max-width:480px;}
  .kv-panel textarea{width:100%;background:var(--ad-bg);border:1px solid var(--ad-border);border-radius:8px;padding:9px 11px;color:var(--ad-text,#f4f7fc);font-size:.82rem;font-family:inherit;}
  .kv-field{display:flex;justify-content:space-between;font-size:.78rem;padding:5px 0;border-bottom:1px solid var(--ad-border);}
  .kv-field .l{color:var(--ad-muted);}
  .kv-acts{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px;}
</style>
@endpush

@section('content')
<div class="ad-page-header">
  <div>
    <h1>Identity Verifications</h1>
    <div class="ad-breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span>
      <a href="{{ route('admin.trading.live.overview') }}">Live Account</a> <span>/</span> KYC
    </div>
  </div>
</div>

<div class="ad-card">
  <div class="ad-card-header" style="justify-content:flex-start;">
    <div class="kv-tabs">
      @foreach(['pending'=>'Pending','approved'=>'Approved','declined'=>'Declined','resubmit'=>'Redo'] as $key=>$label)
      <a href="{{ route('admin.trading.kyc.index', ['status'=>$key]) }}" class="kv-tab {{ $status===$key?'on':'' }}">
        {{ $label }}<span class="c">{{ $counts[$key] }}</span></a>
      @endforeach
    </div>
  </div>

  <div class="ad-table-wrap">
    <table class="ad-table">
      <thead><tr><th>Applicant</th><th>Document</th><th>Photo</th><th>Submitted</th><th>Action</th></tr></thead>
      <tbody>
        @forelse($submissions as $s)
        <tr>
          <td>
            <strong>{{ $s->user->name ?? '—' }}</strong>
            <div style="font-size:.72rem;color:var(--ad-muted);">{{ $s->user->email ?? '' }}</div>
            <div style="font-size:.72rem;color:var(--ad-muted);">Legal name: {{ $s->full_name }}</div>
          </td>
          <td>
            <div style="font-weight:600;">{{ $s->document_label }}</div>
            <div style="font-size:.72rem;color:var(--ad-muted);font-family:ui-monospace,monospace;">{{ $s->document_number }}</div>
          </td>
          <td>
            <a href="{{ route('admin.trading.kyc.document', $s) }}" target="_blank" rel="noopener" title="View document">
              <img src="{{ route('admin.trading.kyc.document', $s) }}" class="kv-doc" alt="doc"
                   onerror="this.outerHTML='<a href=\'{{ route('admin.trading.kyc.document', $s) }}\' target=_blank style=color:var(--ad-accent);font-size:.74rem>Open file</a>'">
            </a>
          </td>
          <td style="font-size:.74rem;color:var(--ad-muted);white-space:nowrap;">{{ $s->created_at->format('d M Y H:i') }}</td>
          <td style="min-width:200px;">
            @if($s->status === 'pending')
              <details class="kv-review">
                <summary><i class="fas fa-gavel"></i> Review</summary>
                <div class="kv-panel">
                  <div class="kv-field"><span class="l">Legal name</span><span>{{ $s->full_name }}</span></div>
                  <div class="kv-field"><span class="l">Document</span><span>{{ $s->document_label }} · {{ $s->document_number }}</span></div>
                  @if($s->message)<div class="kv-field"><span class="l">Message</span><span>{{ $s->message }}</span></div>@endif
                  <a href="{{ route('admin.trading.kyc.document', $s) }}" target="_blank" rel="noopener" class="btn-ad btn-ad-ghost btn-ad-sm" style="margin-top:10px;"><i class="fas fa-image"></i> View full document</a>

                  <form method="POST" action="{{ route('admin.trading.kyc.approve', $s) }}" style="margin-top:10px;"
                        onsubmit="return confirm('Approve {{ $s->user->name }}? They will be able to use live features.')">
                    @csrf
                    <button class="btn-ad btn-ad-primary btn-ad-sm" type="submit"><i class="fas fa-check"></i> Approve</button>
                  </form>

                  <form method="POST" action="{{ route('admin.trading.kyc.redo', $s) }}" style="margin-top:10px;">
                    @csrf
                    <textarea name="admin_note" rows="2" placeholder="What should they redo? (shown to the member)"></textarea>
                    <div class="kv-acts">
                      <button class="btn-ad btn-ad-outline btn-ad-sm" type="submit"><i class="fas fa-rotate"></i> Ask to redo</button>
                    </div>
                  </form>

                  <form method="POST" action="{{ route('admin.trading.kyc.decline', $s) }}" style="margin-top:8px;">
                    @csrf
                    <textarea name="admin_note" rows="2" placeholder="Reason for declining (optional)"></textarea>
                    <div class="kv-acts">
                      <button class="btn-ad btn-ad-outline btn-ad-sm" type="submit" style="color:var(--ad-danger);border-color:var(--ad-danger);"><i class="fas fa-xmark"></i> Decline</button>
                    </div>
                  </form>
                </div>
              </details>
            @else
              <span class="badge-ad {{ ['approved'=>'badge-success','declined'=>'badge-closed','resubmit'=>'badge-high'][$s->status] ?? 'badge-info' }}">{{ ucfirst($s->status) }}</span>
              <div style="font-size:.7rem;color:var(--ad-muted);margin-top:3px;">
                by {{ $s->reviewer->name ?? 'system' }} · {{ $s->reviewed_at?->format('d M H:i') }}
              </div>
              @if($s->admin_note)<div style="font-size:.7rem;color:var(--ad-muted);">“{{ $s->admin_note }}”</div>@endif
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;color:var(--ad-muted);padding:2.2rem;">No {{ $status }} verifications.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($submissions->hasPages())<div style="padding:1rem 1.5rem;">{{ $submissions->links() }}</div>@endif
</div>
@endsection
