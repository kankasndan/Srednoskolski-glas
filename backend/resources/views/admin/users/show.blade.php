@extends('layouts.master')

@section('title', 'Корисник: '.($user->username ?? 'Детали'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('user.index') }}" class="text-sm text-gray-500 hover:text-my-purple flex items-center gap-1">
            &larr; Назад кон корисници
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- Profile header --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6 flex items-center gap-5">
        <img src="{{ $user->imageUrl ?? 'https://via.placeholder.com/80' }}"
            class="w-20 h-20 rounded-full object-cover border border-gray-200">

        <div class="flex-1">
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-bold text-gray-800">{{ $user->username ?? 'Нема корисничко име' }}</h1>

                @if ($user->sanctions->contains(fn($s) => $s->type !== 'warning' && ($s->expires_at === null || $s->expires_at->isFuture())))
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Баниран</span>
                @else
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Активен</span>
                @endif

                @foreach ($user->roles as $role)
                    <span
                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-my-purple/10 text-my-purple capitalize">
                        {{ $role->name }}
                    </span>
                @endforeach
            </div>
            <p class="text-sm text-gray-500">{{ $user->email }}</p>
            @if ($user->bio)
                <p class="text-sm text-gray-600 mt-1">{{ $user->bio }}</p>
            @endif
        </div>

        <div class="flex gap-6 text-right">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide">Карма</p>
                <p class="text-2xl font-bold text-gray-800">{{ $user->karma ?? 0 }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide">Серија</p>
                <p class="text-2xl font-bold text-gray-800">{{ $user->current_streak ?? 0 }}</p>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="button" id="openSanctionModalBtn"
                class="bg-red-600 text-white px-3 py-2 rounded-lg text-xs font-medium hover:bg-red-700">
                Санкционирај
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Account details --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-800 uppercase tracking-wide mb-2">Детали за сметката</h2>

            <div class="text-sm">
                <p class="text-gray-400">Начин на најава</p>
                <p class="text-gray-800 font-medium capitalize">{{ $user->provider ?? '—' }}</p>
            </div>

            <div class="text-sm">
                <p class="text-gray-400">Приклучен</p>
                <p class="text-gray-800 font-medium">{{ $user->created_at?->format('M d, Y') ?? '—' }}</p>
            </div>

            <div class="text-sm">
                <p class="text-gray-400">Потврдена е-пошта</p>
                <p class="text-gray-800 font-medium">
                    {{ $user->email_verified_at ? $user->email_verified_at->format('M d, Y') : 'Не е потврдена' }}
                </p>
            </div>

            <div class="text-sm">
                <p class="text-gray-400">Онбордингот е завршен</p>
                <p class="text-gray-800 font-medium">
                    {{ $user->onboarding_completed_at ? $user->onboarding_completed_at->format('M d, Y') : 'Не е завршен' }}
                </p>
            </div>

            <div class="text-sm">
                <p class="text-gray-400">Јазик</p>
                <p class="text-gray-800 font-medium">{{ $user->language ?? '—' }}</p>
            </div>

            <div class="text-sm">
                <p class="text-gray-400">Тема / Акцент</p>
                <p class="text-gray-800 font-medium capitalize">
                    {{ $user->theme ?? '—' }} / {{ $user->accent_color ?? '—' }}
                </p>
            </div>
        </div>

        {{-- Student data --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-800 uppercase tracking-wide mb-2">Ученички податоци</h2>

            @if ($user->studentData)
                <div class="text-sm">
                    <p class="text-gray-400">Училиште</p>
                    <p class="text-gray-800 font-medium">{{ $user->studentData->school->name ?? '—' }}</p>
                </div>
                <div class="text-sm">
                    <p class="text-gray-400">Град</p>
                    <p class="text-gray-800 font-medium">{{ $user->studentData->school->city->name ?? '—' }}</p>
                </div>
                <div class="text-sm">
                    <p class="text-gray-400">Струка</p>
                    <p class="text-gray-800 font-medium">{{ $user->studentData->vocation->name ?? '—' }}</p>
                </div>
                <div class="text-sm">
                    <p class="text-gray-400">Година</p>
                    <p class="text-gray-800 font-medium">{{ $user->studentData->grade ?? '—' }}</p>
                </div>
            @else
                <p class="text-sm text-gray-400">Нема поставен ученички профил.</p>
            @endif
        </div>

        {{-- Sanctions --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h2 class="text-sm font-semibold text-gray-800 uppercase tracking-wide mb-4">Историја на санкции</h2>

            @forelse ($user->sanctions as $sanction)
                <div class="border-b border-gray-100 last:border-0 py-3">
                    <div class="flex items-center justify-between">
                        <span
                            class="text-sm font-medium text-gray-800 capitalize">{{ str_replace('_', ' ', $sanction->type) }}</span>
                        @if ($sanction->type !== 'warning' && ($sanction->expires_at === null || $sanction->expires_at->isFuture()))
                            <span class="text-xs font-medium text-red-600">Активен</span>
                        @else
                            <span class="text-xs font-medium text-gray-400">Неактивна</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-1">{{ $sanction->reason }}</p>
                    <p class="text-xs text-gray-400 mt-1">
                        Issued {{ $sanction->created_at?->format('M d, Y') }}
                        @if ($sanction->expires_at)
                            &middot; Expires {{ $sanction->expires_at->format('M d, Y') }}
                        @endif
                    </p>
                </div>
            @empty
                <p class="text-sm text-gray-400">Нема санкции во евиденција.</p>
            @endforelse
        </div>
    </div>

    {{-- Social + activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 text-center">
            <p class="text-xs text-gray-400 uppercase">Следбеници</p>
            {{-- <p class="text-2xl font-bold text-gray-800">{{ $user->followers_count ?? $user->followers->count() }}</p> --}}
        </div>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 text-center">
            <p class="text-xs text-gray-400 uppercase">Следи</p>
            {{-- <p class="text-2xl font-bold text-gray-800">{{ $user->following_count ?? $user->following->count() }}</p> --}}
        </div>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 text-center">
            <p class="text-xs text-gray-400 uppercase">Блокирани корисници</p>
            {{-- <p class="text-2xl font-bold text-gray-800">{{ $user->blockedUsers->count() }}</p> --}}
        </div>
    </div>

    {{-- Recent threads --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mt-6">
        <h2 class="text-sm font-semibold text-gray-800 uppercase tracking-wide mb-4">Неодамнешни дискусии</h2>
        @forelse ($user->threads as $thread)
            <a href="{{ route("forums.threads.show", ['forum' => $thread->forum->slug, "thread" => $thread->id]) }}" class="border-b border-gray-100 last:border-0 py-2 text-sm" target="_blank">
                <p class="font-medium text-gray-800">{{ $thread->title }}</p>
                <p class="text-xs text-gray-400">{{ $thread->created_at?->format('M d, Y') }} &middot;
                    {{ $thread->upvotes_count ?? 0 }} гласови</p>
            </a>
        @empty
            <p class="text-sm text-gray-400">Нема објавени дискусии.</p>
        @endforelse
    </div>

    @can('export user as pdf')
        <div class="mt-6">
            <a href="{{ route('user.export', $user->id) }}"
                class="bg-my-purple text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-my-purple/90 inline-flex items-center gap-2">
                Извези профил како PDF
            </a>
        </div>
    @endcan


    <div id="newSanctionModal" class="modal hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="modal-box bg-white rounded-xl w-full max-w-md p-6 space-y-5">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900">Санкционирај корисник</h2>
                <button onclick="document.getElementById('newSanctionModal').classList.add('hidden')">✕</button>
            </div>

            <div class="bg-my-purple/5 border border-my-purple/30 rounded-lg p-3">
                <div class="text-xs font-semibold text-my-purple uppercase mb-1">Системска препорака</div>
                <p class="text-sm text-my-purple">
                    Based on 2 prior offenses, a <span class="font-semibold">7-day ban</span> is recommended
                    for consistency.
                </p>
            </div>

            <form action="{{ route('sanction.create') }}" method="POST" class="space-y-2">
                @csrf

                <input type="hidden" name="user_id" value="{{ $user->id }}">

                <label
                    class="sanction-option flex items-center gap-3 p-3 rounded-lg border border-slate-200 cursor-pointer hover:bg-slate-50">
                    <input type="radio" name="type" value="warning" class="sanction-radio text-my-purple">
                    <div>
                        <div class="text-sm font-medium text-slate-800">Предупредување</div>
                        <div class="text-xs text-slate-500">Корисникот е известен, без ограничувања</div>
                    </div>
                </label>
                <label
                    class="sanction-option flex items-center gap-3 p-3 rounded-lg border border-my-purple/40 bg-my-purple/5 cursor-pointer">
                    <input type="radio" name="type" value="7-day" class="sanction-radio text-my-purple" checked>
                    <div>
                        <div class="text-sm font-medium text-slate-800">7-Day Ban <span
                                class="text-my-purple text-xs">(recommended)</span></div>
                        <div class="text-xs text-slate-500">Сметката е заклучена една недела</div>
                    </div>
                </label>
                @if (auth()->user()?->hasAnyRole(['admin', 'super_admin']))
                    <label
                        class="sanction-option flex items-center gap-3 p-3 rounded-lg border border-slate-200 cursor-pointer hover:bg-slate-50">
                        <input type="radio" name="type" value="permanent_ban" class="sanction-radio text-my-purple">
                        <div>
                            <div class="text-sm font-medium text-slate-800">Трајна забрана</div>
                            <div class="text-xs text-slate-500">Сметката е трајно оневозможена</div>
                        </div>
                    </label>
                @endif
                <label
                    class="sanction-option flex items-center gap-3 p-3 rounded-lg border border-slate-200 cursor-pointer hover:bg-slate-50">
                    <input type="radio" name="type" value="custom" class="sanction-radio text-my-purple">
                    <div class="flex-1">
                        <div class="text-sm font-medium text-slate-800">Прилагодено траење</div>
                        <input id="customDaysInput" type="number" name="days" min="1" placeholder="Денови"
                            class="hidden mt-2 w-24 rounded-lg border-slate-300 text-sm p-1.5 border">
                    </div>
                </label>
                <textarea rows="3" placeholder="Причина..." name="reason"
                    class="w-full rounded-lg text-sm p-3 border border-slate-200 "></textarea>
                <div class="flex gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('newSanctionModal').classList.add('hidden')">
                        Откажи
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-medium hover:bg-red-700">
                        Потврди санкција
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts-user-show')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const openBtn = document.getElementById('openSanctionModalBtn');
                const modal = document.getElementById('newSanctionModal');

                if (openBtn && modal) {
                    openBtn.addEventListener('click', () => {
                        modal.classList.remove('hidden');
                    });

                    // Optional: close when clicking outside the modal content
                    modal.addEventListener('click', (e) => {
                        if (e.target === modal) {
                            modal.classList.add('hidden');
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
