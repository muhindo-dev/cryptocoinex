@extends('layouts.auth')
@section('title', 'Verify your email')

@section('form')
<div style="width:62px;height:62px;border-radius:16px;background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.25);
  display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:1.5rem;margin-bottom:20px;">
  <i class="fas fa-envelope-circle-check"></i>
</div>
<h2 class="af-title">Verify your email</h2>
<p class="af-sub">Thanks for signing up! We've emailed you a verification link. Click it to confirm your
  address. Didn't get it? We'll happily send another.</p>

@if(session('status') == 'verification-link-sent')
<div class="a-alert ok"><i class="fas fa-circle-check"></i><span>A new verification link has been sent to your email address.</span></div>
@endif

<form method="POST" action="{{ route('verification.send') }}">
  @csrf
  <button type="submit" class="a-btn"><i class="fas fa-paper-plane"></i> Resend verification email</button>
</form>

<form method="POST" action="{{ route('admin.logout') }}" style="margin-top:14px;text-align:center;">
  @csrf
  <button type="submit" class="a-back" style="background:none;border:none;cursor:pointer;">
    <i class="fas fa-right-from-bracket"></i> Sign out
  </button>
</form>
@endsection
