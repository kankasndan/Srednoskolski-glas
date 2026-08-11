@extends('layouts.master')

@section('title', 'Жалби')

@section('content')
    <div class="p-6">

        <div class="max-w-6xl mx-auto">

            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Жалби на забрани</h1>
                    <p class="text-sm text-gray-500 mt-1">Прегледај и реши жалби поднесени од банирани членови.</p>
                </div>
                <div>
                    <span id="active-appeals-total"
                        class="inline-flex items-center px-3 py-1 rounded-full bg-my-purple/10 text-my-purple text-sm font-medium">
                        Вкупно жалби: {{ $appeals->total() }}
                    </span>

                    <span id="resolved-appeals-total" style="display: none;"
                        class="inline-flex items-center px-3 py-1 rounded-full bg-my-purple/10 text-my-purple text-sm font-medium">
                        Вкупно решени жалби: {{ $resolvedAppeals->total() }}
                    </span>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex gap-1 border-b border-gray-200 mb-6">
                <button type="button" data-tab="queue"
                    class="tab-btn px-4 py-2.5 text-sm font-medium border-b-2 border-my-purple text-my-purple">
                    Жалби
                </button>
                <button type="button" data-tab="history"
                    class="tab-btn px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                    Историја
                </button>
            </div>

            <!-- ===================== QUEUE TAB ===================== -->
            <div id="tab-queue" class="tab-panel">


                @if (session('success'))
                    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Appeals Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 uppercase text-xs tracking-wide">
                            <tr>
                                <th class="text-left px-6 py-3">Член</th>
                                <th class="text-left px-6 py-3">Причина за забрана</th>
                                <th class="text-left px-6 py-3">Тип на забрана</th>
                                <th class="text-left px-6 py-3">Поднесена</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($appeals as $appeal)
                                <tr class="appeal-row hover:bg-gray-50 cursor-pointer" data-status="{{ $appeal->status }}"
                                    onclick="window.location.href='{{ route('appeal.show', ['appeal' => $appeal->id]) }}'">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $appeal->user->imageUrl }}" alt=""
                                                class="w-8 h-8 rounded-full">
                                            <div>
                                                <p class="font-medium text-gray-800">{{ $appeal->user->username }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">{{ $appeal->sanction->reason }}</td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="text-xs font-medium px-2 py-1 rounded-full {{ match ($appeal->sanction->type) {
                                                'warning' => 'bg-my-yellow/30 text-gray-800',
                                                'permanent_ban' => 'bg-red-100 text-red-700',
                                                '7-day' => 'bg-green-100 text-green-600',
                                                default => 'bg-gray-100 text-gray-600',
                                            } }}">
                                            {{ match ($appeal->sanction->type) {
                                                'warning' => 'Предупредување',
                                                'permanent_ban' => 'Трајна забрана',
                                                '7-day' => '7-дневна забрана',
                                                'custom' => 'Прилагодена',
                                                default => $appeal->sanction->type,
                                            } }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">{{ $appeal->created_at->diffForHumans() }}</td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-400 text-sm">
                                        Нема совпаѓачки жалби.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex justify-center mt-4">
                    <nav class="flex gap-1 text-sm">
                        @if ($appeals->onFirstPage())
                            <button disabled
                                class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-400 cursor-not-allowed">
                                Претходна
                            </button>
                        @else
                            <a href="{{ $appeals->previousPageUrl() }}"
                                class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">
                                Претходна
                            </a>
                        @endif

                        @if ($appeals->hasMorePages())
                            <a href="{{ $appeals->nextPageUrl() }}"
                                class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">
                                Следна
                            </a>
                        @else
                            <button disabled
                                class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-400 cursor-not-allowed">
                                Следна
                            </button>
                        @endif
                    </nav>
                </div>
            </div>

            <!-- ===================== HISTORY TAB ===================== -->
            <div id="tab-history" class="tab-panel hidden">


                <!-- History Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 uppercase text-xs tracking-wide">
                            <tr>
                                <th class="text-left px-6 py-3">Член</th>
                                <th class="text-left px-6 py-3">Причина за забрана</th>
                                <th class="text-left px-6 py-3">Одлука</th>
                                <th class="text-left px-6 py-3">Решено од</th>
                                <th class="text-left px-6 py-3">Решено на</th>
                                <th class="text-right px-6 py-3">Акции</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($resolvedAppeals as $resolvedAppeal)
                                <tr class="history-row hover:bg-gray-50 cursor-pointer"
                                    onclick="window.location.href='{{ route('appeal.show', $resolvedAppeal->id) }}'">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-8 h-8 rounded-full bg-slate-500 flex items-center justify-center text-white text-xs font-semibold">
                                                {{ strtoupper(substr($resolvedAppeal->user->username ?? '?', 0, 2)) }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-800">
                                                    {{ $resolvedAppeal->user->username ?? 'Непознато' }}</p>
                                                <p class="text-gray-400 text-xs">
                                                    {{ '@' . ($resolvedAppeal->user->handle ?? '—') }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">{{ $resolvedAppeal->sanction->reason ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($resolvedAppeal->status === 'accepted')
                                            <span
                                                class="px-2 py-0.5 rounded-full bg-green-100 text-green-800 text-xs font-medium">Accepted
                                                &amp; Unbanned</span>
                                        @else
                                            <span
                                                class="px-2 py-0.5 rounded-full bg-red-100 text-red-800 text-xs font-medium">Одбиена</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">
                                        {{ $resolvedAppeal->resolvedBy->username ?? 'Систем' }}</td>
                                    <td class="px-6 py-4 text-gray-500">
                                        {{ $resolvedAppeal->deleted_at?->diffForHumans() ?? '—' }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <button type="button"
                                            class="text-my-purple hover:text-my-purple font-medium text-sm"
                                            onclick="event.stopPropagation(); window.location.href='{{ route('appeal.show', $resolvedAppeal->id) }}'">
                                            Види
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-400 text-sm">
                                        Нема решени жалби.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- History Pagination -->
                <div class="flex justify-center mt-4">
                    <nav class="flex gap-1 text-sm">
                        @if ($resolvedAppeals->onFirstPage())
                            <button disabled
                                class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-400 cursor-not-allowed">
                                Претходна
                            </button>
                        @else
                            <a href="{{ $resolvedAppeals->previousPageUrl() }}"
                                class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">
                                Претходна
                            </a>
                        @endif

                        @if ($resolvedAppeals->hasMorePages())
                            <a href="{{ $resolvedAppeals->nextPageUrl() }}"
                                class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">
                                Следна
                            </a>
                        @else
                            <button disabled
                                class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-400 cursor-not-allowed">
                                Следна
                            </button>
                        @endif
                    </nav>
                </div>

            </div>

        </div>

        @push('scripts-appeals')
            <script>
                document.addEventListener('DOMContentLoaded', function() {

                    // Server-side status filter (Queue tab)
                    const form = document.getElementById('filter-form');
                    const statusInput = document.getElementById('status-input');
                    const filterBtns = document.querySelectorAll('.filter-btn');

                    filterBtns.forEach(function(btn) {
                        btn.addEventListener('click', function(e) {
                            e.preventDefault();
                            statusInput.value = btn.getAttribute('data-filter');
                            form.submit();
                        });
                    });

                    // Server-side status filter (History tab)
                    const historyForm = document.getElementById('history-filter-form');
                    const historyStatusInput = document.getElementById('history-status-input');
                    const historyFilterBtns = document.querySelectorAll('.history-filter-btn');

                    historyFilterBtns.forEach(function(btn) {
                        btn.addEventListener('click', function(e) {
                            e.preventDefault();
                            historyStatusInput.value = btn.getAttribute('data-history-filter');
                            historyForm.submit();
                        });
                    });

                    // Tab switching (Queue / History)
                    const tabBtns = document.querySelectorAll('.tab-btn');
                    const tabPanels = document.querySelectorAll('.tab-panel');

                    tabBtns.forEach(function(btn) {
                        btn.addEventListener('click', function() {
                            const target = btn.getAttribute('data-tab');

                            tabBtns.forEach(function(b) {
                                b.classList.remove('border-my-purple', 'text-my-purple');
                                b.classList.add('border-transparent', 'text-gray-500');
                            });
                            btn.classList.add('border-my-purple', 'text-my-purple');
                            btn.classList.remove('border-transparent', 'text-gray-500');

                            tabPanels.forEach(function(panel) {
                                panel.classList.toggle('hidden', panel.id !== 'tab-' + target);
                            });

                            document.getElementById('active-appeals-total').style.display = target ===
                                'queue' ? '' : 'none';
                            document.getElementById('resolved-appeals-total').style.display = target ===
                                'history' ? 'inline-flex' : 'none';
                        });
                    });

                });
            </script>
        @endpush
    </div>
@endsection
