@extends('layouts.master')

@section('title', 'Контролна табла')

@section('content')
    <x-admin.page-header title="Контролна табла" subtitle="Што чека преглед и како расте заедницата.">
        @can('export dashboard')
            <a href="{{ route('admin.dashboard.export', request()->only('range')) }}"
                class="inline-flex items-center gap-2 rounded-md bg-my-purple px-4 py-2 text-sm font-medium text-white hover:bg-my-purple/90">
                Извези PDF извештај
            </a>
        @endcan
    </x-admin.page-header>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @can('view reports')
            <a href="{{ route('report.index') }}"
                class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:border-my-purple/40">
                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500">Пријави</p>
                <p class="text-3xl font-semibold {{ $pendingReports > 0 ? 'text-my-purple' : 'text-gray-900' }}">
                    {{ number_format($pendingReports) }}</p>
                <p class="mt-3 text-xs text-gray-400">Чекаат преглед</p>
            </a>
        @endcan
        @can('view appeals')
            <a href="{{ route('appeal.index') }}"
                class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:border-my-purple/40">
                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500">Жалби</p>
                <p class="text-3xl font-semibold {{ $pendingAppeals > 0 ? 'text-my-purple' : 'text-gray-900' }}">
                    {{ number_format($pendingAppeals) }}</p>
                <p class="mt-3 text-xs text-gray-400">Чекаат одлука</p>
            </a>
        @endcan
        @can('view feedback')
            <a href="{{ route('feedback.index') }}"
                class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:border-my-purple/40">
                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500">Мислења</p>
                <p class="text-3xl font-semibold {{ $unreviewedFeedback > 0 ? 'text-my-purple' : 'text-gray-900' }}">
                    {{ number_format($unreviewedFeedback) }}</p>
                <p class="mt-3 text-xs text-gray-400">Нови оценки</p>
            </a>
        @endcan
        @can('view sanctions')
            <a href="{{ route('sanction.index') }}"
                class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:border-my-purple/40">
                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500">Активни забрани</p>
                <p class="text-3xl font-semibold text-gray-900">{{ number_format($activeBans) }}</p>
                <p class="mt-3 text-xs text-gray-400">Моментално банирани</p>
            </a>
        @endcan
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h6 class="mb-1 text-sm text-gray-500">Вкупно корисници</h6>
            <p class="text-2xl font-semibold text-gray-900">{{ number_format($totalUsers) }}</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h6 class="mb-1 text-sm text-gray-500">Активни корисници</h6>
            <p class="text-2xl font-semibold text-gray-900">{{ number_format($activeUsers) }}</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h6 class="mb-1 text-sm text-gray-500">Нови регистрации (30 дена)</h6>
            <p class="text-2xl font-semibold text-gray-900">{{ number_format($newRegistrations30d) }}</p>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="rounded-xl bg-white p-5 shadow-sm lg:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Нови регистрации низ време</h2>
                <form method="GET">
                    <select name="range" onchange="this.form.submit()"
                        class="rounded-md border border-gray-300 px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-my-purple">
                        <option value="7" @selected(request('range') == 7)>Последни 7 дена</option>
                        <option value="30" @selected(request('range', 30) == 30)>Последни 30 дена</option>
                        <option value="90" @selected(request('range') == 90)>Последни 90 дена</option>
                    </select>
                </form>
            </div>
            <canvas id="registrationsChart" height="120"></canvas>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h2 class="mb-3 font-semibold text-gray-900">Топ форуми</h2>
            <ul class="divide-y divide-gray-100">
                @forelse ($topForums as $forum)
                    <li class="flex items-center justify-between py-2">
                        <a href="{{ route('forum.show', ['forum' => $forum->id]) }}"
                            class="text-sm text-gray-700 hover:text-my-purple">
                            {{ $forum->name }}
                        </a>
                        <span class="text-xs text-gray-400">{{ number_format($forum->threads_count) }} дискусии</span>
                    </li>
                @empty
                    <li class="py-2 text-sm text-gray-400">Нема форуми.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h2 class="mb-3 font-semibold text-gray-900">Корисници по град</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b text-left text-gray-500">
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

        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h2 class="mb-3 font-semibold text-gray-900">Корисници по училиште</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b text-left text-gray-500">
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
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
@endpush
