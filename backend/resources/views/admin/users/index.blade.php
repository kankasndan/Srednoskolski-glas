@extends('layouts.master')

@section('content')
    {{-- Page header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Users</h1>
            <p class="text-sm text-gray-500">Search and manage every registered user on the platform</p>
        </div>
        <div class="text-sm text-gray-500">
            Total: <span class="font-semibold text-gray-800">{{ $users->total() }}</span> users
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- Search + Filters --}}
    <form action="{{ route('user.index') }}" method="GET" class="flex flex-wrap items-center gap-3 mb-6">

        <div class="flex items-center gap-3 relative">
            <input type="text" id="user-search" name="search" value="{{ request('search') }}"
                placeholder="Search by username or email..."
                class="flex-1 min-w-[220px] border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-my-purple/40 focus:outline-none">

            <div id="search-results"
                class="absolute left-0 top-full mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg hidden z-50">
            </div>
        </div>
        <select name="school" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">All schools</option>
            @foreach ($schools as $school)
                <option value="{{ $school->id }}" {{ request('school') == $school->id ? 'selected' : '' }}>
                    {{ $school->name }}
                </option>
            @endforeach
        </select>

        <select name="provider" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">All sign-in methods</option>
            <option value="google" {{ request('sign_in_method') == 'google' ? 'selected' : '' }}>Google</option>
            <option value="facebook" {{ request('sign_in_method') == 'facebook' ? 'selected' : '' }}>Facebook</option>
        </select>

        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">All statuses</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="banned" {{ request('status') == 'banned' ? 'selected' : '' }}>Banned</option>
        </select>

        <button type="submit"
            class="bg-my-purple text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-my-purple/90">
            Filter
        </button>

        @if (request()->anyFilled(['search', 'school', 'sign_in_method', 'status']))
            <a href="{{ route('user.index') }}" class="text-sm text-gray-500 hover:underline">Clear filters</a>
        @endif
    </form>

    {{-- Users table --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-left">
                <tr>
                    <th class="px-4 py-3">User</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">School</th>
                    <th class="px-4 py-3">City</th>
                    <th class="px-4 py-3">Sign-in</th>
                    <th class="px-4 py-3">Karma</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($users as $user)
                    <tr class="cursor-pointer hover:bg-gray-50" onclick="window.location='{{ route('user.show', $user->id) }}'">
                        <td class="px-4 py-3 flex items-center gap-3">
                            <img src="{{ $user->imageUrl ?? 'https://via.placeholder.com/32' }}"
                                class="w-8 h-8 rounded-full object-cover">
                            <span class="font-medium text-gray-800">{{ $user->username }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $user->studentData->school->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $user->studentData->school->city->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 capitalize">{{ $user->provider ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-800 font-medium">{{ $user->karma ?? 0 }}</td>
                        <td class="px-4 py-3">
                            @if ($user->sanctions()->exists())
                                @foreach ($user->sanctions as $sanction)
                                    @if ($sanction->type != 'warning' && ($sanction->expires_at === null || $sanction->expires_at->isFuture()))
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Banned</span>
                                    @endif
                                @endforeach
                            @else
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Active</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right" onclick="event.stopPropagation()">
                            <a href="{{ route('user.show', $user->id) }}"
                                class="text-my-purple text-xs font-medium hover:underline">
                                View Profile
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-400 text-sm">
                            No users found matching your filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $users->withQueryString()->links() }}
    </div>

    @push('scripts')
        <script>
            const roleShowTemplate = "{{ route('user.show', ['user' => '__ID__']) }}";
            const liveSearchUrl = "{{ route('user.liveSearch') }}";

            const searchInput = document.getElementById('user-search');
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
                    resultsBox.innerHTML = `<div class="px-4 py-3 text-sm text-gray-400">No matching users</div>`;
                    resultsBox.classList.remove('hidden');
                    return;
                }

                users.forEach(user => {
                    const row = document.createElement('a');
                    row.href = roleShowTemplate.replace('__ID__', user.id);
                    row.className =
                        'block px-4 py-2 hover:bg-gray-50 cursor-pointer flex justify-between items-center text-sm border-b border-gray-100 last:border-0 no-underline text-inherit';
                    row.innerHTML = `
                <span class="font-medium text-gray-800">${user.username ?? 'No username'}</span>
                <span class="text-gray-400 text-xs">${user.email}</span>
            `;
                    resultsBox.appendChild(row);
                });

                resultsBox.classList.remove('hidden');
            }
        </script>
    @endpush
@endsection
