@extends('layouts.auth')
@section('title', 'Create account')

@push('styles')
<style>
  .a-steps{display:flex;gap:6px;margin-bottom:22px;}
  .a-step{flex:1;height:4px;border-radius:3px;background:var(--a-card);}
  .a-step.on{background:var(--gold);}
</style>
@endpush

@section('form')
<div class="a-steps"><div class="a-step on"></div><div class="a-step"></div><div class="a-step"></div></div>
<div class="af-eyebrow">Step 1 of 3 · Free forever</div>
<h2 class="af-title">Create your account</h2>
<p class="af-sub">Start with {{ number_format((int) \App\Models\Trading\TradingSetting::get('default_start_balance', 10000)) }} virtual USD — no card, no deposit, ever.</p>

@if($errors->any())
<div class="a-alert err"><i class="fas fa-circle-exclamation"></i>
  <div>Please fix the following:<ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
</div>
@endif

<form method="POST" action="{{ route('onboarding.store') }}">
  @csrf
  <div class="a-field">
    <label class="a-label" for="name">Full name</label>
    <div class="a-inwrap">
      <i class="fas fa-user"></i>
      <input class="a-input" id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Jane Trader" required autofocus>
    </div>
  </div>

  <div class="a-field">
    <label class="a-label" for="email">Email address</label>
    <div class="a-inwrap">
      <i class="fas fa-envelope"></i>
      <input class="a-input" id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="username">
    </div>
  </div>

  <div class="a-grid2">
    <div class="a-field">
      <label class="a-label" for="password">Password</label>
      <div class="a-inwrap">
        <i class="fas fa-lock"></i>
        <input class="a-input" id="password" type="password" name="password" placeholder="••••••••" required autocomplete="new-password" style="padding-right:44px;">
        <button type="button" class="a-eye" data-eye="password"><i class="fas fa-eye"></i></button>
      </div>
    </div>
    <div class="a-field">
      <label class="a-label" for="password_confirmation">Confirm</label>
      <div class="a-inwrap">
        <i class="fas fa-lock"></i>
        <input class="a-input" id="password_confirmation" type="password" name="password_confirmation" placeholder="••••••••" required autocomplete="new-password">
      </div>
    </div>
  </div>

  <button type="submit" class="a-btn"><i class="fas fa-rocket"></i> Create my free account</button>
</form>

<div class="a-alt">Already have an account? <a href="{{ url('/admin/login') }}">Sign in</a></div>
<div style="text-align:center;"><a href="{{ route('home') }}" class="a-back"><i class="fas fa-arrow-left"></i> Back to home</a></div>

<p style="text-align:center;font-size:.7rem;color:var(--a-dim);margin-top:18px;line-height:1.6;">
  By creating an account you agree to our
  <a href="{{ route('terms') }}" style="color:var(--a-mt);">Terms</a> &amp;
  <a href="{{ route('privacy') }}" style="color:var(--a-mt);">Privacy Policy</a>.
</p>
@endsection
