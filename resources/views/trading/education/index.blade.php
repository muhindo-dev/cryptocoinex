@extends('layouts.trade-app')
@section('title', 'Education')

@push('styles')
<style>
  .ed-hero{display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap;margin-bottom:22px;}
  .ed-hero h1{font-size:1.4rem;font-weight:800;letter-spacing:-.01em;}
  .ed-hero p{font-size:.82rem;color:var(--text-muted);margin-top:4px;}
  .ed-prog{min-width:200px;}
  .ed-prog-bar{height:8px;border-radius:6px;background:var(--bg-hover);overflow:hidden;margin-top:7px;}
  .ed-prog-fill{height:100%;background:linear-gradient(90deg,var(--gold),#d97706);border-radius:6px;transition:width .4s;}
  .ed-prog-meta{display:flex;justify-content:space-between;font-size:.66rem;color:var(--text-muted);font-weight:700;}

  .ed-sec-title{font-size:.7rem;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);font-weight:800;margin:22px 0 12px;}

  /* Recommended carousel */
  .ed-rail{display:flex;gap:14px;overflow-x:auto;padding-bottom:6px;scrollbar-width:thin;}
  .ed-rail::-webkit-scrollbar{height:6px;} .ed-rail::-webkit-scrollbar-thumb{background:var(--border);border-radius:6px;}
  .ed-rcard{flex:0 0 240px;background:var(--bg-surface);border:1px solid var(--border);border-radius:13px;overflow:hidden;
    text-decoration:none;transition:transform .15s,border-color .15s;}
  .ed-rcard:hover{transform:translateY(-3px);border-color:var(--border-focus);}
  .ed-thumb{position:relative;aspect-ratio:16/9;background:var(--bg-hover) center/cover no-repeat;}
  .ed-thumb::after{content:'';position:absolute;inset:0;background:linear-gradient(180deg,transparent 40%,rgba(7,9,14,.7));}
  .ed-play{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:42px;height:42px;border-radius:50%;
    background:rgba(0,0,0,.55);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.9rem;z-index:2;backdrop-filter:blur(2px);}
  .ed-dur{position:absolute;bottom:7px;right:8px;z-index:2;font-size:.6rem;font-weight:800;color:#fff;background:rgba(0,0,0,.65);padding:2px 6px;border-radius:5px;}
  .ed-rbody{padding:11px 13px;}
  .ed-rbody .t{font-size:.8rem;font-weight:700;line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}

  /* Category pills */
  .ed-cats{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px;}
  .ed-cat{display:inline-flex;align-items:center;gap:8px;padding:8px 14px;border-radius:10px;border:1px solid var(--border);
    background:var(--bg-surface);color:var(--text-muted);font-size:.76rem;font-weight:700;text-decoration:none;transition:.15s;}
  .ed-cat:hover{color:var(--text-primary);border-color:var(--border-focus);}
  .ed-cat.on{background:var(--gold-muted);color:var(--gold);border-color:rgba(245,158,11,.35);}
  .ed-cat .n{font-size:.6rem;opacity:.7;}

  /* Article grid */
  .ed-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;}
  .ed-acard{background:var(--bg-surface);border:1px solid var(--border);border-radius:13px;overflow:hidden;text-decoration:none;
    display:flex;flex-direction:column;transition:transform .15s,border-color .15s;}
  .ed-acard:hover{transform:translateY(-3px);border-color:var(--border-focus);}
  .ed-abody{padding:12px 13px;display:flex;flex-direction:column;gap:8px;flex:1;}
  .ed-abody .t{font-size:.82rem;font-weight:700;line-height:1.32;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
  .ed-ameta{display:flex;align-items:center;gap:8px;margin-top:auto;}
  .ed-chip{font-size:.58rem;font-weight:800;text-transform:uppercase;letter-spacing:.04em;padding:2px 7px;border-radius:5px;}
  .ed-read{font-size:.62rem;color:var(--text-muted);}
  .ed-done{margin-left:auto;color:var(--green);font-size:.74rem;}
</style>
@endpush

@section('content')
<div class="ed-hero">
  <div>
    <h1><i class="fas fa-graduation-cap" style="color:var(--gold);margin-right:6px;"></i> Trading Academy</h1>
    <p>A free, structured course — {{ $total }} lessons across {{ $categories->count() }} tracks. Learn at your own pace.</p>
  </div>
  <div class="ed-prog">
    <div class="ed-prog-meta"><span>Your progress</span><span>{{ $completed->count() }}/{{ $total }}</span></div>
    <div class="ed-prog-bar"><div class="ed-prog-fill" style="width:{{ $total>0 ? round($completed->count()/$total*100) : 0 }}%"></div></div>
  </div>
</div>

@if(!$activeCategory && $recommended->count())
  <div class="ed-sec-title">⭐ Recommended to start</div>
  <div class="ed-rail">
    @foreach($recommended as $a)
    <a class="ed-rcard" href="{{ route('trade.education.show', $a) }}">
      <div class="ed-thumb" style="background-image:url('{{ $a->thumbUrl() }}')">
        <span class="ed-play"><i class="fas fa-play"></i></span>
        @if($a->duration)<span class="ed-dur">{{ $a->duration }}</span>@endif
      </div>
      <div class="ed-rbody"><div class="t">{{ $a->title }}</div></div>
    </a>
    @endforeach
  </div>
@endif

<div class="ed-sec-title">Tracks</div>
<div class="ed-cats">
  <a class="ed-cat {{ !$activeCategory ? 'on' : '' }}" href="{{ route('trade.education.index') }}">
    <i class="fas fa-layer-group"></i> All
  </a>
  @foreach($categories as $cat)
    <a class="ed-cat {{ $activeCategory && $activeCategory->id===$cat->id ? 'on' : '' }}"
       href="{{ route('trade.education.index', ['category'=>$cat->slug]) }}" style="--c:{{ $cat->accent }}">
      <i class="fas {{ $cat->icon }}" style="color:{{ $cat->accent }}"></i> {{ $cat->name }}
      <span class="n">{{ $cat->articles_count }}</span>
    </a>
  @endforeach
</div>

@if($activeCategory)
  <div class="ed-sec-title">{{ $activeCategory->name }} — {{ $activeCategory->tagline }}</div>
@else
  <div class="ed-sec-title">All lessons</div>
@endif

<div class="ed-grid">
  @forelse($articles as $a)
  <a class="ed-acard" href="{{ route('trade.education.show', $a) }}">
    <div class="ed-thumb" style="background-image:url('{{ $a->thumbUrl() }}')">
      <span class="ed-play"><i class="fas fa-play"></i></span>
      @if($a->duration)<span class="ed-dur">{{ $a->duration }}</span>@endif
    </div>
    <div class="ed-abody">
      <div class="t">{{ $a->title }}</div>
      <div class="ed-ameta">
        <span class="ed-chip" style="background:color-mix(in srgb,{{ $a->levelColor() }} 16%,transparent);color:{{ $a->levelColor() }};">{{ ucfirst($a->level) }}</span>
        <span class="ed-read">{{ $a->read_minutes }} min</span>
        @if($completed->contains($a->id))<span class="ed-done"><i class="fas fa-circle-check"></i></span>@endif
      </div>
    </div>
  </a>
  @empty
  <div style="grid-column:1/-1;text-align:center;color:var(--text-muted);padding:40px;">No lessons in this track yet.</div>
  @endforelse
</div>
@endsection
