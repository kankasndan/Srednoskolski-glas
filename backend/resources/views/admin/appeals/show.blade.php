@extends('layouts.master')

@section('title', 'Детали за жалба')

@section('content')
    @php
        $sanction = $appeal->sanction;
        $report = $sanction?->report;
        $reportable = $report?->reportable;
        $appealUser = $appeal->user;
        $school = $appealUser?->studentData?->school;
        $statusBadgeClasses = match ($appeal->status) {
            'pending' => 'bg-my-yellow/30 text-gray-800',
            'rejected' => 'bg-red-100 text-red-700',
            'accepted' => 'bg-green-100 text-green-600',
            default => 'bg-gray-100 text-gray-600',
        };
        $statusLabel = match ($appeal->status) {
            'pending' => 'Во тек',
            'rejected' => 'Одбиена',
            'accepted' => 'Прифатена',
            default => $appeal->status,
        };
        $reportableType = $reportable ? class_basename($reportable) : null;
        $reportableTypeLabel = match ($reportableType) {
            'Comment' => 'Коментар',
            'Thread' => 'Дискусија',
            'User' => 'Корисник',
            default => $reportableType,
        };
    @endphp

    <div class="p-6">

        <div class="max-w-4xl mx-auto">

            <!-- Back link -->
            <a href="{{ route('appeal.index') }}"
                class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 mb-6">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Назад кон жалби
            </a>

            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <img src="{{ $appealUser?->imageUrl }}" alt="{{ $appealUser?->username }}"
                        class="w-14 h-14 rounded-full object-cover bg-gray-100">
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">{{ $appealUser?->username }}</h1>
                        <p class="text-sm text-gray-500">
                            Училиште: {{ $school?->name ?? 'Нема поврзано училиште' }}
                        </p>
                    </div>
                </div>
                <span class="text-xs font-medium px-2 py-1 rounded-full {{ $statusBadgeClasses }}">
                    {{ $statusLabel }}
                </span>
            </div>

            <!-- Ban Info Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase mb-4">Детали за санкција</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Тип на забрана</p>
                        <p class="text-sm font-medium text-gray-800">{{ match ($sanction?->type) {
                            'warning' => 'Предупредување',
                            'permanent_ban' => 'Трајна забрана',
                            '7-day' => '7-дневна забрана',
                            'custom' => 'Прилагодена',
                            default => $sanction?->type ?? 'Н/П',
                        } }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Причина</p>
                        <p class="text-sm font-medium text-gray-800">{{ $sanction?->reason ?? 'Н/П' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Издадена на</p>
                        <p class="text-sm font-medium text-gray-800">
                            {{ $sanction?->created_at?->format('d.m.Y · H:i') ?? 'Н/П' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Издадена од</p>
                        <p class="text-sm font-medium text-gray-800">
                            {{ $sanction?->issuedBy?->username ?? 'Непознато' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Истекува</p>
                        <p class="text-sm font-medium text-gray-800">
                            {{ $sanction?->expires_at ? ($sanction->expires_at->isPast() ? 'Expired' : $sanction->expires_at->diffForHumans()) : 'Permanent' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Решено на</p>
                        <p class="text-sm font-medium text-gray-800">
                            {{ $appeal->resolved_at?->format('M d, Y · H:i') ?? 'Сè уште не е решено' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Appeal Message -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase mb-3">Жалба на членот</h2>
                <div class="flex items-start gap-3">
                    <img src="{{ $appealUser?->imageUrl }}" alt="{{ $appealUser?->username }}"
                        class="size-10 rounded-full object-cover bg-gray-100">
                    <div class="bg-gray-50 rounded-lg p-4 flex-1">
                        <p class="text-sm text-gray-700">
                            {{ $appeal->explanation }}
                        </p>
                        <p class="text-xs text-gray-400 mt-2">Поднесена {{ $appeal->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>

            <!-- Пријавена содржина Preview -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase mb-3">Пријавена содржина</h2>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                    @if ($report)
                        <p class="text-xs text-gray-500 mb-1">
                            @if ($reportableType === 'Comment')
                                Коментар на дискусија „{{ $reportable?->thread?->title ?? 'Непозната дискусија' }}“
                            @elseif ($reportableType === 'Thread')
                                Дискусија „{{ $reportable?->title ?? 'Непозната дискусија' }}“
                            @elseif ($reportableType === 'User')
                                Корисник „{{ $reportable?->username ?? 'Непознат корисник' }}“
                            @else
                                Пријавена ставка
                            @endif
                        </p>
                        <p class="text-sm text-gray-700">
                            {{ $reportableType === 'Comment' ? $reportable?->content : ($reportableType === 'Thread' ? $reportable?->description : $report->reason) }}
                        </p>
                    @else
                        <p class="text-sm text-gray-500">Нема поврзана пријава за оваа санкција.</p>
                    @endif
                </div>
            </div>

            <!-- Decision Actions -->
            <div class="flex justify-between items-center bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h2 class="text-sm font-semibold text-gray-500 uppercase mb-3">Реши жалба</h2>

                <div class="flex justify-end gap-3">
                    @can('reject appeals')
                        <form action="{{ route('appeal.reject', ['appeal' => $appeal->id]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                class="px-5 py-2 rounded-lg text-sm font-medium bg-red-600 text-white hover:bg-red-700">
                                Одбиј жалба
                            </button>
                        </form>
                    @endcan
                    @can('accept appeals')
                        <form action="{{ route('appeal.accept', ['appeal' => $appeal->id]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                class="px-5 py-2 rounded-lg text-sm font-medium bg-green-600 text-white hover:bg-green-700">
                                Прифати и одбани
                            </button>
                        </form>
                    @endcan
                </div>
            </div>

        </div>

    </div>
@endsection
