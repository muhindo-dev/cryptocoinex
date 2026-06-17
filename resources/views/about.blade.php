@extends('layouts.frontend')

@section('title', 'About Us - DevRoots Academy')

@section('content')
<div class="container py-10">
    <h1 class="text-3xl font-bold mb-6">About DevRoots Academy</h1>
    <p class="mb-4">DevRoots Academy is dedicated to empowering IT innovators in Masaka and beyond. We offer hands-on training in programming, IT support, software development, cybersecurity, UI/UX design, and more. Our mission is to bridge the digital skills gap and foster a community of lifelong learners.</p>
    <h2 class="text-2xl font-semibold mt-8 mb-4">Our Vision</h2>
    <p class="mb-4">To be the leading hub for tech education and innovation in the region, nurturing talent and driving positive change through technology.</p>
    <h2 class="text-2xl font-semibold mt-8 mb-4">Our Values</h2>
    <ul class="list-disc pl-6 mb-4">
        <li>Excellence in teaching and learning</li>
        <li>Inclusivity and diversity</li>
        <li>Community engagement</li>
        <li>Innovation and creativity</li>
        <li>Integrity and professionalism</li>
    </ul>
    <h2 class="text-2xl font-semibold mt-8 mb-4">Contact Us</h2>
    <p>Email: info@devrootsacademy.com</p>
    <p>Phone: +256 700 000 000</p>
</div>
@endsection
@push('scripts')
    <script src="{{ asset('js/main.js') }}"></script>
@endpush
