@extends('layouts.master')

@section('title', 'Мој профил')

@section('content')
    <x-admin.page-header title="Мој профил" subtitle="Управувај со податоците и поставките на сметката." />

    <x-admin.flash />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Avatar + banner preview card --}}
        <div class="lg:col-span-1">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">

                <div class="p-6 text-center">
                    <x-admin.avatar :user="$user" size="2xl" class="mx-auto border-4 border-white shadow-sm" />
                    <h2 class="text-lg font-bold text-gray-800 mt-3">{{ $user->username }}</h2>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>

                    <span
                        class="inline-flex items-center mt-3 px-3 py-1 rounded-full text-xs font-medium
                        {{ $user->role == 'super_admin' ? 'bg-my-purple/10 text-my-purple' : ($user->role == 'admin' ? 'bg-my-blue text-my-purple' : 'bg-green-100 text-green-700') }}">
                        {{ match ($user->role) {
                            'super_admin' => 'Супер админ',
                            'admin' => 'Админ',
                            'moderator' => 'Модератор',
                            default => $user->role,
                        } }}
                    </span>

                    <div class="mt-6 border-t border-gray-100 pt-4 text-left space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Приклучен</span>
                            <span class="text-gray-800">{{ $user->created_at->format('M d, Y') }}</span>
                        </div>
                        @if ($user->moderatedForum)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Модерира</span>
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
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Профилни слики</h3>
                <form action="{{ route('profile.updateImages', ['user' => $user->id]) }}" method="POST"
                    enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="text-sm font-medium text-gray-700">Аватар</label>
                        <input type="file" name="image" accept="image/*"
                            class="mt-1 block w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                    </div>

                    <button type="submit"
                        class="px-4 py-2 rounded-lg text-sm font-medium bg-my-purple text-white hover:bg-my-purple/90">
                        Прикачи слики
                    </button>
                </form>
            </div>

            {{-- Basic info --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Основни информации</h3>
                <form action="{{ route('profile.update', ['user' => $user->id]) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="text-sm font-medium text-gray-700">Корисничко име</label>
                        <input type="text" name="username" value="{{ old('username', $user->username) }}"
                            class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-my-purple/40 focus:outline-none">
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Е-пошта</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                            class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-my-purple/40 focus:outline-none">
                    </div>

                    <button type="submit"
                        class="px-4 py-2 rounded-lg text-sm font-medium bg-my-purple text-white hover:bg-my-purple/90">
                        Зачувај промени
                    </button>
                </form>
            </div>

            {{-- Password change --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Промени лозинка</h3>
                <form action="{{ route('profile.updatePassword', ['user' => $user->id]) }}" method="POST"
                    class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="text-sm font-medium text-gray-700">Тековна лозинка</label>
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
                        <label class="text-sm font-medium text-gray-700">Нова лозинка</label>
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
                        <label class="text-sm font-medium text-gray-700">Потврди нова лозинка</label>
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
                        Ажурирај лозинка
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
