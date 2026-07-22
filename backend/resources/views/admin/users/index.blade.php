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
    <form action="{{ route('users.index') }}" method="GET" class="flex flex-wrap items-center gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by username or email..."
            class="flex-1 min-w-[220px] border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-my-purple/40 focus:outline-none">

        <select name="school" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">All schools</option>
            @foreach ($schools as $school)
                <option value="{{ $school->id }}" {{ request('school') == $school->id ? 'selected' : '' }}>
                    {{ $school->name }}
                </option>
            @endforeach
        </select>

        <select name="sign_in_method" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">All sign-in methods</option>
            <option value="google" {{ request('sign_in_method') == 'google' ? 'selected' : '' }}>Google</option>
            <option value="facebook" {{ request('sign_in_method') == 'facebook' ? 'selected' : '' }}>Facebook</option>
        </select>

        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">All statuses</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="banned" {{ request('status') == 'banned' ? 'selected' : '' }}>Banned</option>
        </select>

        <button type="submit" class="bg-my-purple text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-my-purple/90">
            Filter
        </button>

        @if (request()->anyFilled(['search', 'school', 'sign_in_method', 'status']))
            <a href="{{ route('users.index') }}" class="text-sm text-gray-500 hover:underline">Clear filters</a>
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
                    <tr class="cursor-pointer hover:bg-gray-50"
                        onclick="window.location='{{ route('users.show', $user->id) }}'">
                        <td class="px-4 py-3 flex items-center gap-3">
                            <img src="{{ $user->imageUrl ?? 'https://via.placeholder.com/32' }}" class="w-8 h-8 rounded-full object-cover">
                            <span class="font-medium text-gray-800">{{ $user->username }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $user->school->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $user->school->city ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 capitalize">{{ $user->sign_in_method ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-800 font-medium">{{ $user->karma ?? 0 }}</td>
                        <td class="px-4 py-3">
                            @if ($user->banned_until && $user->banned_until->isFuture())
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Banned</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Active</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right" onclick="event.stopPropagation()">
                            <a href="{{ route('users.show', $user->id) }}" class="text-my-purple text-xs font-medium hover:underline">
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
@endsection