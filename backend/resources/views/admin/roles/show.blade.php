@extends('layouts.master')

@section('title', 'Персонал: '.($user->username ?? 'Профил'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('role.index') }}" class="text-sm text-gray-500 hover:text-my-purple flex items-center gap-1">
            &larr; Назад кон персонал
        </a>
    </div>

    <x-admin.page-header title="Профил на персонал" subtitle="Прегледај и управувај со улогата на овој член од персоналот." />

    <x-admin.flash />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Profile overview card --}}
        <div class="lg:col-span-1">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 text-center">
                <x-admin.avatar :user="$user" size="2xl" class="mx-auto mb-4" />
                <h2 class="text-lg font-bold text-gray-800">{{ $user->username }}</h2>
                <p class="text-sm text-gray-500 mb-3">{{ $user->email }}</p>

                <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
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
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Последно ажурирање</span>
                        <span class="text-gray-800">{{ $user->updated_at->diffForHumans() }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">ID на корисник</span>
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
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Форум</h3>

                    @if ($user->forum)
                        <div class="flex items-center gap-4">
                            <img src="{{ $user->forum->imageUrl }}" class="w-12 h-12 rounded-lg object-cover">
                            <div>
                                <p class="font-medium text-gray-800">{{ $user->forum->name }}</p>
                                <p class="text-xs text-gray-500">{{ $user->forum->description }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col items-start gap-1">
                            <p class="text-sm text-gray-500 mb-3">Смени форум на модераторот</p>
                            <form action="{{ route('role.update.forum') }}" method="POST" class="flex gap-2">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <select name="forum" class="border border-gray-300 rounded-lg text-sm px-3 py-2 flex-1">
                                    <option value="">Избери форум</option>
                                    @foreach ($forums as $forum)
                                        <option value="{{ $forum->id }}">{{ $forum->name }}</option>
                                    @endforeach
                                </select>
                                <button
                                    class="px-4 py-2 rounded-lg text-sm font-medium bg-green-100 text-black">Додели</button>
                            </form>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 mb-3">Овој модератор сè уште нема доделен форум.</p>
                        <form action="{{ route('role.update.forum') }}" method="POST" class="flex gap-2">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                            <select name="forum" class="border border-gray-300 rounded-lg text-sm px-3 py-2 flex-1">
                                <option value="">Избери форум</option>
                                @foreach ($forums as $forum)
                                    <option value="{{ $forum->id }}">{{ $forum->name }}</option>
                                @endforeach
                            </select>
                            <button class="px-4 py-2 rounded-lg text-sm font-medium bg-green-100 text-black">Додели</button>
                        </form>
                    @endif
                </div>
            @endif

            {{-- Role management --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Управување со улоги</h3>

                @if ($canManage && count($assignableRoles) > 0)
                    <div class="flex items-center gap-3">
                        <form action="{{ route('role.update', ['user' => $user->id]) }}" method="POST"
                            class="flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <select class="border border-gray-300 rounded-lg text-sm px-3 py-2" name="role">
                                @foreach ($assignableRoles as $assignableRole)
                                    <option value="{{ $assignableRole }}" @selected($user->role === $assignableRole)>
                                        {{ match ($assignableRole) {
                                            'super_admin' => 'Супер админ',
                                            'admin' => 'Админ',
                                            'moderator' => 'Модератор',
                                            default => $assignableRole,
                                        } }}
                                    </option>
                                @endforeach
                            </select>
                            <button
                                class="px-4 py-2 rounded-lg text-sm font-medium bg-green-100 text-black hover:bg-green-200">
                                Ажурирај улога
                            </button>
                        </form>

                        <form action="{{ route('role.destroy', ['user' => $user->id]) }}" method="POST"
                            data-confirm="Одземи го пристапот на овој член од персоналот?">
                            @csrf
                            @method('DELETE')
                            <button
                                class="px-4 py-2 rounded-lg text-sm font-medium text-red-600 border border-red-200 hover:bg-red-50">
                                Одземи пристап
                            </button>
                        </form>
                    </div>
                @else
                    <p class="text-sm text-gray-400 italic">Оваа сметка е заштитена и не може да се измени.</p>
                @endif
            </div>

            {{-- Activity stats placeholder --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Преглед на активност</h3>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="text-xl font-bold text-gray-800">{{ $user->threads_count ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Дискусии</p>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-800">{{ $user->reports_reviewed_count ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Прегледани пријави</p>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-800">{{ $user->sanctions_issued_count ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Издадени санкции</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
