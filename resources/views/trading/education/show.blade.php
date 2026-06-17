@extends('layouts.trade-app')
@section('title', 'Education')

@push('styles')
<style>
  .ar-wrap{max-width:780px;margin:0 auto;}
  .ar-back{display:inline-flex;align-items:center;gap:7px;font-size:.76rem;color:var(--text-muted);text-decoration:none;font-weight:700;margin-bottom:14px;}
  .ar-back:hover{color:var(--gold);}
  .ar-cat{font-size:.66rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:var(--gold);}
  .ar-title{font-size:1.55rem;font-weight:800;letter-spacing:-.01em;margin:6px 0 10px;line-height:1.2;}
  .ar-meta{display:flex;align-items:center;gap:12px;font-size:.72rem;color:var(--text-muted);margin-bottom:18px;}
  .ar-chip{font-size:.6rem;font-weight:800;text-transform:uppercase;padding:2px 8px;border-radius:5px;}

  .ar-video{position:relative;aspect-ratio:16/9;border-radius:14px;overflow:hidden;background:#000;margin-bottom:22px;border:1px solid var(--border);}
  .ar-video .poster{position:absolute;inset:0;background-size:cover;background-position:center;cursor:pointer;}
  .ar-video .poster::after{content:'';position:absolute;inset:0;background:rgba(7,9,14,.35);}
  .ar-video .pbtn{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:64px;height:64px;border-radius:50%;
    background:rgba(245,158,11,.95);display:flex;align-items:center;justify-content:center;color:#0f172a;font-size:1.4rem;z-index:2;
    box-shadow:0 8px 30px rgba(245,158,11,.4);transition:transform .15s;}
  .ar-video .poster:hover .pbtn{transform:translate(-50%,-50%) scale(1.08);}
  .ar-video iframe{position:absolute;inset:0;width:100%;height:100%;border:0;}

  .ar-excerpt{font-size:1rem;line-height:1.65;color:var(--text-primary);margin-bottom:8px;}
  .ar-body{font-size:.92rem;line-height:1.75;color:var(--text-primary);}
  .ar-body h3{font-size:1.05rem;font-weight:800;margin:26px 0 8px;display:flex;align-items:baseline;gap:9px;}
  .ar-body h3 .num{color:var(--gold);font-size:.85rem;font-weight:800;}
  .ar-body p{margin:8px 0;color:var(--text-primary);}
  .ar-body ul{margin:8px 0 8px 4px;padding:0;list-style:none;}
  .ar-body li{position:relative;padding-left:22px;margin:6px 0;color:var(--text-primary);}
  .ar-body li::before{content:'';position:absolute;left:4px;top:9px;width:6px;height:6px;border-radius:50%;background:var(--gold);}

  .ar-actions{display:flex;gap:12px;align-items:center;margin:28px 0;padding-top:20px;border-top:1px solid var(--border);flex-wrap:wrap;}
  .ar-done-btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:10px;padding:11px 20px;font-size:.82rem;font-weight:800;cursor:pointer;transition:.15s;}
  .ar-done-btn.todo{background:var(--green);color:#04130c;}
  .ar-done-btn.done{background:var(--green-muted);color:var(--green);border:1px solid rgba(0,201,123,.3);}
  .ar-next{margin-left:auto;display:inline-flex;align-items:center;gap:8px;color:var(--gold);font-weight:800;font-size:.82rem;text-decoration:none;}

  .ar-related{margin-top:26px;}
  .ar-rel-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;}
  .ar-rel{display:flex;gap:10px;align-items:center;background:var(--bg-surface);border:1px solid var(--border);border-radius:11px;padding:9px;text-decoration:none;transition:.15s;}
  .ar-rel:hover{border-color:var(--border-focus);}
  .ar-rel img{width:64px;height:40px;object-fit:cover;border-radius:7px;flex-shrink:0;background:var(--bg-hover);}
  .ar-rel .t{font-size:.72rem;font-weight:700;line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
</style>
@endpush

@section('content')
<div class="ar-wrap">
  <a class="ar-back" href="{{ route('trade.education.index', ['category'=>$article->category->slug]) }}">
    <i class="fas fa-arrow-left"></i> {{ $article->category->name }}
  </a>

  <div class="ar-cat">{{ $article->category->name }}</div>
  <h1 class="ar-title">{{ $article->title }}</h1>
  <div class="ar-meta">
    <span class="ar-chip" style="background:color-mix(in srgb,{{ $article->levelColor() }} 16%,transparent);color:{{ $article->levelColor() }};">{{ ucfirst($article->level) }}</span>
    <span><i class="fas fa-clock"></i> {{ $article->read_minutes }} min read</span>
    @if($article->duration)<span><i class="fas fa-play-circle"></i> {{ $article->duration }} video</span>@endif
  </div>

  @if($article->youtube_id)
  <div class="ar-video" id="arVideo" data-embed="{{ $article->embedUrl() }}">
    <div class="poster" style="background-image:url('{{ $article->thumbUrl() }}')" onclick="playVideo()">
      <div class="pbtn"><i class="fas fa-play"></i></div>
    </div>
  </div>
  @endif

  @if($article->excerpt)<p class="ar-excerpt">{{ $article->excerpt }}</p>@endif

  <div class="ar-body">
    @foreach($sections as $i => $s)
      @if($s['heading'])<h3><span class="num">{{ $i+1 }}.</span> {{ $s['heading'] }}</h3>@endif
      {!! $s['html'] !!}
    @endforeach
  </div>

  <div class="ar-actions">
    <button class="ar-done-btn {{ $isCompleted ? 'done' : 'todo' }}" id="arDone" data-done="{{ $isCompleted ? '1':'0' }}"
            onclick="toggleDone(this)">
      <i class="fas {{ $isCompleted ? 'fa-circle-check' : 'fa-check' }}"></i>
      <span>{{ $isCompleted ? 'Completed' : 'Mark as complete' }}</span>
    </button>
    @if($next)
    <a class="ar-next" href="{{ route('trade.education.show', $next) }}">Next lesson <i class="fas fa-arrow-right"></i></a>
    @endif
  </div>

  @if($related->count())
  <div class="ar-related">
    <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);font-weight:800;margin-bottom:12px;">More in {{ $article->category->name }}</div>
    <div class="ar-rel-grid">
      @foreach($related as $r)
      <a class="ar-rel" href="{{ route('trade.education.show', $r) }}">
        <img src="{{ $r->thumbUrl() }}" alt="" loading="lazy">
        <div class="t">{{ $r->title }}</div>
      </a>
      @endforeach
    </div>
  </div>
  @endif
</div>

@push('scripts')
<script>
function playVideo(){
  var v = document.getElementById('arVideo');
  var f = document.createElement('iframe');
  f.src = v.dataset.embed + '&autoplay=1';
  f.allow = 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture';
  f.allowFullscreen = true;
  v.innerHTML = ''; v.appendChild(f);
}
function toggleDone(btn){
  fetch('{{ route('trade.education.complete', $article) }}', {
    method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}
  }).then(r=>r.json()).then(d=>{
    if(d.completed){ btn.className='ar-done-btn done'; btn.querySelector('span').textContent='Completed'; btn.querySelector('i').className='fas fa-circle-check'; }
    else { btn.className='ar-done-btn todo'; btn.querySelector('span').textContent='Mark as complete'; btn.querySelector('i').className='fas fa-check'; }
  }).catch(()=>{});
}
</script>
@endpush
@endsection
