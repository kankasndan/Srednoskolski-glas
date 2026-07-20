@extends('layouts.master')

@section('content')
    {{-- Page header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Staff Management</h1>
            <p class="text-sm text-gray-500">Grant or revoke Admin and Moderator roles</p>
        </div>
        <a href="{{ route("role.index") }}" class="bg-my-purple text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-my-purple/90">
            Go back to all users
        </a>
    </div>

    {{-- Search bar --}}
    <div class="flex items-center gap-3 mb-6 relative">
        <input type="text" id="staff-search" placeholder="Search staff by email..."
            class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-my-purple/40 focus:outline-none">

        <div id="search-results"
            class="absolute left-0 top-full mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg hidden z-50">
        </div>
    </div>

    @if (session('success'))
        <span class="text-green-400 text-sm">{{ session('success') }}</span>
    @endif

    <section class="mb-8">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">{{ $user->role }}</h2>
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">



            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-left">
                    <tr>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">

                    <tr>
                        <td class="px-4 py-3 flex items-center gap-3">
                            <img src="https://via.placeholder.com/32" class="w-8 h-8 rounded-full">
                            <span class="font-medium text-gray-800">{{ $user->username }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">{{ $user->role }}</span>
                        </td>
                        @if ($user->role == 'admin' || $user->role == 'moderator')
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex gap-2">
                                    <div class="flex justify-between items-center gap-5">
                                        <form action="{{ route('role.update', ['user' => $user->id]) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <select class="border border-gray-300 rounded-lg text-xs px-2 py-1"
                                                name="role">
                                                <option value="super_admin">Super admin</option>
                                                <option value="admin">Admin</option>
                                                <option value="moderator">Moderator</option>
                                            </select>
                                            <button
                                                class="inline-flex items-center px-2.5 py-1 rounded-lg text-sm font-medium bg-green-100 text-black">Update</button>
                                        </form>
                                        <form action="{{ route('role.destroy', ['user' => $user->id]) }}" method="post">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-600 text-xs font-medium hover:underline">Revoke</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        @else
                            <td class="px-4 py-3 text-right">
                                <span class="text-xs text-gray-400 italic">Protected</span>
                            </td>
                        @endif
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
@endsection
