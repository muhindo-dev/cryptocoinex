<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'DevRoots Academy — Empowering IT innovators in Masaka with practical, hands-on tech education.')">

    <title>@yield('title', 'DevRoots Academy')</title>

    {{-- Bootstrap 5.3 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Font Awesome 6 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- Site Stylesheet (must come after Bootstrap to override variables) --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    {{-- Page-specific styles --}}
    @stack('styles')
</head>
<body>

    {{-- ===== HEADER ===== --}}
    @include('frontend.partials.header')

    {{-- ===== PAGE CONTENT ===== --}}
    <main>
        @yield('content')
    </main>

    {{-- ===== LIVE CHAT WIDGET ===== --}}
    <div id="live-chat">
        <button class="chat-btn" aria-label="Open support chat">
            <i class="fas fa-comments"></i>
        </button>
        <div class="chat-box" style="display:none;">
            <div class="chat-header">
                <i class="fas fa-headset me-2"></i> DevRoots Support
            </div>
            <div class="chat-messages" id="chat-messages">
                <p>Hello 👋 How can we help you?</p>
            </div>
            <input type="text" id="chat-input" placeholder="Type a message…">
            <button id="send-btn">Send</button>
        </div>
    </div>

    {{-- ===== BACK TO TOP ===== --}}
    <button id="back-to-top" aria-label="Back to top">
        <i class="fas fa-arrow-up"></i>
    </button>

    {{-- ===== FOOTER ===== --}}
    @include('frontend.partials.footer')

    {{-- Bootstrap JS bundle (includes Popper) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Site JS --}}
    <script src="{{ asset('js/main.js') }}"></script>

    {{-- Page-specific scripts --}}
    @stack('scripts')

</body>
</html>
