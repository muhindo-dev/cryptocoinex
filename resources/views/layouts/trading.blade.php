<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  @include('partials.sw-kill')
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Trading') — {{ config('app.name') }}</title>
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo-square.png') }}">
  <link rel="manifest" href="{{ asset('manifest.json') }}">
  <meta name="theme-color" content="#f59e0b">
  <script>
    window.CX_DEFAULT_THEME = '{{ auth()->user()->theme ?? 'dark' }}';
    window.CX_THEME_SAVE = { url: '{{ route('trade.theme') }}', token: '{{ csrf_token() }}' };
  </script>
  <script src="{{ asset('js/theme.js') }}?v={{ filemtime(public_path('js/theme.js')) }}"></script>
  <link rel="stylesheet" href="{{ asset('vendor/fa/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('css/tokens.css') }}?v={{ filemtime(public_path('css/tokens.css')) }}">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; overflow: hidden; }
    body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
  </style>
  @stack('styles')
</head>
<body>
@yield('content')
<script src="{{ asset('vendor/js/jquery.min.js') }}"></script>
<script>
  // Service worker disabled — actively unregister any old one and clear caches
  // (a previous cache-first worker caused stale assets / blank screens).
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.getRegistrations().then((rs) => rs.forEach((r) => r.unregister())).catch(() => {});
    if (window.caches) caches.keys().then((ks) => ks.forEach((k) => caches.delete(k))).catch(() => {});
  }
</script>
@stack('scripts')
{{-- Lift the chat bubble above the BUY/SELL bar on the trade screen --}}
@include('partials.tawk', ['tawkOffsetY' => 104])
</body>
</html>
