@extends('layouts.master')

@section('title', 'Пријави')

@section('content')
    <div class="space-y-6">

        <x-admin.page-header title="Пријави" subtitle="Прегледај содржина означена од AI и пријавена од корисници." />

        <div class="flex gap-6 border-b border-gray-200">
            <a href="{{ route('report.index', array_merge(request()->except(['page', 'tab']), ['tab' => 'queue'])) }}"
                class="pb-3 border-b-2 text-sm font-medium {{ ($activeTab ?? 'queue') === 'queue' ? 'border-my-purple text-my-purple' : 'border-transparent text-gray-500' }}">
                Пријави
            </a>
            <a href="{{ route('report.index', ['tab' => 'history']) }}"
                class="pb-3 border-b-2 text-sm font-medium {{ ($activeTab ?? 'queue') === 'history' ? 'border-my-purple text-my-purple' : 'border-transparent text-gray-500' }}">
                Историја
            </a>
        </div>

        <x-admin.flash />

        {{-- =========================== REPORTS QUEUE TAB =========================== --}}

        {{-- =========================== REPORTS QUEUE TAB =========================== --}}
        <div class="tab-panel space-y-4 {{ ($activeTab ?? 'queue') === 'queue' ? '' : 'hidden' }}" data-tab-panel="queue">

            {{-- Filters --}}
            <form action="{{ route('report.index') }}" method="GET"
                class="bg-white rounded-xl border border-gray-200 p-4 flex justify-start items-center gap-4">
                <input type="hidden" name="tab" value="queue">


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
                    <a href="{{ route('report.index', ['tab' => 'queue']) }}" class="text-sm text-gray-500 hover:underline">
                        Исчисти филтри
                    </a>
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
                <div class="report-card bg-white rounded-xl border border-gray-200 p-5 space-y-4">
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

                                <span class="text-xs text-gray-400">Пријавено
                                    {{ $report->created_at->diffForHumans() }}</span>
                                @if ((int) ($report->reports_count ?? 1) > 1)
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-800 text-white text-xs font-semibold">
                                        {{ $report->reports_count }} пријави
                                    </span>
                                @endif
                            </div>
                            <span class="text-xs text-gray-500">Пријавено од
                                @if (isset($report->group_reporters) && $report->group_reporters->isNotEmpty())
                                    {{ $report->group_reporters->pluck('username')->filter()->join(', ') }}
                                @else
                                    {{ $report->reporter->username }}
                                @endif
                            </span>
                            <span class="text-xs text-gray-400">{{ $report->other_reason }}</span>
                        </div>
                        @if ($report->source == 'ai')
                            <div class="flex flex-col items-start">
                                <div class="text-right">
                                    <div class="text-xs text-gray-500">AI доверба</div>
                                    <div class="text-lg font-bold text-red-600">{{ $report->ai_confidence }}%</div>
                                </div>
                                <div class="">
                                    <span class="text-xs text-gray-500">Причина:</span>
                                    <span
                                        class="text-sm font-semibold text-gray-700">{{ match ($report->reason) {
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
                                <span class="text-xs text-gray-500">Причина:</span>
                                <span
                                    class="text-sm font-semibold text-gray-700">{{ match ($report->reason) {
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

                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                        @if ($type == 'comment')
                            <div class="text-sm font-semibold text-gray-800 mb-1">"{{ $content }}"</div>
                            <div class="flex items-center gap-2 mt-3 text-xs text-gray-500">
                                <span>Објавено од</span>
                                <span class="font-medium text-gray-700">{{ $report->reportable->user->username }}</span>
                                <span>·</span>
                                <span>Форум:
                                    {{ $report->reportable->thread()->withTrashed()->first()->forum->name }}</span>
                            </div>
                        @elseif($type == 'thread')
                            <div class="text-sm font-semibold text-gray-800 mb-1">"{{ $content }}"</div>
                            <p class="text-sm text-gray-600 leading-relaxed">
                                {{ $description }}
                            </p>
                            <div class="flex items-center gap-2 mt-3 text-xs text-gray-500">
                                <span>Објавено од</span>
                                <span class="font-medium text-gray-700">{{ $report->reportable->user->username }}</span>
                                <span>·</span>
                                <span>Форум: {{ $report->reportable->forum->name }}</span>
                            </div>
                        @elseif($type == 'user')
                            <a href="{{ route('user.show', ['user' => $report->reportable->id]) }}"
                                class="hover:cursor-pointer">
                                <div class="flex items-center gap-3 bg-gray-50 rounded-lg p-4 border border-gray-100">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-my-purple to-[#3300F5]">
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-800">
                                            {{ $report->reportable->username }}</div>
                                        <div class="text-xs text-gray-500">
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

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-gray-100">
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
                            <h2 class="text-lg font-bold text-gray-900">Санкционирај</h2>
                            <button data-close-modal="sanctionModal-{{ $report->id }}"
                                class="text-gray-400 hover:text-gray-600">✕</button>
                        </div>

                        <form action="{{ route('sanction.create') }}" method="POST" class="space-y-2">
                            @csrf

                            {{-- Author id for thread/comment; reportable id for user reports. Server re-resolves from report_id. --}}
                            <input type="hidden" name="user_id"
                                value="{{ $type === 'user' ? $report->reportable?->id : $report->reportable?->user_id }}">
                            <input type="hidden" name="report_id" value="{{ $report->id }}">

                            <label
                                class="sanction-option flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50">
                                <input type="radio" name="type" value="warning"
                                    class="sanction-radio text-my-purple">
                                <div>
                                    <div class="text-sm font-medium text-gray-800">Предупредување</div>
                                    <div class="text-xs text-gray-500">Корисникот е известен, без ограничувања</div>
                                </div>
                            </label>
                            <label
                                class="sanction-option flex items-center gap-3 p-3 rounded-lg border border-my-purple/40 bg-my-purple/10 cursor-pointer">
                                <input type="radio" name="type" value="7-day"
                                    class="sanction-radio text-my-purple" checked>
                                <div>
                                    <div class="text-sm font-medium text-gray-800">7 дневна санкција</div>
                                    <div class="text-xs text-gray-500">Сметката е заклучена една недела</div>
                                </div>
                            </label>
                            @if (auth()->user()
                                    ?->hasAnyRole(['admin', 'super_admin']))
                                <label
                                    class="sanction-option flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50">
                                    <input type="radio" name="type" value="permanent_ban"
                                        class="sanction-radio text-my-purple">
                                    <div>
                                        <div class="text-sm font-medium text-gray-800">Трајна забрана</div>
                                        <div class="text-xs text-gray-500">Сметката е трајно оневозможена</div>
                                    </div>
                                </label>
                            @endif
                            <label
                                class="sanction-option flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50">
                                <input type="radio" name="type" value="custom"
                                    class="sanction-radio text-my-purple">
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-800">Прилагодено траење</div>
                                    <input id="customDaysInput" type="number" name="days" min="1" placeholder="Денови"
                                        class="hidden mt-2 w-24 rounded-lg border-gray-300 text-sm p-1.5 border">
                                </div>
                            </label>
                            <textarea rows="3" placeholder="Причина..." name="reason"
                                class="w-full rounded-lg text-sm p-3 border border-gray-200 "></textarea>

                            @if ($type == 'comment' || $type == 'thread')
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="content" checked class="rounded text-red-600">
                                    <span class="text-sm text-gray-700">Избриши ја содржината веднаш</span>
                                </label>
                            @endif
                            <div class="flex gap-2 pt-2">
                                <button data-close-modal="sanctionModal-{{ $report->id }}"
                                    class="flex-1 px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Откажи
                                </button>
                                <button data-action="confirm-sanction"
                                    data-close-modal="sanctionModal-{{ $report->id }}"
                                    class="flex-1 px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-medium hover:bg-red-700">
                                    Потврди санкција
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div id="emptyState" class="text-center py-16 text-gray-400">
                    <p class="text-sm">Нема пријави што одговараат.</p>
                </div>
            @endforelse

            <x-admin.pagination :paginator="$reports" />

        </div>

        {{-- =========================== HISTORY TAB =========================== --}}
        <div class="tab-panel space-y-4 {{ ($activeTab ?? 'queue') === 'history' ? '' : 'hidden' }}"
            data-tab-panel="history">

            <form action="{{ route('report.index') }}" method="GET"
                class="bg-white rounded-xl border border-gray-200 p-4 flex justify-start items-center gap-4">
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

                @if (request()->anyFilled(['status', 'type']))
                    <a href="{{ route('report.index', ['tab' => 'history']) }}"
                        class="text-sm text-gray-500 hover:underline">
                        Исчисти филтри
                    </a>
                @endif
            </form>

            @forelse ($resolvedReports as $historyReport)
                <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-between gap-4">
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
                            <span
                                class="text-xs text-gray-400">{{ match (class_basename($historyReport->reportable_type)) {
                                    'Comment' => 'Коментар',
                                    'Thread' => 'Дискусија',
                                    'User' => 'Корисник',
                                    default => class_basename($historyReport->reportable_type),
                                } }}</span>
                        </div>
                        <span class="text-sm text-gray-700">Пријавено од {{ $historyReport->reporter->username }} ·
                            Причина:
                            {{ match ($historyReport->reason) {
                                'spam' => 'Спам',
                                'insulting_content' => 'Навредлива содржина',
                                'misinformation' => 'Дезинформација',
                                'age_inappropriate' => 'Несоодветна содржина',
                                'other' => 'Друго',
                                default => $historyReport->reason,
                            } }}</span>
                        <span class="text-xs text-gray-400">Решено
                            {{ $historyReport->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            @empty
                <div class="text-center py-16 text-gray-400">
                    <p class="text-sm">Сè уште нема решени пријави.</p>
                </div>
            @endforelse

            <div class="flex justify-center p-3">
                <x-admin.pagination :paginator="$resolvedReports" class="w-full" />
            </div>

        </div>

    </div>

    @push('scripts-reports')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

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
                            label.classList.remove('border-my-purple/40', 'bg-my-purple/10');
                            label.classList.add('border-gray-200');
                        });
                        radio.closest('.sanction-option').classList.remove('border-gray-200');
                        radio.closest('.sanction-option').classList.add('border-my-purple/40',
                            'bg-my-purple/10');

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
            });
        </script>
    @endpush
@endsection
