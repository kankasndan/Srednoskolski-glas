{{-- resources/views/admin/sanctions/index.blade.php --}}
@extends('layouts.master')

@section('title', 'Санкции')

@section('content')
    <x-admin.page-header title="Санкции" subtitle="Управувај со предупредувања, забрани и жалби на корисници.">
            <button type="button" onclick="document.getElementById('newSanctionModal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 rounded-lg bg-my-purple px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-my-purple/90">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Нова санкција
            </button>
    </x-admin.page-header>

        <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-4">
            <a href="{{ route('sanction.index', ['tab' => 'active']) }}" class="rounded-xl border border-gray-200 bg-white p-4 hover:border-my-purple/40">
                <p class="text-xs text-gray-500">Активни забрани</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($activeBansCount) }}</p>
            </a>
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <p class="text-xs text-gray-500">Предупредувања (30 дена)</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $warnings30Days }}</p>
            </div>
            <a href="{{ route('appeal.index') }}" class="rounded-xl border border-gray-200 bg-white p-4 hover:border-my-purple/40">
                <p class="text-xs text-gray-500">Чекаат жалба</p>
                <p class="mt-1 text-2xl font-bold text-amber-600">{{ $appeals }}</p>
            </a>
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <p class="text-xs text-gray-500">Трајни забрани</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $permanentBansCount }}</p>
            </div>
        </div>

        <div class="mb-6 border-b border-gray-200">
            <nav class="flex gap-6 -mb-px">
                <a href="{{ route('sanction.index', ['tab' => 'active']) }}"
                    class="border-b-2 pb-3 text-sm font-medium {{ $tab === 'active' ? 'border-my-purple text-my-purple' : 'border-transparent text-gray-500 hover:text-gray-700' }}">Активни санкции</a>
                <a href="{{ route('sanction.index', ['tab' => 'history']) }}"
                    class="border-b-2 pb-3 text-sm font-medium {{ $tab === 'history' ? 'border-my-purple text-my-purple' : 'border-transparent text-gray-500 hover:text-gray-700' }}">Историја</a>
            </nav>
        </div>

        <x-admin.flash />
        @if ($tab === 'active')
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                        <tr>
                            <th class="text-left px-4 py-3">Корисник</th>
                            <th class="text-left px-4 py-3">Тип</th>
                            <th class="text-left px-4 py-3">Причина</th>
                            <th class="text-left px-4 py-3">Издадено од</th>
                            <th class="text-left px-4 py-3">Останато време</th>
                            <th class="text-left px-4 py-3">Претходни прекршоци</th>
                            <th class="text-right px-4 py-3">Акција</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($activeSanctions as $sanction)
                        @if($sanctionId && $sanction->id == $sanctionId)
                            <tr class="bg-gray-100">
                        @else
                            <tr class="hover:bg-gray-50">
                        @endif
                                <td class="px-4 py-3">
                                    <a href="{{ route('user.show', $sanction->user) }}" class="flex items-center gap-2">
                                        <x-admin.avatar :user="$sanction->user" size="sm" />
                                        <span class="font-medium text-gray-900 hover:text-my-purple">{{ $sanction->user->username }}</span>
                                    </a>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="text-xs font-medium px-2 py-1 rounded-full {{ match ($sanction->type) {
                                            'warning' => 'bg-my-yellow/30 text-gray-800',
                                            'permanent_ban' => 'bg-red-100 text-red-700',
                                            '7-day' => 'bg-green-100 text-green-600',
                                            default => 'bg-gray-100 text-gray-600',
                                        } }}">
                                        {{ match ($sanction->type) {
                                            'warning' => 'Предупредување',
                                            'permanent_ban' => 'Трајна забрана',
                                            '7-day' => '7-дневна забрана',
                                            'custom' => 'Прилагодена',
                                            default => $sanction->type,
                                        } }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $sanction->reason }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $sanction->issuedBy?->username ?? '—' }}</td>
                                @if ($sanction->expires_at != null)
                                    <td class="px-4 py-3 text-gray-600">{{ $sanction->expires_at->diffForHumans() }}</td>
                                @else
                                    <td class="px-4 py-3 text-gray-600">Трајна</td>
                                @endif
                                <td class="px-4 py-3 text-gray-600">{{ $sanction->user->sanctions()->count() }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if ($sanction->type == 'permanent_ban')
                                        @can('remove permanent sanctions')
                                            <form action="{{ route('sanction.remove', ['sanction' => $sanction->id]) }}"
                                                method="POST" data-confirm="Отстрани ја оваа санкција?">
                                                @csrf
                                                @method('DELETE')
                                                <button class="font-medium text-my-purple hover:underline">Тргни
                                                    санкција</button>
                                            </form>
                                        @endcan
                                    @else
                                        @can('remove sanctions')
                                            <form action="{{ route('sanction.remove', ['sanction' => $sanction->id]) }}"
                                                method="POST" data-confirm="Отстрани ја оваа санкција?">
                                                @csrf
                                                @method('DELETE')
                                                <button class="font-medium text-my-purple hover:underline">Тргни
                                                    санкција</button>
                                            </form>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-400">Нема активни санкции.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <x-admin.pagination :paginator="$activeSanctions" />
        @else
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                        <tr>
                            <th class="text-left px-4 py-3">Корисник</th>
                            <th class="text-left px-4 py-3">Тип</th>
                            <th class="text-left px-4 py-3">Статус</th>
                            <th class="text-left px-4 py-3">Датум</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($expiredSanctions as $sanction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    @if ($sanction->user?->role === 'user')
                                        <a href="{{ route('user.show', $sanction->user) }}" class="flex items-center gap-2">
                                            <x-admin.avatar :user="$sanction->user" size="sm" />
                                            <span class="font-medium text-gray-900 hover:text-my-purple">{{ $sanction->user->username }}</span>
                                        </a>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <x-admin.avatar :user="$sanction->user" size="sm" />
                                            <span class="font-medium text-gray-900">{{ $sanction->user?->username ?? '—' }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3"><span
                                        class="bg-red-100 text-red-700 text-xs font-medium px-2 py-1 rounded-full">{{ match ($sanction->type) {
                                            'warning' => 'Предупредување',
                                            'permanent_ban' => 'Трајна забрана',
                                            '7-day' => '7-дневна забрана',
                                            'custom' => 'Прилагодена',
                                            default => $sanction->type,
                                        } }}</span>
                                </td>
                                <td class="px-4 py-3 text-green-600">{{ $sanction->issued_by ? 'Избришана' : 'Истечена' }}
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $sanction->deleted_at?->format('d.m.Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-gray-400">Нема историја на санкции.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <x-admin.pagination :paginator="$expiredSanctions" />
        @endif

    <!-- NEW SANCTION MODAL -->
    <div id="newSanctionModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
        <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Издај санкција</h2>
                <button onclick="document.getElementById('newSanctionModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>

            <div class="space-y-4">
                <form action="{{ route('sanction.create') }}" method="POST">
                    <div class="relative">
                        <input type="text" id="user-search" name="search" value="{{ request('search') }}"
                            placeholder="Пребарај по корисничко име или е-пошта..."
                            class="flex-1 min-w-[220px] border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-my-purple/40 focus:outline-none">

                        <div id="search-results"
                            class="absolute left-0 top-full mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg hidden z-50">
                        </div>
                    </div>
                    <input type="hidden" id="selected-user-id" name="user_id">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-2">Тип на санкција</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label
                                class="flex items-center gap-2 border border-gray-300 rounded-lg px-3 py-2 text-sm cursor-pointer">
                                <input type="radio" name="type" value="warning" class="text-red-600">
                                Предупредување
                            </label>
                            <label
                                class="flex items-center gap-2 border border-red-500 bg-red-50 rounded-lg px-3 py-2 text-sm cursor-pointer">
                                <input type="radio" name="type" value="7-day" checked class="text-red-600">
                                7-дневна забрана
                            </label>
                            @if (auth()->user()
                                    ?->hasAnyRole(['admin', 'super_admin']))
                                <label
                                    class="flex items-center gap-2 border border-gray-300 rounded-lg px-3 py-2 text-sm cursor-pointer">
                                    <input type="radio" name="type" value="permanent_ban" class="text-red-600">
                                    Трајна
                                    забрана
                                </label>
                            @endif
                            <label
                                class="flex items-center gap-2 border border-gray-300 rounded-lg px-3 py-2 text-sm cursor-pointer">
                                <input type="radio" name="type" value="custom" class="text-red-600"> Прилагодено
                                траење
                            </label>
                        </div>
                    </div>

                    <div id="customDurationField" class="hidden">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Број на дена</label>
                        <input type="number" min="1" name="days"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Причина</label>
                        <textarea rows="3" placeholder="Опиши причината за санкцијата..." name="reason"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button"
                            onclick="document.getElementById('newSanctionModal').classList.add('hidden')"
                            class="text-sm font-medium text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-100">Откажи</button>

                        <button type="submit"
                            class="text-sm font-medium text-white bg-my-purple hover:bg-my-purple/90 px-4 py-2 rounded-lg">Потврди
                            санкција</button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    @push('scripts-sanctions')
        <script>
            document.querySelectorAll('input[name="type"]').forEach((radio) => {
                radio.addEventListener('change', function() {
                    const isCustom = this.value === 'custom' && this.checked;
                    document.getElementById('customDurationField')
                        .classList.toggle('hidden', !isCustom);
                });
            });

            const liveSearchUrl = "{{ route('user.liveSearch') }}";

            const searchInput = document.getElementById('user-search');
            const resultsBox = document.getElementById('search-results');
            const selectedUserId = document.getElementById('selected-user-id');

            searchInput.addEventListener('input', function() {
                const query = this.value.trim();

                if (query.length < 2) {
                    resultsBox.classList.add('hidden');
                    return;
                }

                fetch(`${liveSearchUrl}?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(users => renderResults(users))
                    .catch(err => console.error(err));
            });

            function renderResults(users) {
                resultsBox.innerHTML = '';

                if (users.length === 0) {
                    resultsBox.innerHTML = `<div class="px-4 py-3 text-sm text-gray-400">Нема совпаѓања</div>`;
                    resultsBox.classList.remove('hidden');
                    return;
                }

                users.forEach(user => {
                    resultsBox.appendChild(adminSearchRow({
                        primary: user.username ?? 'Нема корисничко име',
                        secondary: user.email ?? '',
                        onClick: () => {
                            searchInput.value = user.username ?? '';
                            selectedUserId.value = user.id;
                            resultsBox.classList.add('hidden');
                        },
                    }));
                });

                resultsBox.classList.remove('hidden');
            }
        </script>
    @endpush
@endsection
