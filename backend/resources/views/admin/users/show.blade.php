@extends('layouts.master')

@section('content')
    <div class="mb-6">
        <a href="{{ route('user.index') }}" class="text-sm text-gray-500 hover:text-my-purple flex items-center gap-1">
            &larr; Back to users
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
                <h1 class="text-xl font-bold text-gray-800">{{ $user->username ?? 'No username' }}</h1>

                @if ($user->sanctions->contains(fn ($s) => $s->type !== 'warning' && ($s->expires_at === null || $s->expires_at->isFuture())))
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Banned</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Active</span>
                @endif

                @foreach ($user->roles as $role)
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-my-purple capitalize">
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
                <p class="text-xs text-gray-400 uppercase tracking-wide">Karma</p>
                <p class="text-2xl font-bold text-gray-800">{{ $user->karma ?? 0 }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide">Streak</p>
                <p class="text-2xl font-bold text-gray-800">{{ $user->current_streak ?? 0 }}</p>
            </div>
        </div>

        <div class="flex gap-3">
            {{-- {{ route('users.sanction', $user->id) }} --}}
            <a href="" 
                class="bg-red-600 text-white px-3 py-2 rounded-lg text-xs font-medium hover:bg-red-700">
                Sanction
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Account details --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-800 uppercase tracking-wide mb-2">Account Details</h2>

            <div class="text-sm">
                <p class="text-gray-400">Sign-in method</p>
                <p class="text-gray-800 font-medium capitalize">{{ $user->provider ?? '—' }}</p>
            </div>

            <div class="text-sm">
                <p class="text-gray-400">Joined</p>
                <p class="text-gray-800 font-medium">{{ $user->created_at?->format('M d, Y') ?? '—' }}</p>
            </div>

            <div class="text-sm">
                <p class="text-gray-400">Email verified</p>
                <p class="text-gray-800 font-medium">
                    {{ $user->email_verified_at ? $user->email_verified_at->format('M d, Y') : 'Not verified' }}
                </p>
            </div>

            <div class="text-sm">
                <p class="text-gray-400">Onboarding completed</p>
                <p class="text-gray-800 font-medium">
                    {{ $user->onboarding_completed_at ? $user->onboarding_completed_at->format('M d, Y') : 'Not completed' }}
                </p>
            </div>

            <div class="text-sm">
                <p class="text-gray-400">Language</p>
                <p class="text-gray-800 font-medium">{{ $user->language ?? '—' }}</p>
            </div>

            <div class="text-sm">
                <p class="text-gray-400">Theme / Accent</p>
                <p class="text-gray-800 font-medium capitalize">
                    {{ $user->theme ?? '—' }} / {{ $user->accent_color ?? '—' }}
                </p>
            </div>
        </div>

        {{-- Student data --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-800 uppercase tracking-wide mb-2">Student Info</h2>

            @if ($user->studentData)
                <div class="text-sm">
                    <p class="text-gray-400">School</p>
                    <p class="text-gray-800 font-medium">{{ $user->studentData->school->name ?? '—' }}</p>
                </div>
                <div class="text-sm">
                    <p class="text-gray-400">City</p>
                    <p class="text-gray-800 font-medium">{{ $user->studentData->school->city->name ?? '—' }}</p>
                </div>
                <div class="text-sm">
                    <p class="text-gray-400">Vocation</p>
                    <p class="text-gray-800 font-medium">{{ $user->studentData->vocation->name ?? '—' }}</p>
                </div>
                <div class="text-sm">
                    <p class="text-gray-400">Grade</p>
                    <p class="text-gray-800 font-medium">{{ $user->studentData->grade ?? '—' }}</p>
                </div>
            @else
                <p class="text-sm text-gray-400">No student profile set up.</p>
            @endif

            <div class="text-sm pt-2 border-t border-gray-100">
                <p class="text-gray-400 mb-1">Feed topics</p>
                <div class="flex flex-wrap gap-1">
                    @forelse ($user->topics as $topic)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                            {{ $topic->name }}
                        </span>
                    @empty
                        <span class="text-gray-400 text-sm">No topics selected</span>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Sanctions --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h2 class="text-sm font-semibold text-gray-800 uppercase tracking-wide mb-4">Sanction History</h2>

            @forelse ($user->sanctions as $sanction)
                <div class="border-b border-gray-100 last:border-0 py-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-800 capitalize">{{ str_replace('_', ' ', $sanction->type) }}</span>
                        @if ($sanction->type !== 'warning' && ($sanction->expires_at === null || $sanction->expires_at->isFuture()))
                            <span class="text-xs font-medium text-red-600">Active</span>
                        @else
                            <span class="text-xs font-medium text-gray-400">Inactive</span>
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
                <p class="text-sm text-gray-400">No sanctions on record.</p>
            @endforelse
        </div>
    </div>

    {{-- Social + activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 text-center">
            <p class="text-xs text-gray-400 uppercase">Followers</p>
            {{-- <p class="text-2xl font-bold text-gray-800">{{ $user->followers_count ?? $user->followers->count() }}</p> --}}
        </div>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 text-center">
            <p class="text-xs text-gray-400 uppercase">Following</p>
            {{-- <p class="text-2xl font-bold text-gray-800">{{ $user->following_count ?? $user->following->count() }}</p> --}}
        </div>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 text-center">
            <p class="text-xs text-gray-400 uppercase">Blocked Users</p>
            {{-- <p class="text-2xl font-bold text-gray-800">{{ $user->blockedUsers->count() }}</p> --}}
        </div>
    </div>

    {{-- Recent threads --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mt-6">
        <h2 class="text-sm font-semibold text-gray-800 uppercase tracking-wide mb-4">Recent Threads</h2>
        @forelse ($user->threads as $thread)
            <div class="border-b border-gray-100 last:border-0 py-2 text-sm">
                <p class="font-medium text-gray-800">{{ $thread->title }}</p>
                <p class="text-xs text-gray-400">{{ $thread->created_at?->format('M d, Y') }} &middot; {{ $thread->upvotes_count ?? 0 }} upvotes</p>
            </div>
        @empty
            <p class="text-sm text-gray-400">No threads posted.</p>
        @endforelse
    </div>

    <div class="mt-6">
        <a href="{{ route('user.export', $user->id) }}"
            class="bg-my-purple text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-my-purple/90 inline-flex items-center gap-2">
            Export Profile as PDF
        </a>
    </div>
@endsection