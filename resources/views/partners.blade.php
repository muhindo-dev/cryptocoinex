@extends('layouts.frontend')

@section('title', 'Our Partners - DevRoots Academy')

@section('content')
<div class="container py-10">
    <h1 class="text-3xl font-bold mb-6">Our Partners</h1>
    <p class="mb-4">We collaborate with industry leaders and organizations to provide the best learning opportunities and resources for our students. Our partners help us deliver high-quality education and real-world experiences.</p>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-8 mt-8">
        <div class="bg-white rounded shadow p-4 flex flex-col items-center">
            <img src="{{ asset('images/partners/butende.png') }}" alt="Butende" class="h-16 mb-2">
            <span class="font-semibold">Butende</span>
        </div>
        <div class="bg-white rounded shadow p-4 flex flex-col items-center">
            <img src="{{ asset('images/partners/mru.png') }}" alt="MRU" class="h-16 mb-2">
            <span class="font-semibold">MRU</span>
        </div>
        <div class="bg-white rounded shadow p-4 flex flex-col items-center">
            <img src="{{ asset('images/partners/mahipso.png') }}" alt="Mahipso" class="h-16 mb-2">
            <span class="font-semibold">Mahipso</span>
        </div>
        <div class="bg-white rounded shadow p-4 flex flex-col items-center">
            <img src="{{ asset('images/partners/adic.png') }}" alt="ADIC" class="h-16 mb-2">
            <span class="font-semibold">ADIC</span>
        </div>
        <div class="bg-white rounded shadow p-4 flex flex-col items-center">
            <img src="{{ asset('images/partners/masakacity.png') }}" alt="Masaka City" class="h-16 mb-2">
            <span class="font-semibold">Masaka City</span>
        </div>
        <div class="bg-white rounded shadow p-4 flex flex-col items-center">
            <img src="{{ asset('images/partners/nita.svg') }}" alt="NITA" class="h-16 mb-2">
            <span class="font-semibold">NITA</span>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script src="{{ asset('js/main.js') }}"></script>
@endpush
