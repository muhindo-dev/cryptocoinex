@extends('layouts.admin')
@section('title', $user->name)

@section('content')

<div class="ad-page-header">
  <div>
    <h1>{{ $user->name }}</h1>
    <div class="ad-breadcrumb"><a href="{{ route('admin.users.index') }}">Users</a> <span>/</span> {{ $user->name }}</div>
  </div>
  <a href="{{ route('admin.users.edit', $user) }}" class="btn-ad btn-ad-primary"><i class="fas fa-pen"></i> Edit</a>
</div>

<div style="display:grid;grid-template-columns:280px 1fr;gap:20px;">
  <div class="ad-card">
    <div class="ad-card-body" style="text-align:center;">
      <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,var(--ad-primary),var(--ad-accent));display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:1.5rem;font-weight:700;color:#fff;">
        {{ strtoupper(substr($user->name,0,2)) }}
      </div>
      <h3 style="font-size:1rem;font-weight:700;">{{ $user->name }}</h3>
      <div style="margin:8px 0;">
        <span class="badge-ad {{ $user->role==='admin'?'badge-high':'badge-active' }}">{{ $user->role_label }}</span>
      </div>
      <p style="font-size:0.8125rem;color:var(--ad-muted);">{{ $user->email }}</p>
      @if($user->phone)<p style="font-size:0.8125rem;margin-top:4px;">{{ $user->phone }}</p>@endif
      @if($user->bio)
      <p style="font-size:0.8rem;color:var(--ad-muted);margin-top:10px;line-height:1.5;">{{ $user->bio }}</p>
      @endif
    </div>
  </div>

  <div class="ad-card">
    <div class="ad-card-header"><span class="ad-card-title">Trading Wallet</span></div>
    <div class="ad-card-body">
      @if($user->tradingWallet)
        <div style="display:flex;gap:24px;flex-wrap:wrap;">
          <div><div style="font-size:1.4rem;font-weight:800;color:var(--ad-accent);">{{ number_format($user->tradingWallet->balance) }}</div><div style="font-size:.7rem;color:var(--ad-muted);">Balance (USD)</div></div>
          <div><div style="font-size:1.4rem;font-weight:800;">{{ number_format($user->tradingWallet->peak_balance) }}</div><div style="font-size:.7rem;color:var(--ad-muted);">Peak Balance</div></div>
          <div><div style="font-size:1.4rem;font-weight:800;">{{ $user->trades()->count() }}</div><div style="font-size:.7rem;color:var(--ad-muted);">Trades</div></div>
        </div>
      @else
        <p style="color:var(--ad-muted);">No trading wallet yet.</p>
      @endif
    </div>
  </div>
</div>
@endsection
