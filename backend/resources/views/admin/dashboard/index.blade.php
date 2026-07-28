@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

    {{-- Stat Cards --}}
    <div class="flex justify-start items-center gap-4">
        <div class="bg-white rounded-xl shadow p-5">
            <h6 class="text-sm text-gray-500 mb-1">Total Users</h6>
            <p class="text-2xl font-semibold text-gray-900">{{ number_format($totalUsers) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <h6 class="text-sm text-gray-500 mb-1">Active Users</h6>
            <p class="text-2xl font-semibold text-gray-900">{{ number_format($activeUsers) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <h6 class="text-sm text-gray-500 mb-1">New Registrations (30d)</h6>
            <p class="text-2xl font-semibold text-gray-900">{{ number_format($newRegistrations30d) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Registrations Over Time Chart --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow p-5">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-semibold text-gray-900">New Registrations Over Time</h2>
                <form method="GET">
                    <select name="range" onchange="this.form.submit()"
                        class="border border-gray-300 rounded-md text-sm px-2 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="7"  {{ request('range') == 7  ? 'selected' : '' }}>Last 7 days</option>
                        <option value="30" {{ request('range', 30) == 30 ? 'selected' : '' }}>Last 30 days</option>
                        <option value="90" {{ request('range') == 90 ? 'selected' : '' }}>Last 90 days</option>
                    </select>
                </form>
            </div>
            <canvas id="registrationsChart" height="120"></canvas>
        </div>

        {{-- Top Forums --}}
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="font-semibold text-gray-900 mb-3">Top Forums</h2>
            <ul class="divide-y divide-gray-100">
                @foreach ($topForums as $forum)
                    <li class="flex justify-between items-center py-2">
                        {{-- {{ route('admin.forums.show', $forum->slug) }} --}}
                        <a href=""
                           class="text-sm text-gray-700 hover:text-indigo-600">
                            {{ $forum->name }}
                        </a>
                        <span class="text-xs font-medium bg-indigo-100 text-indigo-700 rounded-full px-2 py-0.5">
                            {{ $forum->activity_score }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Users by City --}}
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="font-semibold text-gray-900 mb-3">Users by City</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2">City</th>
                            <th class="py-2 text-right">Users</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($usersByCity as $row)
                            <tr>
                                <td class="py-2 text-gray-700">{{ $row->name }}</td>
                                <td class="py-2 text-right text-gray-900">{{ number_format($row->student_data_count) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Users by School --}}
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="font-semibold text-gray-900 mb-3">Users by School</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2">School</th>
                            <th class="py-2 text-right">Users</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($usersBySchool as $row)
                            <tr>
                                <td class="py-2 text-gray-700">{{ $row->school->name }}</td>
                                <td class="py-2 text-right text-gray-900">{{ number_format($row->total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Export --}}
    <div class="flex justify-end">
        <a href="{{ route('admin.dashboard.export') }}"
           class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
            Export PDF Report
        </a>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('registrationsChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($registrationLabels),
            datasets: [{
                label: 'New Registrations',
                data: @json($registrationCounts),
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
</script>
@endpush
