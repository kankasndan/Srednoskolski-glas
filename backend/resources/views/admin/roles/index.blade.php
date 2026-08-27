@extends('layouts.master')

@section('title', 'Персонал')

@section('content')
    <x-admin.page-header title="Персонал" subtitle="Додели или одземи улоги Админ и Модератор.">
        @can('grant roles')
            <button type="button" onclick="document.getElementById('grant-role-modal').classList.remove('hidden')"
                class="rounded-lg bg-my-purple px-4 py-2 text-sm font-medium text-white hover:bg-my-purple/90">
                + Додели улога
            </button>
        @endcan
    </x-admin.page-header>

    <div class="relative mb-6">
        <input type="text" id="staff-search" placeholder="Пребарај персонал по корисничко име..."
            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-my-purple/40 focus:outline-none">

        <div id="search-results"
            class="absolute top-full left-0 z-50 mt-1 hidden w-full rounded-lg border border-gray-200 bg-white shadow-lg">
        </div>
    </div>

    <x-admin.flash />

    <section class="mb-8 space-y-8">
        @foreach ($roleOrder as $roleKey)
            @php
                $staff = $staffByRole->get($roleKey, collect());
                $roleLabel = match ($roleKey) {
                    'super_admin' => 'Супер админи',
                    'admin' => 'Админи',
                    'moderator' => 'Модератори',
                    default => $roleKey,
                };
            @endphp
            <div>
                <h2 class="mb-3 text-sm font-semibold tracking-wide text-gray-500 uppercase">{{ $roleLabel }}</h2>
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-gray-500">
                            <tr>
                                <th class="px-4 py-3">Корисник</th>
                                <th class="px-4 py-3">Е-пошта</th>
                                <th class="px-4 py-3">Улога</th>
                                @if ($roleKey === 'moderator')
                                    <th class="px-4 py-3">Форум</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-left">
                            @forelse ($staff as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('role.show', $user) }}" class="flex items-center gap-3">
                                            <x-admin.avatar :user="$user" size="sm" />
                                            <span class="font-medium text-gray-800 hover:text-my-purple">{{ $user->username }}</span>
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500">{{ $user->email }}</td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700">{{ match ($user->role) {
                                                'super_admin' => 'Супер админ',
                                                'admin' => 'Админ',
                                                'moderator' => 'Модератор',
                                                default => $user->role,
                                            } }}</span>
                                    </td>
                                    @if ($roleKey === 'moderator')
                                        <td class="px-4 py-3 text-gray-800">
                                            {{ $user->forum?->name ?? 'Нема избран форум' }}
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $roleKey === 'moderator' ? 4 : 3 }}"
                                        class="px-4 py-8 text-center text-sm text-gray-400">
                                        Нема членови во оваа улога.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </section>

    <div id="grant-role-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-lg">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Додели улога на персонал</h3>
                <button type="button" onclick="document.getElementById('grant-role-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>

            <form action="{{ route('role.grant') }}" method="POST">
                @csrf

                <div class="relative mb-4">
                    <label class="text-sm font-medium text-gray-700">Пребарај корисник по корисничко име</label>
                    <input type="text" id="grant-search" autocomplete="off" placeholder="Внеси корисничко име..."
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-my-purple/40 focus:outline-none">

                    <input type="hidden" name="user_id" id="grant-selected-user-id">

                    <div id="grant-search-results"
                        class="absolute top-full left-0 z-50 mt-1 hidden max-h-40 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="text-sm font-medium text-gray-700">Додели улога</label>
                    <select name="role" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Избери улога</option>
                        @foreach ($assignableRoles as $assignableRole)
                            <option value="{{ $assignableRole }}">{{ match ($assignableRole) {
                                'super_admin' => 'Супер админ',
                                'admin' => 'Админ',
                                'moderator' => 'Модератор',
                                default => $assignableRole,
                            } }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('grant-role-modal').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Откажи</button>
                    <button type="submit"
                        class="rounded-lg bg-my-purple px-4 py-2 text-sm text-white hover:bg-my-purple/90">Додели улога</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            const roleShowTemplate = "{{ route('role.show', ['user' => '__ID__']) }}";
            const liveSearchUrl = "{{ route('role.liveSearch') }}";

            const searchInput = document.getElementById('staff-search');
            const resultsBox = document.getElementById('search-results');

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
                        href: roleShowTemplate.replace('__ID__', user.id),
                        primary: user.username ?? 'Нема корисничко име',
                        secondary: user.email,
                    }));
                });

                resultsBox.classList.remove('hidden');
            }
        </script>
    @endpush

    @push('scripts1')
        <script>
            const grantSearchUrl = "{{ route('role.grantSearch') }}";
            const grantSearchInput = document.getElementById('grant-search');
            const grantResultsBox = document.getElementById('grant-search-results');
            const grantSelectedUserId = document.getElementById('grant-selected-user-id');

            grantSearchInput.addEventListener('input', function() {
                const query = this.value.trim();
                grantSelectedUserId.value = '';

                if (query.length < 2) {
                    grantResultsBox.classList.add('hidden');
                    return;
                }

                fetch(`${grantSearchUrl}?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(users => renderGrantResults(users))
                    .catch(err => console.error(err));
            });

            function renderGrantResults(users) {
                grantResultsBox.innerHTML = '';

                if (users.length === 0) {
                    grantResultsBox.innerHTML = `<div class="px-4 py-3 text-sm text-gray-400">Нема совпаѓања</div>`;
                    grantResultsBox.classList.remove('hidden');
                    return;
                }

                users.forEach(user => {
                    const row = adminSearchRow({
                        primary: user.username ?? 'Нема корисничко име',
                        secondary: user.email,
                        onClick: () => {
                            grantSearchInput.value = user.username ?? '';
                            grantSelectedUserId.value = user.id;
                            grantResultsBox.classList.add('hidden');
                        },
                    });
                    row.className = 'px-3 py-2 hover:bg-gray-50 cursor-pointer flex justify-between text-sm';
                    grantResultsBox.appendChild(row);
                });

                grantResultsBox.classList.remove('hidden');
            }
        </script>
    @endpush
@endsection
