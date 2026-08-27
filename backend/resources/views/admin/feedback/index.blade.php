@extends('layouts.master')

@section('title', 'Мислења')

@section('content')
    @php
        $average = (float) $stats['average'];
        $hasFeedback = $stats['total'] > 0;
        $ratingLabels = [
            5 => 'Одлично',
            4 => 'Добро',
            3 => 'Средно',
            2 => 'Слабо',
            1 => 'Многу слабо',
        ];
        $filterQuery = function (array $overrides = []) use ($queryBase) {
            return array_filter(
                array_merge($queryBase, $overrides),
                fn($value) => $value !== null && $value !== '',
            );
        };
    @endphp

    <x-admin.page-header title="Мислења за платформата" subtitle="Новите оценки први. Лошите оценки се посебно означени.">
        @if ($stats['new'] > 0)
            <span class="inline-flex items-center rounded-full bg-my-purple/10 px-3 py-1 text-sm font-medium text-my-purple">
                {{ $stats['new'] }} {{ $stats['new'] === 1 ? 'ново' : 'нови' }}
            </span>
        @endif
        <a href="{{ route('feedback.export', $queryBase) }}"
            class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Извези CSV
        </a>
    </x-admin.page-header>

    <x-admin.flash />

    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500">Просечна оценка</p>
            <div class="flex items-end gap-2">
                <p class="text-3xl font-semibold leading-none text-gray-900">
                    {{ $hasFeedback ? number_format($average, 1) : '—' }}
                </p>
                @if ($hasFeedback)
                    <span class="mb-0.5 text-sm text-gray-400">/ 5</span>
                @endif
            </div>
            <p class="mt-3 text-xs text-gray-400">{{ number_format($stats['guests']) }} гости ·
                {{ number_format($stats['members']) }} членови</p>
        </div>

        <a href="{{ route('feedback.index', $filterQuery(['status' => 'all', 'range' => null, 'rating' => null])) }}"
            class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:border-my-purple/40">
            <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500">Вкупно</p>
            <p class="text-3xl font-semibold leading-none text-gray-900">{{ number_format($stats['total']) }}</p>
            <p class="mt-3 text-xs text-gray-400">Сите примени мислења</p>
        </a>

        <a href="{{ route('feedback.index', $filterQuery(['status' => null, 'range' => null])) }}"
            class="rounded-xl border {{ $filters['status'] === 'new' ? 'border-my-purple/40' : 'border-gray-200' }} bg-white p-5 shadow-sm hover:border-my-purple/40">
            <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500">Нови</p>
            <p class="text-3xl font-semibold leading-none {{ $stats['new'] > 0 ? 'text-my-purple' : 'text-gray-900' }}">
                {{ number_format($stats['new']) }}</p>
            <p class="mt-3 text-xs text-gray-400">Чекаат преглед</p>
        </a>

        <a href="{{ route('feedback.index', $filterQuery(['range' => 'week', 'status' => 'all'])) }}"
            class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:border-my-purple/40">
            <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500">Оваа недела</p>
            <p class="text-3xl font-semibold leading-none text-gray-900">{{ number_format($stats['this_week']) }}</p>
            <p class="mt-3 text-xs text-gray-400">
                @if ($stats['low'] > 0)
                    {{ $stats['low'] }} слаби оценки чекаат
                @else
                    Од понеделник до денес
                @endif
            </p>
        </a>
    </div>

    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-sm font-semibold text-gray-800">Распределба на оценки</h2>
        <div class="space-y-2.5">
            @foreach ($distribution as $star => $count)
                @php
                    $percent = $hasFeedback ? round(($count / $stats['total']) * 100) : 0;
                @endphp
                <a href="{{ route('feedback.index', $filterQuery(['rating' => $filters['rating'] === $star ? null : $star])) }}"
                    class="group grid grid-cols-[6.5rem_1fr_4.5rem] items-center gap-3">
                    <span class="flex items-center gap-1 text-xs {{ $star <= 2 ? 'text-red-600' : 'text-gray-600' }}">
                        <x-feedback-stars :rating="$star" />
                    </span>
                    <span class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                        <span
                            class="block h-full rounded-full {{ $star <= 2 ? 'bg-red-400' : ($filters['rating'] === $star ? 'bg-my-purple' : 'bg-my-purple/70 group-hover:bg-my-purple') }}"
                            style="width: {{ $percent }}%"></span>
                    </span>
                    <span class="text-right text-xs tabular-nums text-gray-500">
                        {{ $count }}
                        <span class="text-gray-400">({{ $percent }}%)</span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    <form method="GET" action="{{ route('feedback.index') }}"
        class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute top-1/2 left-3 -translate-y-1/2 text-xs text-gray-400"></i>
                <input type="search" name="q" value="{{ $filters['q'] }}"
                    placeholder="Пребарај по порака или корисничко име"
                    class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pr-3 pl-9 text-sm text-gray-800 placeholder:text-gray-400 focus:border-my-purple focus:ring-2 focus:ring-my-purple/40 focus:outline-none">
            </div>

            <select name="rating"
                class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 focus:border-my-purple focus:outline-none">
                <option value="">Сите оценки</option>
                @foreach ($ratingLabels as $value => $label)
                    <option value="{{ $value }}" @selected($filters['rating'] === $value)>{{ $value }} ★ ·
                        {{ $label }}</option>
                @endforeach
            </select>

            <select name="range"
                class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 focus:border-my-purple focus:outline-none">
                <option value="all" @selected($filters['range'] === 'all')>Сите датуми</option>
                <option value="week" @selected($filters['range'] === 'week')>Оваа недела</option>
                <option value="7" @selected($filters['range'] === '7')>Последни 7 дена</option>
                <option value="30" @selected($filters['range'] === '30')>Последни 30 дена</option>
            </select>

            <div class="flex flex-wrap items-center gap-1">
                @foreach (['new' => 'Нови', 'reviewed' => 'Прегледани', 'all' => 'Сите'] as $value => $label)
                    <label
                        class="cursor-pointer rounded-full px-3 py-1.5 text-xs font-medium {{ $filters['status'] === $value ? 'bg-my-purple text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        <input type="radio" name="status" value="{{ $value }}" class="sr-only"
                            {{ $filters['status'] === $value ? 'checked' : '' }} onchange="this.form.submit()">
                        {{ $label }}
                    </label>
                @endforeach
            </div>

            <button type="submit"
                class="rounded-lg bg-my-purple px-4 py-2 text-sm font-medium text-white hover:opacity-90">
                Филтрирај
            </button>

            @if ($isFiltered)
                <a href="{{ route('feedback.index') }}" class="text-sm text-gray-500 hover:underline">Исчисти</a>
            @endif
        </div>
    </form>

    @if ($items->isEmpty())
        <x-admin.empty :title="$isFiltered ? 'Нема мислења за овој филтер.' : 'Нема нови мислења.'"
            :description="$isFiltered ? 'Пробај друга оценка, статус или пребарување.' : 'Ќе се појават тука кога некој ќе ја оцени страницата „За нас“.'"
            icon="fa-star">
            @if ($isFiltered)
                <a href="{{ route('feedback.index') }}" class="text-sm font-medium text-my-purple hover:underline">Исчисти
                    филтри</a>
            @endif
        </x-admin.empty>
    @else
        <div class="space-y-3">
            @foreach ($items as $item)
                @php
                    $excerpt = $item->message
                        ? \Illuminate\Support\Str::limit($item->message, 160)
                        : 'Без дополнителна порака.';
                    $showQuery = array_merge(['feedback' => $item], $queryBase);
                @endphp
                <div
                    class="rounded-xl border bg-white p-5 shadow-sm {{ $item->rating <= 2 ? 'border-l-4 border-l-red-400 border-gray-200' : ($item->isReviewed() ? 'border-gray-200' : 'border-my-purple/25') }}">
                    <div class="flex items-start gap-4">
                        <x-admin.avatar :user="$item->user" />

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                @if ($item->user && auth()->user()?->can('view user details'))
                                    <a href="{{ route('user.show', $item->user) }}"
                                        class="truncate font-medium text-gray-800 hover:text-my-purple">{{ $item->user->username }}</a>
                                @else
                                    <p class="truncate font-medium text-gray-800">{{ $item->user?->username ?? 'Гостин' }}
                                    </p>
                                @endif
                                <x-feedback-stars :rating="$item->rating" size="md" />
                                @unless ($item->isReviewed())
                                    <span
                                        class="inline-flex items-center rounded-full bg-my-purple/10 px-2 py-0.5 text-[11px] font-semibold text-my-purple">Ново</span>
                                @endunless
                                @if ($item->rating <= 2)
                                    <span
                                        class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-700">Слаба
                                        оценка</span>
                                @endif
                                <span
                                    class="ml-auto shrink-0 text-xs text-gray-400">{{ $item->created_at->diffForHumans() }}</span>
                            </div>
                            <p
                                class="mt-2 text-sm leading-6 {{ $item->message ? 'text-gray-600' : 'text-gray-400 italic' }}">
                                {{ $excerpt }}
                            </p>
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <a href="{{ route('feedback.show', $showQuery) }}"
                                    class="text-sm font-medium text-my-purple hover:underline">Отвори</a>
                                @can('review feedback')
                                    @unless ($item->isReviewed())
                                        <form method="POST" action="{{ route('feedback.review', $item) }}">
                                            @csrf
                                            @method('PATCH')
                                            @foreach ($queryBase as $key => $value)
                                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                            @endforeach
                                            <button type="submit"
                                                class="text-sm font-medium text-gray-600 hover:text-my-purple">Означи како
                                                прегледано</button>
                                        </form>
                                    @endunless
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <x-admin.pagination :paginator="$items" />
    @endif
@endsection
