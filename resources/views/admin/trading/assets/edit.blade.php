@extends('layouts.admin')
@section('title', 'Edit ' . $asset->symbol)

@section('content')
<div class="ad-page-header">
  <div>
    <h1>Edit Asset: {{ $asset->symbol }}</h1>
    <div class="ad-breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span>
      <a href="{{ route('admin.trading.assets.index') }}">Assets</a> <span>/</span> Edit
    </div>
  </div>
</div>

@if($errors->any())
<div class="alert-ad alert-danger" style="margin-bottom:1rem;">
  <ul style="margin:0;padding-left:1.2rem;">
    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
  </ul>
</div>
@endif

<form method="POST" action="{{ route('admin.trading.assets.update', $asset) }}">
  @csrf @method('PUT')
  @include('admin.trading.assets._form', ['asset' => $asset])
  <div style="margin-top:1.5rem;display:flex;gap:0.75rem;">
    <button type="submit" class="btn-ad btn-ad-primary">Save Changes</button>
    <a href="{{ route('admin.trading.assets.index') }}" class="btn-ad btn-ad-ghost">Cancel</a>
  </div>
</form>
@endsection
