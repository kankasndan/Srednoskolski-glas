@extends('layouts.master')

@section('title', 'Мислење')

@section('content')
    @php
        $author = $feedback->user;
        $school = $author?->studentData?->school;
        $ratingLabels = [
            5 => 'Одлично',
            4 => 'Добро',
            3 => 'Средно',
            2 => 'Слабо',
            1 => 'Многу слабо',
        ];
    @endphp

    <a href="{{ route('feedback.index', $backQuery) }}"
        class="mb-6 inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Назад кон мислења
    </a>

    <x-admin.flash />

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <x-admin.avatar :user="$author" size="lg" />
            <div>
                <h1 class="text-xl font-bold text-gray-800">{{ $author?->username ?? 'Гостин' }}</h1>
                <p class="text-sm text-gray-500">
                    @if ($author)
                        Училиште: {{ $school?->name ?? 'Нема поврзано училиште' }}
                    @else
                        Испратено без најава
                    @endif
                </p>
            </div>
        </div>
        @if ($feedback->isReviewed())
            <span class="self-start rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">Прегледано</span>
        @else
            <span class="self-start rounded-full bg-my-purple/10 px-2 py-1 text-xs font-medium text-my-purple">Ново</span>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_16rem]">
        <div class="space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex flex-wrap items-center gap-3">
                    <x-feedback-stars :rating="$feedback->rating" size="lg" />
                    <span class="text-2xl font-semibold text-gray-900">{{ $feedback->rating }}</span>
                    <span class="text-sm text-gray-500">{{ $ratingLabels[$feedback->rating] ?? '' }}</span>
                </div>
                @if ($feedback->message)
                    <p class="whitespace-pre-wrap text-sm leading-6 text-gray-700">{{ $feedback->message }}</p>
                @else
                    <p class="text-sm text-gray-400 italic">Корисникот не остави дополнителна порака — само оценка.</p>
                @endif
                <p class="mt-3 text-xs text-gray-400">Поднесена {{ $feedback->created_at->diffForHumans() }}</p>
            </div>

            @can('review feedback')
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-3 text-sm font-semibold uppercase text-gray-500">Внатрешна белешка</h2>
                    <form method="POST" action="{{ route('feedback.note', $feedback) }}" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        @foreach ($backQuery as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        <textarea name="staff_note" rows="4" maxlength="2000"
                            class="w-full rounded-lg border border-gray-200 p-3 text-sm focus:border-my-purple focus:ring-2 focus:ring-my-purple/40 focus:outline-none"
                            placeholder="Забелешка само за персоналот…">{{ old('staff_note', $feedback->staff_note) }}</textarea>
                        <button type="submit"
                            class="rounded-lg bg-my-purple px-4 py-2 text-sm font-medium text-white hover:opacity-90">Зачувај
                            белешка</button>
                    </form>
                </div>
            @else
                @if ($feedback->staff_note)
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                        <h2 class="mb-3 text-sm font-semibold uppercase text-gray-500">Внатрешна белешка</h2>
                        <p class="whitespace-pre-wrap text-sm text-gray-700">{{ $feedback->staff_note }}</p>
                    </div>
                @endif
            @endcan
        </div>

        <aside class="space-y-4">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold uppercase text-gray-500">Детали</h2>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="mb-1 text-xs text-gray-500">Испратено</dt>
                        <dd class="font-medium text-gray-800">{{ $feedback->created_at->format('d.m.Y · H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="mb-1 text-xs text-gray-500">Член</dt>
                        <dd class="font-medium text-gray-800">
                            @if ($author && auth()->user()?->can('view user details'))
                                <a href="{{ route('user.show', $author) }}"
                                    class="text-my-purple hover:underline">{{ $author->username }}</a>
                            @else
                                {{ $author?->username ?? 'Гостин' }}
                            @endif
                        </dd>
                    </div>
                    @if ($feedback->isReviewed())
                        <div>
                            <dt class="mb-1 text-xs text-gray-500">Прегледано од</dt>
                            <dd class="font-medium text-gray-800">{{ $feedback->reviewer?->username ?? 'Непознато' }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-gray-500">Прегледано на</dt>
                            <dd class="font-medium text-gray-800">{{ $feedback->reviewed_at->format('d.m.Y · H:i') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="space-y-3 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-semibold uppercase text-gray-500">Акции</h2>

                @can('review feedback')
                    @unless ($feedback->isReviewed())
                        <form method="POST" action="{{ route('feedback.review', $feedback) }}">
                            @csrf
                            @method('PATCH')
                            @foreach ($backQuery as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <button type="submit"
                                class="w-full rounded-lg bg-my-purple px-4 py-2.5 text-sm font-medium text-white hover:opacity-90">
                                Означи како прегледано
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('feedback.unreview', $feedback) }}">
                            @csrf
                            @method('PATCH')
                            @foreach ($backQuery as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <button type="submit"
                                class="w-full rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Врати како ново
                            </button>
                        </form>
                    @endunless
                @endcan

                @can('delete feedback')
                    <form method="POST" action="{{ route('feedback.destroy', $feedback) }}"
                        data-confirm="Избриши го ова мислење? Ова не може да се врати.">
                        @csrf
                        @method('DELETE')
                        @foreach ($backQuery as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        <button type="submit"
                            class="w-full rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-medium text-red-700 hover:bg-red-100">
                            Избриши
                        </button>
                    </form>
                @endcan

                @unless (auth()->user()?->can('review feedback') || auth()->user()?->can('delete feedback'))
                    <p class="text-xs text-gray-400">Имаш пристап само за преглед.</p>
                @endunless
            </div>
        </aside>
    </div>
@endsection
