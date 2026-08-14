@extends('layouts.master')

@section('title', 'Контролна табла')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

    {{-- Stat Cards --}}
    <div class="flex justify-start items-center gap-4">
        <div class="bg-white rounded-xl shadow p-5">
            <h6 class="text-sm text-gray-500 mb-1">Вкупно корисници</h6>
            <p class="text-2xl font-semibold text-gray-900">{{ number_format($totalUsers) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <h6 class="text-sm text-gray-500 mb-1">Активни корисници</h6>
            <p class="text-2xl font-semibold text-gray-900">{{ number_format($activeUsers) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <h6 class="text-sm text-gray-500 mb-1">Нови регистрации (30 дена)</h6>
            <p class="text-2xl font-semibold text-gray-900">{{ number_format($newRegistrations30d) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Registrations Over Time Chart --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow p-5">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-semibold text-gray-900">Нови регистрации низ време</h2>
                <form method="GET">
                    <select name="range" onchange="this.form.submit()"
                        class="border border-gray-300 rounded-md text-sm px-2 py-1 focus:outline-none focus:ring-2 focus:ring-my-purple">
                        <option value="7"  {{ request('range') == 7  ? 'selected' : '' }}>Последни 7 дена</option>
                        <option value="30" {{ request('range', 30) == 30 ? 'selected' : '' }}>Последни 30 дена</option>
                        <option value="90" {{ request('range') == 90 ? 'selected' : '' }}>Последни 90 дена</option>
                    </select>
                </form>
            </div>
            <canvas id="registrationsChart" height="120"></canvas>
        </div>

        {{-- Топ форуми --}}
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="font-semibold text-gray-900 mb-3">Топ форуми</h2>
            <ul class="divide-y divide-gray-100">
                @foreach ($topForums as $forum)
                    <li class="flex justify-between items-center py-2">
                        <a href="{{ route('forum.show', ["forum" => $forum->id]) }}"
                           class="text-sm text-gray-700 hover:text-my-purple">
                            {{ $forum->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Корисници по град --}}
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="font-semibold text-gray-900 mb-3">Корисници по град</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2">Град</th>
                            <th class="py-2 text-right">Корисници</th>
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

        {{-- Корисници по училиште --}}
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="font-semibold text-gray-900 mb-3">Корисници по училиште</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2">Училиште</th>
                            <th class="py-2 text-right">Корисници</th>
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
           class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-my-purple text-sm font-medium text-white hover:bg-my-purple/90">
            Извези PDF извештај
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
                label: 'Нови регистрации',
                data: @json($registrationCounts),
                borderColor: '#582FF5',
                backgroundColor: 'rgba(88,47,245,0.12)',
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
