@extends('layouts.trade-app')
@section('title', 'Trade Journal')

@push('styles')
<style>
  .j-tags{display:flex;flex-wrap:wrap;gap:7px;margin-bottom:18px;}
  .j-tag{padding:5px 12px;border-radius:20px;border:1px solid var(--border);background:var(--bg-surface);
    color:var(--text-muted);font-size:.68rem;font-weight:700;text-decoration:none;}
  .j-tag.on,.j-tag:hover{background:var(--gold-muted);color:var(--gold);border-color:rgba(245,158,11,.3);}
  .j-day{font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);font-weight:800;margin:18px 0 10px;}
  .j-entry{display:flex;gap:14px;background:var(--bg-surface);border:1px solid var(--border);border-radius:11px;padding:14px;margin-bottom:10px;}
  .j-ico{width:38px;height:38px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-weight:800;}
  .j-ico.up{background:var(--green-muted);color:var(--green);} .j-ico.down{background:var(--red-muted);color:var(--red);}
  .j-sym{font-weight:800;font-size:.85rem;}
  .j-meta{font-size:.66rem;color:var(--text-muted);margin-top:2px;}
  .j-note{font-size:.8rem;margin-top:8px;line-height:1.5;color:var(--text-primary);}
  .j-chips{display:flex;flex-wrap:wrap;gap:5px;margin-top:8px;}
  .j-chip{font-size:.6rem;padding:2px 8px;border-radius:5px;background:var(--blue-muted);color:var(--blue);font-weight:700;}
  .j-sentiment{font-size:.6rem;padding:2px 8px;border-radius:5px;background:var(--gold-muted);color:var(--gold);font-weight:700;}
  .j-pnl{margin-left:auto;text-align:right;font-variant-numeric:tabular-nums;font-weight:800;}
  .j-pnl.pos{color:var(--green);} .j-pnl.neg{color:var(--red);} .j-pnl.tie{color:var(--text-muted);}
  .j-edit{background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:.66rem;margin-top:6px;}
  .j-edit:hover{color:var(--gold);}
  .j-form{margin-top:8px;display:none;}
  .j-form.open{display:block;}
  .j-form textarea{width:100%;background:var(--bg-elevated);border:1px solid var(--border);border-radius:7px;color:var(--text-primary);
    padding:8px;font-size:.78rem;resize:vertical;min-height:54px;font-family:inherit;}
  .j-form .row{display:flex;gap:8px;margin-top:7px;align-items:center;}
  .j-form input,.j-form select{background:var(--bg-elevated);border:1px solid var(--border);border-radius:7px;color:var(--text-primary);padding:6px 9px;font-size:.74rem;}
  .j-save{background:var(--gold);color:#0f172a;border:none;border-radius:7px;padding:6px 14px;font-size:.74rem;font-weight:800;cursor:pointer;}
</style>
@endpush

@section('content')
@if($allTags->count())
<div class="j-tags">
  <a href="{{ route('trade.journal') }}" class="j-tag {{ !request('tag')?'on':'' }}">All</a>
  @foreach($allTags as $tag)
    <a href="{{ route('trade.journal', ['tag'=>$tag]) }}" class="j-tag {{ request('tag')===$tag?'on':'' }}">#{{ $tag }}</a>
  @endforeach
</div>
@endif

@forelse($grouped as $day => $entries)
  <div class="j-day">{{ \Illuminate\Support\Carbon::parse($day)->format('l, d M Y') }}</div>
  @foreach($entries as $t)
  @php $pnl = ((int)($t->payout_amount ?? 0)) - (int)$t->stake; @endphp
  <div class="j-entry">
    <div class="j-ico {{ $t->direction }}">{{ $t->direction==='up'?'▲':'▼' }}</div>
    <div style="flex:1;min-width:0;">
      <div style="display:flex;align-items:center;gap:10px;">
        <span class="j-sym">{{ $t->asset?->symbol }}</span>
        <span class="j-meta">{{ number_format($t->stake) }} USD · {{ strtoupper($t->direction) }} · {{ $t->settled_at?->format('H:i') }}</span>
        <span class="j-pnl {{ $pnl>0?'pos':($pnl<0?'neg':'tie') }}">{{ $pnl>0?'+':'' }}{{ number_format($pnl) }}</span>
      </div>
      @if($t->notes)<div class="j-note">{{ $t->notes }}</div>@endif
      <div class="j-chips">
        @foreach(($t->tags ?? []) as $tg)<span class="j-chip">#{{ $tg }}</span>@endforeach
        @if($t->sentiment)<span class="j-sentiment">{{ $t->sentiment }}</span>@endif
      </div>
      <button class="j-edit" onclick="document.getElementById('jf-{{ $t->id }}').classList.toggle('open')">
        <i class="fas fa-pen"></i> Edit note
      </button>
      <form class="j-form" id="jf-{{ $t->id }}" data-id="{{ $t->id }}">
        <textarea name="notes" placeholder="What did you learn from this trade?">{{ $t->notes }}</textarea>
        <div class="row">
          <input type="text" name="tags" placeholder="tags, comma, separated" value="{{ implode(', ', $t->tags ?? []) }}">
          <select name="sentiment">
            <option value="">Sentiment…</option>
            @foreach(['confident','unsure','fomo'] as $s)<option value="{{ $s }}" {{ $t->sentiment===$s?'selected':'' }}>{{ ucfirst($s) }}</option>@endforeach
          </select>
          <button type="submit" class="j-save">Save</button>
        </div>
      </form>
    </div>
  </div>
  @endforeach
@empty
  <div class="ta-card" style="text-align:center;color:var(--text-muted);padding:40px;">
    <i class="fas fa-book" style="font-size:1.8rem;margin-bottom:10px;display:block;color:var(--text-dim);"></i>
    No journalled trades yet. After a trade settles, add a note to reflect on it.
  </div>
@endforelse

@if($trades->hasPages())<div style="margin-top:16px;">{{ $trades->links() }}</div>@endif

@push('scripts')
<script>
document.querySelectorAll('.j-form').forEach(form => {
  form.addEventListener('submit', async e => {
    e.preventDefault();
    const id = form.dataset.id;
    const tags = form.tags.value.split(',').map(s=>s.trim()).filter(Boolean);
    const body = { notes: form.notes.value || null, tags, sentiment: form.sentiment.value || null };
    const r = await fetch(`{{ url('trade') }}/${id}/note`, {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
      body: JSON.stringify(body)
    });
    if (r.ok) location.reload(); else alert('Could not save note.');
  });
});
</script>
@endpush
@endsection
