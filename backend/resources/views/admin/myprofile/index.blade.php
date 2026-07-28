@extends('layouts.master')

@section('content')
    {{-- Page header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">My Profile</h1>
            <p class="text-sm text-gray-500">Manage your account information and preferences</p>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Avatar + banner preview card --}}
        <div class="lg:col-span-1">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="h-24 bg-gradient-to-r from-my-purple to-purple-400 relative">
                    @if ($user->bannerUrl)
                        <img src="{{ $user->bannerUrl }}" class="w-full h-full object-cover">
                    @endif
                </div>
                <div class="p-6 text-center -mt-12">
                    <img src="{{ $user->imageUrl ?? 'https://via.placeholder.com/96' }}"
                        class="w-24 h-24 rounded-full mx-auto border-4 border-white shadow-sm object-cover">
                    <h2 class="text-lg font-bold text-gray-800 mt-3">{{ $user->username }}</h2>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>

                    <span
                        class="inline-flex items-center mt-3 px-3 py-1 rounded-full text-xs font-medium
                        {{ $user->role == 'super_admin' ? 'bg-purple-100 text-purple-700' : ($user->role == 'admin' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700') }}">
                        {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                    </span>

                    <div class="mt-6 border-t border-gray-100 pt-4 text-left space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Joined</span>
                            <span class="text-gray-800">{{ $user->created_at->format('M d, Y') }}</span>
                        </div>
                        @if ($user->moderatedForum)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Moderates</span>
                                <span class="text-gray-800">{{ $user->moderatedForum->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Editable sections --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Avatar & banner upload --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Profile Images</h3>
                <form action="{{ route('profile.updateImages', ['user' => $user->id]) }}" method="POST"
                    enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="text-sm font-medium text-gray-700">Avatar</label>
                        <input type="file" name="avatar" accept="image/*"
                            class="mt-1 block w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Banner</label>
                        <input type="file" name="banner" accept="image/*"
                            class="mt-1 block w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                    </div>

                    <button type="submit"
                        class="px-4 py-2 rounded-lg text-sm font-medium bg-my-purple text-white hover:bg-my-purple/90">
                        Upload Images
                    </button>
                </form>
            </div>

            {{-- Basic info --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Basic Information</h3>
                <form action="{{ route('profile.update', ['user' => $user->id]) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="text-sm font-medium text-gray-700">Username</label>
                        <input type="text" name="username" value="{{ old('username', $user->username) }}"
                            class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-my-purple/40 focus:outline-none">
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                            class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-my-purple/40 focus:outline-none">
                    </div>

                    <button type="submit"
                        class="px-4 py-2 rounded-lg text-sm font-medium bg-my-purple text-white hover:bg-my-purple/90">
                        Save Changes
                    </button>
                </form>
            </div>

            {{-- Password change --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Change Password</h3>
                <form action="{{ route('profile.updatePassword', ['user' => $user->id]) }}" method="POST"
                    class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="text-sm font-medium text-gray-700">Current Password</label>
                        <div class="relative mt-1">
                            <input type="password" name="current_password" id="current_password"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-10 text-sm focus:ring-2 focus:ring-my-purple/40 focus:outline-none">
                            <button type="button" onclick="togglePassword('current_password', 'currentPasswordIcon')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i id="currentPasswordIcon" class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">New Password</label>
                        <div class="relative mt-1">
                            <input type="password" name="password" id="password"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-10 text-sm focus:ring-2 focus:ring-my-purple/40 focus:outline-none">
                            <button type="button" onclick="togglePassword('password', 'passwordIcon')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i id="passwordIcon" class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Confirm New Password</label>
                        <div class="relative mt-1">
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-10 text-sm focus:ring-2 focus:ring-my-purple/40 focus:outline-none">
                            <button type="button" onclick="togglePassword('password_confirmation', 'confirmPasswordIcon')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i id="confirmPasswordIcon" class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit"
                        class="px-4 py-2 rounded-lg text-sm font-medium bg-red-50 text-red-600 border border-red-200 hover:bg-red-100">
                        Update Password
                    </button>
                </form>
            </div>

        </div>
    </div>
    @push('scripts-profile')
        <script>
            function togglePassword(inputId, iconId) {
                const input = document.getElementById(inputId);
                const icon = document.getElementById(iconId);

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }
        </script>
    @endpush
@endsection
