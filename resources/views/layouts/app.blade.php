@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<!-- STAT CARDS -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">

    <x-stat-card title="Total Students" value="120" color="bg-blue-600" />
    <x-stat-card title="Pending" value="18" color="bg-yellow-500" />
    <x-stat-card title="Active" value="74" color="bg-green-600" />
    <x-stat-card title="Completed" value="22" color="bg-purple-600" />
    <x-stat-card title="Dropped" value="6" color="bg-red-600" />

</div>

<!-- CHARTS + ACTIVITY -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- ENROLLMENT CHART PLACEHOLDER -->
    <div class="bg-white rounded-xl shadow p-6 lg:col-span-2">
        <h2 class="text-lg font-semibold mb-4">Student Enrollment Overview</h2>

        <div class="h-64 flex items-center justify-center text-gray-400">
            Chart goes here
        </div>
    </div>

    <!-- RECENT ACTIVITY -->
    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Recent Activity</h2>

        <ul class="space-y-3 text-sm">
            <li>🟢 John Doe registered</li>
            <li>🎓 Mary completed Programming</li>
            <li>⚠ Peter dropped UI/UX</li>
            <li>💰 Payment received (UGX 300k)</li>
        </ul>
    </div>

</div>

<!-- QUICK ACTIONS -->
<div class="mt-8 bg-white rounded-xl shadow p-6">
    <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>

    <div class="flex flex-wrap gap-4">
        <a href="#" class="px-5 py-2 bg-red-600 text-white rounded hover:bg-red-700">
            + Add Student
        </a>
        <a href="#" class="px-5 py-2 bg-gray-800 text-white rounded hover:bg-gray-900">
            + Add Course
        </a>
        <a href="#" class="px-5 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Generate Report
        </a>
    </div>
</div>

@endsection
