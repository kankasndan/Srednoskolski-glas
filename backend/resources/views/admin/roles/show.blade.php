@extends('layouts.master')

@section('content')
    {{-- Page header --}}
    <div class="flex flex-col items-start justify-between mb-6">
        <div class="mb-6">
            <a href="{{ route('role.index') }}" class="text-sm text-gray-500 hover:text-my-purple flex items-center gap-1">
                &larr; Back to users
            </a>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Staff Profile</h1>
            <p class="text-sm text-gray-500">View and manage this staff member's role and permissions</p>
        </div>
        
    </div>

    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Profile overview card --}}
        <div class="lg:col-span-1">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 text-center">
                <img src="https://via.placeholder.com/96" class="w-24 h-24 rounded-full mx-auto mb-4">
                <h2 class="text-lg font-bold text-gray-800">{{ $user->username }}</h2>
                <p class="text-sm text-gray-500 mb-3">{{ $user->email }}</p>

                <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                    {{ $user->role == 'super_admin' ? 'bg-purple-100 text-purple-700' : ($user->role == 'admin' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700') }}">
                    {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                </span>

                <div class="mt-6 border-t border-gray-100 pt-4 text-left space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Joined</span>
                        <span class="text-gray-800">{{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Last updated</span>
                        <span class="text-gray-800">{{ $user->updated_at->diffForHumans() }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">User ID</span>
                        <span class="text-gray-800">#{{ $user->id }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Details + management --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Moderation info, only for moderators --}}
            @if ($user->role == 'moderator')
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 space-y-6">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Forum</h3>

                    @if ($user->forum)
                        <div class="flex items-center gap-4">
                            <img src="{{ $user->forum->imageUrl }}" class="w-12 h-12 rounded-lg object-cover">
                            <div>
                                <p class="font-medium text-gray-800">{{ $user->forum->name }}</p>
                                <p class="text-xs text-gray-500">{{ $user->forum->description }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col items-start gap-1">
                            <p class="text-sm text-gray-500 mb-3">Change moderator's forum</p>
                            <form action="{{ route('role.update.forum') }}" method="POST" class="flex gap-2">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <select name="forum" class="border border-gray-300 rounded-lg text-sm px-3 py-2 flex-1">
                                    <option value="">Select forum</option>
                                    @foreach ($forums as $forum)
                                        <option value="{{ $forum->id }}">{{ $forum->name }}</option>
                                    @endforeach
                                </select>
                                <button
                                    class="px-4 py-2 rounded-lg text-sm font-medium bg-green-100 text-black">Assign</button>
                            </form>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 mb-3">This moderator has no forum assigned yet.</p>
                        <form action="{{ route('role.update.forum') }}" method="POST" class="flex gap-2">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                            <select name="forum" class="border border-gray-300 rounded-lg text-sm px-3 py-2 flex-1">
                                <option value="">Select forum</option>
                                @foreach ($forums as $forum)
                                    <option value="{{ $forum->id }}">{{ $forum->name }}</option>
                                @endforeach
                            </select>
                            <button class="px-4 py-2 rounded-lg text-sm font-medium bg-green-100 text-black">Assign</button>
                        </form>
                    @endif
                </div>
            @endif

            {{-- Role management --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Role Management</h3>

                @if ($user->role == 'admin' || $user->role == 'moderator')
                    <div class="flex items-center gap-3">
                        <form action="{{ route('role.update', ['user' => $user->id]) }}" method="POST"
                            class="flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <select class="border border-gray-300 rounded-lg text-sm px-3 py-2" name="role">
                                <option value="super_admin">Super admin</option>
                                <option value="admin">Admin</option>
                                <option value="moderator">Moderator</option>
                            </select>
                            <button
                                class="px-4 py-2 rounded-lg text-sm font-medium bg-green-100 text-black hover:bg-green-200">
                                Update Role
                            </button>
                        </form>

                        <form action="{{ route('role.destroy', ['user' => $user->id]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button
                                class="px-4 py-2 rounded-lg text-sm font-medium text-red-600 border border-red-200 hover:bg-red-50">
                                Revoke Access
                            </button>
                        </form>
                    </div>
                @else
                    <p class="text-sm text-gray-400 italic">This account is protected and cannot be modified.</p>
                @endif
            </div>

            {{-- Activity stats placeholder --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Activity Overview</h3>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="text-xl font-bold text-gray-800">{{ $user->threads_count ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Threads</p>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-800">{{ $user->reports_reviewed_count ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Reports Reviewed</p>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-800">{{ $user->sanctions_issued_count ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Sanctions Issued</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
