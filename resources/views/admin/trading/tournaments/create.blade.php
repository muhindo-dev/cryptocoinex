@extends('layouts.admin')
@section('title', 'New Tournament')

@section('content')
<div class="ad-page-header">
  <div><h1>New Tournament</h1>
    <div class="ad-breadcrumb"><a href="{{ route('admin.trading.tournaments.index') }}">Tournaments</a> <span>/</span> New</div>
  </div>
</div>

<div class="ad-card" style="max-width:640px;">
  <div class="ad-card-body">
    <form method="POST" action="{{ route('admin.trading.tournaments.store') }}">
      @csrf
      <div class="ad-form-group">
        <label>Name <span class="req">*</span></label>
        <input class="ad-input" name="name" value="{{ old('name', '1-Hour BTC Sprint') }}" required>
      </div>
      <div class="ad-form-group">
        <label>Description</label>
        <textarea class="ad-input" name="description" rows="2">{{ old('description') }}</textarea>
      </div>
      <div class="ad-form-group">
        <label>Asset</label>
        <select class="ad-select" name="asset_id">
          <option value="">Any asset</option>
          @foreach($assets as $a)<option value="{{ $a->id }}" {{ old('asset_id')==$a->id?'selected':'' }}>{{ $a->symbol }} — {{ $a->name }}</option>@endforeach
        </select>
      </div>
      <div class="ad-form-group">
        <label>Starting balance (USD) <span class="req">*</span></label>
        <input class="ad-input" type="number" name="starting_balance" value="{{ old('starting_balance', 5000) }}" min="100" required>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div class="ad-form-group">
          <label>Starts at <span class="req">*</span></label>
          <input class="ad-input" type="datetime-local" name="starts_at" value="{{ old('starts_at', now()->format('Y-m-d\TH:i')) }}" required>
        </div>
        <div class="ad-form-group">
          <label>Ends at <span class="req">*</span></label>
          <input class="ad-input" type="datetime-local" name="ends_at" value="{{ old('ends_at', now()->addHour()->format('Y-m-d\TH:i')) }}" required>
        </div>
      </div>
      <div style="margin-top:16px;display:flex;gap:10px;">
        <button class="btn-ad btn-ad-primary" type="submit">Create Tournament</button>
        <a href="{{ route('admin.trading.tournaments.index') }}" class="btn-ad btn-ad-ghost">Cancel</a>
      </div>
    </form>
  </div>
</div>
@endsection
