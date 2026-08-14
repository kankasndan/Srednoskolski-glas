@extends('layouts.master')

@section('title', 'Пријави')

@section('content')
    <div class="min-h-screen p-6 space-y-6">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Пријави</h1>
                <p class="text-sm text-slate-500">Прегледај содржина означена од AI и пријавена од корисници.</p>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="flex gap-6 border-b border-slate-200">
            <button data-tab-btn="queue"
                class="tab-btn pb-3 border-b-2 border-indigo-600 text-indigo-600 text-sm font-medium">
                Пријави
            </button>
            <button data-tab-btn="history"
                class="tab-btn pb-3 border-b-2 border-transparent text-slate-500 text-sm font-medium">
                Историја
            </button>
        </div>

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

        {{-- =========================== REPORTS QUEUE TAB =========================== --}}
        <div class="tab-panel space-y-4" data-tab-panel="queue">

            {{-- Filters --}}
            <form action="{{ route('report.index') }}" method="GET"
                class="bg-white rounded-xl border border-slate-200 p-4 flex justify-start items-center gap-4">

                <div class="flex items-center gap-2">
                    <select class="rounded-lg border border-gray-300 text-sm p-1.5" name="source">
                        <option value="">Сите извори</option>
                        <option value="ai" @selected(request('source') === 'ai')>Означено од AI</option>
                        <option value="human" @selected(request('source') === 'human')>Пријавено од корисник</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <select id="typeFilter" class="rounded-lg border border-gray-300 text-sm p-1.5" name="type">
                        <option value="">Сите типови</option>
                        <option value="User" @selected(request('type') === 'User')>Корисници</option>
                        <option value="Comment" @selected(request('type') === 'Comment')>Коментари</option>
                        <option value="Thread" @selected(request('type') === 'Thread')>Дискусии</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <select class="rounded-lg border border-gray-300 text-sm p-1.5" name="reason">
                        <option value="">Сите причини</option>
                        <option value="spam" @selected(request('reason') === 'spam')>Спам</option>
                        <option value="harassment" @selected(request('reason') === 'harassment')>Вознемирување</option>
                        <option value="hate_speech" @selected(request('reason') === 'hate_speech')>Говор на омраза</option>
                        <option value="inappropriate_content" @selected(request('reason') === 'inappropriate_content')>Несоодветна содржина</option>
                        <option value="misinformation" @selected(request('reason') === 'misinformation')>Дезинформација</option>
                        <option value="other" @selected(request('reason') === 'other')>Друго</option>
                    </select>
                </div>

                <button type="submit"
                    class="bg-my-purple text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-my-purple/90">
                    Филтрирај
                </button>

                @if (request()->anyFilled(['source', 'type', 'reason']))
                    <a href="{{ route('report.index') }}" class="text-sm text-gray-500 hover:underline">Clear
                        filters</a>
                @endif
            </form>

            {{-- Report Card: Означено од AI Discussion --}}
            @forelse ($reports as $report)
                @if ($report->reportable_type === 'App\Models\Comment')
                    @php
                        $type = 'comment';
                        $content = $report->reportable?->content ?? '[Содржината повеќе не е достапна]';
                    @endphp
                @elseif ($report->reportable_type === 'App\Models\Thread')
                    @php
                        $type = 'thread';
                        $content = $report->reportable?->title ?? '[Содржината повеќе не е достапна]';
                        $description = \App\Support\HtmlSanitizer::plainText($report->reportable?->description);
                    @endphp
                @else
                    @php
                        $type = 'user';
                    @endphp
                @endif
                <div class="report-card bg-white rounded-xl border border-slate-200 p-5 space-y-4">
                    <div class="flex items-start justify-between">
                        <div class="flex flex-col items-start space-y-3">
                            <div class="flex items-center gap-3">
                                @if ($report->source == 'ai')
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-purple-100 text-purple-700 text-xs font-semibold">
                                        🤖 Означено од AI
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold">
                                        🧑 Пријавено од корисник
                                    </span>
                                @endif

                                @if ($type === 'comment')
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full bg-orange-100 text-orange-600 text-xs font-semibold capitalize">
                                        {{ $type }}
                                    </span>
                                @elseif($type === 'thread')
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full bg-purple-100 text-purple-600 text-xs font-semibold capitalize">
                                        {{ $type }}
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full bg-pink-100 text-pink-600 text-xs font-semibold capitalize">
                                        {{ $type }}
                                    </span>
                                @endif

                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full bg-yellow-200 text-yellow-600 text-xs font-semibold">
                                    {{ match ($report->status) {
                                        'pending' => 'Во тек',
                                        'approved' => 'Одобрена',
                                        'rejected' => 'Одбиена',
                                        default => $report->status,
                                    } }}
                                </span>

                                <span class="text-xs text-slate-400">Пријавено
                                    {{ $report->created_at->diffForHumans() }}</span>
                            </div>
                            <span class="text-xs text-slate-500">Пријавено од {{ $report->reporter->username }}</span>
                            <span class="text-xs text-slate-400">{{ $report->other_reason }}</span>
                        </div>
                        @if ($report->source == 'ai')
                            <div class="flex flex-col items-start">
                                <div class="text-right">
                                    <div class="text-xs text-slate-500">AI доверба</div>
                                    <div class="text-lg font-bold text-red-600">{{ $report->ai_confidence }}%</div>
                                </div>
                                <div class="">
                                    <span class="text-xs text-slate-500">Причина:</span>
                                    <span class="text-sm font-semibold text-slate-700">{{ match ($report->reason) {
                                        'spam' => 'Спам',
                                        'insulting_content' => 'Навредлива содржина',
                                        'misinformation' => 'Дезинформација',
                                        'age_inappropriate' => 'Несоодветна содржина',
                                        'other' => 'Друго',
                                        default => $report->reason,
                                    } }}</span>
                                </div>
                            </div>
                        @else
                            <div class="">
                                <span class="text-xs text-slate-500">Причина:</span>
                                <span class="text-sm font-semibold text-slate-700">{{ match ($report->reason) {
                                    'spam' => 'Спам',
                                    'insulting_content' => 'Навредлива содржина',
                                    'misinformation' => 'Дезинформација',
                                    'age_inappropriate' => 'Несоодветна содржина',
                                    'other' => 'Друго',
                                    default => $report->reason,
                                } }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="bg-slate-50 rounded-lg p-4 border border-slate-100">
                        @if ($type == 'comment')
                            <div class="text-sm font-semibold text-slate-800 mb-1">"{{ $content }}"</div>
                            <div class="flex items-center gap-2 mt-3 text-xs text-slate-500">
                                <span>Објавено од</span>
                                <span class="font-medium text-slate-700">{{ $report->reportable->user->username }}</span>
                                <span>·</span>
                                <span>Форум:
                                    {{ $report->reportable->thread()->withTrashed()->first()->forum->name }}</span>
                            </div>
                        @elseif($type == 'thread')
                            <div class="text-sm font-semibold text-slate-800 mb-1">"{{ $content }}"</div>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                {{ $description }}
                            </p>
                            <div class="flex items-center gap-2 mt-3 text-xs text-slate-500">
                                <span>Објавено од</span>
                                <span class="font-medium text-slate-700">{{ $report->reportable->user->username }}</span>
                                <span>·</span>
                                <span>Форум: {{ $report->reportable->forum->name }}</span>
                            </div>
                        @elseif($type == 'user')
                            <a href="{{ route('user.show', ['user' => $report->reportable->id]) }}"
                                class="hover:cursor-pointer">
                                <div class="flex items-center gap-3 bg-slate-50 rounded-lg p-4 border border-slate-100">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500">
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-800">
                                            {{ $report->reportable->username }}</div>
                                        <div class="text-xs text-slate-500">
                                            {{ $report->reportable->studentData->school->city->name }} ·
                                            {{ $report->reportable->studentData->school->name }}</div>
                                    </div>
                                </div>
                            </a>
                        @endif
                    </div>

                    @if ($report->source == 'ai')
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                            <div class="text-xs font-semibold text-amber-800 uppercase mb-1">AI образложение</div>
                            <p class="text-sm text-amber-900">
                                {{ $report->ai_reasoning }}
                            </p>
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-100">
                        <div class="flex gap-2">
                            <form action="{{ route('report.approve', ['report' => $report->id]) }}" method="POST">
                                @csrf
                                @method('patch')
                                <button
                                    class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700">
                                    Одобри пријава
                                </button>
                            </form>
                            <form action="{{ route('report.reject', ['report' => $report->id]) }}" method="POST">
                                @csrf
                                @method('patch')
                                <button
                                    class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-medium hover:bg-red-700">
                                    Одбиј пријава
                                </button>
                            </form>
                        </div>
                        <div class="flex gap-2">
                            @if ($type == 'comment' || $type == 'thread')
                                <button data-open-modal="sanctionModal-{{ $report->id }}"
                                    class="px-4 py-2 rounded-lg border border-orange-300 text-orange-700 text-sm font-medium hover:bg-orange-50">
                                    Избриши содржина + санкционирај
                                </button>
                            @else
                                <button data-open-modal="sanctionModal-{{ $report->id }}"
                                    class="px-4 py-2 rounded-lg border border-orange-300 text-orange-700 text-sm font-medium hover:bg-orange-50">
                                    Санкционирај корисник
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- =========================== SANCTION MODAL =========================== --}}
                <div id="sanctionModal-{{ $report->id }}"
                    class="modal hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                    <div class="modal-box bg-white rounded-xl w-full max-w-md p-6 space-y-5">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-bold text-slate-900">Санкционирај корисник</h2>
                            <button data-close-modal="sanctionModal-{{ $report->id }}"
                                class="text-slate-400 hover:text-slate-600">✕</button>
                        </div>

                        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-3">
                            <div class="text-xs font-semibold text-indigo-700 uppercase mb-1">Системска препорака</div>
                            <p class="text-sm text-indigo-900">
                                Based on 2 prior offenses, a <span class="font-semibold">7-day ban</span> is recommended
                                for consistency.
                            </p>
                        </div>

                        <form action="{{ route('sanction.create') }}" method="POST" class="space-y-2">
                            @csrf

                            {{-- Author id for thread/comment; reportable id for user reports. Server re-resolves from report_id. --}}
                            <input type="hidden" name="user_id"
                                value="{{ $type === 'user' ? $report->reportable?->id : $report->reportable?->user_id }}">
                            <input type="hidden" name="report_id" value="{{ $report->id }}">

                            <label
                                class="sanction-option flex items-center gap-3 p-3 rounded-lg border border-slate-200 cursor-pointer hover:bg-slate-50">
                                <input type="radio" name="type" value="warning"
                                    class="sanction-radio text-indigo-600">
                                <div>
                                    <div class="text-sm font-medium text-slate-800">Предупредување</div>
                                    <div class="text-xs text-slate-500">Корисникот е известен, без ограничувања</div>
                                </div>
                            </label>
                            <label
                                class="sanction-option flex items-center gap-3 p-3 rounded-lg border border-indigo-300 bg-indigo-50 cursor-pointer">
                                <input type="radio" name="type" value="7-day"
                                    class="sanction-radio text-indigo-600" checked>
                                <div>
                                    <div class="text-sm font-medium text-slate-800">7-Day Ban <span
                                            class="text-indigo-600 text-xs">(recommended)</span></div>
                                    <div class="text-xs text-slate-500">Сметката е заклучена една недела</div>
                                </div>
                            </label>
                            @if (auth()->user()?->hasAnyRole(['admin', 'super_admin']))
                                <label
                                    class="sanction-option flex items-center gap-3 p-3 rounded-lg border border-slate-200 cursor-pointer hover:bg-slate-50">
                                    <input type="radio" name="type" value="permanent_ban"
                                        class="sanction-radio text-indigo-600">
                                    <div>
                                        <div class="text-sm font-medium text-slate-800">Трајна забрана</div>
                                        <div class="text-xs text-slate-500">Сметката е трајно оневозможена</div>
                                    </div>
                                </label>
                            @endif
                            <label
                                class="sanction-option flex items-center gap-3 p-3 rounded-lg border border-slate-200 cursor-pointer hover:bg-slate-50">
                                <input type="radio" name="type" value="custom"
                                    class="sanction-radio text-indigo-600">
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-slate-800">Прилагодено траење</div>
                                    <input id="customDaysInput" type="number" placeholder="Денови"
                                        class="hidden mt-2 w-24 rounded-lg border-slate-300 text-sm p-1.5 border">
                                </div>
                            </label>
                            <textarea rows="3" placeholder="Причина..." name="reason"
                                class="w-full rounded-lg text-sm p-3 border border-slate-200 "></textarea>

                            @if ($type == 'comment' || $type == 'thread')
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="content" checked class="rounded text-red-600">
                                    <span class="text-sm text-slate-700">Избриши ја содржината веднаш</span>
                                </label>
                            @endif
                            <div class="flex gap-2 pt-2">
                                <button data-close-modal="sanctionModal-{{ $report->id }}"
                                    class="flex-1 px-4 py-2 rounded-lg border border-slate-300 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                    Откажи
                                </button>
                                <button data-action="confirm-sanction" data-close-modal="sanctionModal-{{ $report->id }}"
                                    class="flex-1 px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-medium hover:bg-red-700">
                                    Потврди санкција
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div id="emptyState" class="text-center py-16 text-slate-400">
                    <p class="text-sm">Нема пријави што одговараат.</p>
                </div>
            @endforelse

            <div class="flex justify-center p-3">
                <nav class="flex gap-1 text-sm">
                    @if ($reports->onFirstPage())
                        <button disabled
                            class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-400 cursor-not-allowed">
                            Претходна
                        </button>
                    @else
                        <a href="{{ $reports->previousPageUrl() }}"
                            class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">
                            Претходна
                        </a>
                    @endif

                    @if ($reports->hasMorePages())
                        <a href="{{ $reports->nextPageUrl() }}"
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

        {{-- =========================== HISTORY TAB =========================== --}}
        <div class="tab-panel space-y-4 hidden" data-tab-panel="history">

            <form action="{{ route('report.index') }}" method="GET"
                class="bg-white rounded-xl border border-slate-200 p-4 flex justify-start items-center gap-4">
                <input type="hidden" name="tab" value="history">

                <select class="rounded-lg border border-gray-300 text-sm p-1.5" name="status">
                    <option value="">Сите статуси</option>
                    <option value="approved" @selected(request('status') === 'approved')>Одобрена</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Одбиена</option>
                </select>

                <select class="rounded-lg border border-gray-300 text-sm p-1.5" name="type">
                    <option value="">Сите типови</option>
                    <option value="User" @selected(request('type') === 'User')>Корисници</option>
                    <option value="Comment" @selected(request('type') === 'Comment')>Коментари</option>
                    <option value="Thread" @selected(request('type') === 'Thread')>Дискусии</option>
                </select>

                <button type="submit"
                    class="bg-my-purple text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-my-purple/90">
                    Филтрирај
                </button>
            </form>

            @forelse ($resolvedReports as $historyReport)
                <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center justify-between gap-4">
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center gap-2">
                            @if ($historyReport->status === 'approved')
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">
                                    Одобрена
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold">
                                    Одбиена
                                </span>
                            @endif
                            <span class="text-xs text-slate-400">{{ match (class_basename($historyReport->reportable_type)) {
                                'Comment' => 'Коментар',
                                'Thread' => 'Дискусија',
                                'User' => 'Корисник',
                                default => class_basename($historyReport->reportable_type),
                            } }}</span>
                        </div>
                        <span class="text-sm text-slate-700">Пријавено од {{ $historyReport->reporter->username }} ·
                            Причина: {{ match ($historyReport->reason) {
                                'spam' => 'Спам',
                                'insulting_content' => 'Навредлива содржина',
                                'misinformation' => 'Дезинформација',
                                'age_inappropriate' => 'Несоодветна содржина',
                                'other' => 'Друго',
                                default => $historyReport->reason,
                            } }}</span>
                        <span class="text-xs text-slate-400">Решено
                            {{ $historyReport->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            @empty
                <div class="text-center py-16 text-slate-400">
                    <p class="text-sm">Сè уште нема решени пријави.</p>
                </div>
            @endforelse

            <div class="flex justify-center p-3">
                {{ $resolvedReports->links() }}
            </div>

        </div>

    </div>

    @push('scripts-reports')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                // ---------- Tabs ----------
                var tabButtons = document.querySelectorAll('.tab-btn');
                var tabPanels = document.querySelectorAll('.tab-panel');

                tabButtons.forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var target = btn.getAttribute('data-tab-btn');

                        tabButtons.forEach(function(b) {
                            b.classList.remove('border-indigo-600', 'text-indigo-600');
                            b.classList.add('border-transparent', 'text-slate-500');
                        });
                        btn.classList.remove('border-transparent', 'text-slate-500');
                        btn.classList.add('border-indigo-600', 'text-indigo-600');

                        tabPanels.forEach(function(panel) {
                            if (panel.getAttribute('data-tab-panel') === target) {
                                panel.classList.remove('hidden');
                            } else {
                                panel.classList.add('hidden');
                            }
                        });
                    });
                });

                // ---------- Modals ----------
                document.querySelectorAll('[data-open-modal]').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var modal = document.getElementById(btn.getAttribute('data-open-modal'));
                        if (modal) modal.classList.remove('hidden');
                    });
                });

                document.querySelectorAll('[data-close-modal]').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var modal = document.getElementById(btn.getAttribute('data-close-modal'));
                        if (modal) modal.classList.add('hidden');
                    });
                });

                document.querySelectorAll('.modal').forEach(function(modal) {
                    modal.addEventListener('click', function(e) {
                        if (e.target === modal) modal.classList.add('hidden');
                    });
                });

                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        document.querySelectorAll('.modal').forEach(function(modal) {
                            modal.classList.add('hidden');
                        });
                    }
                });

                // ---------- Sanction radio highlight + custom days input ----------
                var sanctionRadios = document.querySelectorAll('.sanction-radio');

                sanctionRadios.forEach(function(radio) {
                    radio.addEventListener('change', function() {
                        var group = radio.closest('form').querySelectorAll('.sanction-option');
                        group.forEach(function(label) {
                            label.classList.remove('border-indigo-300', 'bg-indigo-50');
                            label.classList.add('border-slate-200');
                        });
                        radio.closest('.sanction-option').classList.remove('border-slate-200');
                        radio.closest('.sanction-option').classList.add('border-indigo-300', 'bg-indigo-50');

                        var customInput = radio.closest('form').querySelector('#customDaysInput');
                        if (customInput) {
                            if (radio.value === 'custom') {
                                customInput.classList.remove('hidden');
                            } else {
                                customInput.classList.add('hidden');
                            }
                        }
                    });
                });

                // ---------- Report card actions (approve/reject/dismiss) ----------
                document.querySelectorAll('.report-card [data-action]').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var card = btn.closest('.report-card');
                        var action = btn.getAttribute('data-action');
                        if (action === 'approve' || action === 'reject' || action === 'dismiss') {
                            if (card) {
                                card.style.transition = 'opacity 0.25s ease';
                                card.style.opacity = '0';
                                setTimeout(function() {
                                    card.remove();
                                    checkEmptyState();
                                }, 250);
                            }
                        }
                    });
                });

                // ---------- Ban appeal actions ----------
                document.querySelectorAll('[data-action="accept-appeal"], [data-action="reject-appeal"]').forEach(
                    function(btn) {
                        btn.addEventListener('click', function() {
                            var card = btn.closest('.bg-white');
                            if (card) {
                                card.style.transition = 'opacity 0.25s ease';
                                card.style.opacity = '0';
                                setTimeout(function() {
                                    card.remove();
                                }, 250);
                            }
                        });
                    });

                // ---------- Unban action ----------
                document.querySelectorAll('[data-action="unban"]').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var row = btn.closest('.ban-row');
                        if (row) row.remove();
                    });
                });

                // ---------- Filters (client-side, if used) ----------
                var sourceFilter = document.getElementById('sourceFilter');
                var typeFilter = document.getElementById('typeFilter');

                if (sourceFilter && typeFilter) {
                    function applyFilters() {
                        var sourceVal = sourceFilter.value;
                        var typeVal = typeFilter.value;
                        var visibleCount = 0;

                        document.querySelectorAll('.report-card').forEach(function(card) {
                            var matchesSource = sourceVal === 'all' || card.getAttribute('data-source') === sourceVal;
                            var matchesType = typeVal === 'all' || card.getAttribute('data-type') === typeVal;
                            var visible = matchesSource && matchesType;
                            card.classList.toggle('hidden', !visible);
                            if (visible) visibleCount++;
                        });

                        var emptyState = document.getElementById('emptyState');
                        if (emptyState) emptyState.classList.toggle('hidden', visibleCount !== 0);
                    }

                    sourceFilter.addEventListener('change', applyFilters);
                    typeFilter.addEventListener('change', applyFilters);
                }

                function checkEmptyState() {
                    var remaining = document.querySelectorAll('.report-card:not(.hidden)').length;
                    var emptyState = document.getElementById('emptyState');
                    if (emptyState) emptyState.classList.toggle('hidden', remaining !== 0);
                }
            });
        </script>
    @endpush
@endsection