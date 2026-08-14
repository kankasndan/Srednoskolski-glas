@extends('layouts.master')

@section('title', 'Детали за форум')

@section('content')
<div class="max-w-7xl mx-auto w-full px-4 py-6 space-y-6">

    {{-- Back link --}}
    <a href="{{ route('forum.index') }}" class="text-sm text-my-purple hover:underline mb-6">
        &larr; Назад кон форуми
    </a>

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

    {{-- Forum Header: banner, icon, name, description, type, city, slug --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        @if ($forum->bannerUrl)
            <div class="h-32 w-full bg-gray-200 bg-cover bg-center"
                style="background-image: url('{{ $forum->bannerUrl }}')"></div>
        @else
            <div class="h-32 w-full bg-gray-200"></div>
        @endif

        <div class="p-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div class="flex items-start gap-4">
                @if ($forum->imageUrl)
                    <img src="{{ $forum->imageUrl }}" alt=""
                        class="w-16 h-16 rounded-xl object-cover bg-gray-100 -mt-14 border-4 border-white shrink-0">
                @else
                    <div class="w-16 h-16 rounded-xl bg-gray-300 -mt-14 border-4 border-white shrink-0"></div>
                @endif

                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-semibold text-gray-900">{{ $forum->name }}</h1>
                        <span class="text-xs bg-my-purple/10 text-my-purple rounded-full px-2 py-0.5">{{ match ($forum->type) {
                            'general' => 'Општ',
                            'school' => 'Училишен',
                            default => $forum->type,
                        } }}</span>
                    </div>
                    @if($forum->school && $forum->school->city)
                        <p class="text-sm text-gray-500 mt-1">Град: {{ $forum->school->city->name }}</p>
                    @endif
                    <p class="text-sm text-gray-500 mt-1">Слаг: {{ $forum->slug }}</p>
                    <p class="text-gray-600 text-sm mt-2 max-w-xl">
                        {{ $forum->description }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @can('update forums')
                    <button type="button" onclick="openForumModal()"
                        class="px-4 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                        Уреди форум
                    </button>
                @endcan
                @can('delete forums')
                    <button type="button" onclick="openDeleteForumModal()"
                        class="px-4 py-2 rounded-md bg-red-600 text-white text-sm font-medium hover:bg-red-700">
                        Избриши форум
                    </button>
                @endcan
            </div>
        </div>
    </div>

    {{-- Stats row: threads, comments, followers --}}
    <div class="flex justify-between items-center w-full gap-4">
        <div class="bg-white rounded-xl shadow p-5">
            <h6 class="text-sm text-gray-500 mb-1">Вкупно дискусии</h6>
            <p class="text-2xl font-semibold text-gray-900">{{ $forum->threads_count }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <h6 class="text-sm text-gray-500 mb-1">Следбеници</h6>
            <p class="text-2xl font-semibold text-gray-900">{{ $forum->members_count }}</p>
        </div>
    </div>

    {{-- Threads in this forum --}}
    <div class="bg-white rounded-xl shadow">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Дискусии во овој форум</h2>
        </div>

        <div id="forum-threads" class="divide-y divide-gray-100">

            {{-- Single thread row (repeat per thread) --}}
            @forelse ($threads as $thread)
            <div class="flex items-center justify-between px-5 py-4 shadow-sm shadow-gray-300/50">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-gray-200 shrink-0"></div>

                    <div>
                        <div class="flex items-center gap-2">
                            <a href="#" class="font-medium text-gray-900 hover:underline">{{ $thread->name }}</a>
                            <span class="text-xs bg-gray-100 text-gray-500 rounded-full px-2 py-0.5">Анонимно</span>
                            <span class="text-xs bg-orange-100 text-orange-600 rounded-full px-2 py-0.5">Под преглед</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">од {{ $thread->user->username }} · {{ $thread->created_at?->diffForHumans() }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-6 text-sm text-gray-500">
                    <span>▲ {{ $thread->upvotes }} гласови</span>
                    <span>💬 {{ $thread->comments_count }} коментари</span>
                    <button type="button"
                    {{-- {{ route('thread.destroy', ['thread' => $thread->id]) }} --}}
                        onclick="openDeleteThreadModal('', '{{ $thread->name }}')"
                        class="text-red-600 hover:underline text-xs">
                        Избриши
                    </button>
                </div>
            </div>
            @empty
            {{-- End single thread row --}}

            {{-- Empty state --}}
            <div class="px-5 py-10 text-center text-sm text-gray-400">
                Сè уште нема дискусии во овој форум.
            </div>

            @endforelse

        </div>

        {{-- Pagination for threads --}}
        <div class="flex justify-center p-3">
            <nav class="flex gap-1 text-sm">
                @if ($threads->onFirstPage())
                    <button disabled class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-400 cursor-not-allowed">
                        Претходна
                    </button>
                @else
                    <a href="{{ $threads->previousPageUrl() }}"
                        class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">
                        Претходна
                    </a>
                @endif

                @if ($threads->hasMorePages())
                    <a href="{{ $threads->nextPageUrl() }}"
                        class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">
                        Следна
                    </a>
                @else
                    <button disabled class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-400 cursor-not-allowed">
                        Следна
                    </button>
                @endif
            </nav>
        </div>
    </div>

</div>

{{-- Уреди форум Modal --}}
<div class="fixed inset-0 bg-black/40 hidden items-center justify-center" id="forumModal">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Уреди форум</h2>
            <button type="button" onclick="closeForumModal()" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <form class="space-y-3" method="POST" action="{{ route('forum.update', ['forum' => $forum->id]) }}" enctype="multipart/form-data">
            @csrf
            @method('patch')
            <div>
                <label class="text-sm text-gray-600">Име</label>
                <input type="text" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" name="name" value="{{ $forum->name }}">
            </div>

            <div>
                <label class="text-sm text-gray-600">Слаг</label>
                <input type="text" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" name="slug" value="{{ $forum->slug }}">
            </div>

            <div>
                <label class="text-sm text-gray-600">Опис</label>
                <textarea rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" name="description">{{ $forum->description }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-sm text-gray-600">Икона</label>
                    @if ($forum->imageUrl)
                        <img src="{{ $forum->imageUrl }}" alt="" class="mb-2 h-12 w-12 rounded-lg object-cover">
                    @endif
                    <input type="file" accept="image/*"
                        class="w-full text-sm border border-gray-300 p-2 rounded-md" name="icon">
                    <p class="text-xs text-gray-400 mt-1">Остави празно за да се задржи тековната икона.</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Банер</label>
                    @if ($forum->bannerUrl)
                        <img src="{{ $forum->bannerUrl }}" alt="" class="mb-2 h-12 w-full rounded-lg object-cover">
                    @endif
                    <input type="file" accept="image/*"
                        class="w-full text-sm border border-gray-300 p-2 rounded-md" name="banner">
                    <p class="text-xs text-gray-400 mt-1">Остави празно за да се задржи тековниот банер.</p>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeForumModal()"
                    class="px-4 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">Откажи</button>
                <button type="submit"
                    class="px-4 py-2 rounded-md bg-my-purple text-white text-sm font-medium hover:bg-my-purple/90">Зачувај форум</button>
            </div>
        </form>
    </div>
</div>

{{-- Избриши форум Modal --}}
<div class="fixed inset-0 bg-black/40 hidden items-center justify-center" id="deleteForumModal">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
        <h2 class="text-lg font-semibold text-gray-900">Избриши форум</h2>
        <p class="text-sm text-gray-600">
            Ова трајно ќе го избрише <span class="font-medium text-gray-900">{{ $forum->name }}</span> и ќе ги отстрани сите дискусии, коментари, зачувувања и следења. Оваа акција не може да се поништи.
        </p>

        <form method="POST" action="{{ route('forum.destroy', ['forum' => $forum->id]) }}">
            @csrf
            @method('delete')
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeDeleteForumModal()"
                    class="px-4 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">Откажи</button>
                <button type="submit"
                    class="px-4 py-2 rounded-md bg-red-600 text-white text-sm font-medium hover:bg-red-700">Избриши форум</button>
            </div>
        </form>
    </div>
</div>

{{-- Избриши дискусија + Ban Author Modal --}}
<div class="fixed inset-0 bg-black/40 hidden items-center justify-center" id="deleteThreadModal">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
        <h2 class="text-lg font-semibold text-gray-900">Избриши дискусија</h2>
        <p class="text-sm text-gray-600">
            Ова трајно ќе избрише <span class="font-medium text-gray-900" id="deleteThreadName"></span>. You may also sanction the author in the same action.
        </p>

        <form method="POST" id="deleteThreadForm" action="">
            @csrf
            @method('delete')

            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="ban_author" value="1">
                Исто така банирај го авторот на дискусијата
            </label>

            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeDeleteThreadModal()"
                    class="px-4 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">Откажи</button>
                <button type="submit"
                    class="px-4 py-2 rounded-md bg-red-600 text-white text-sm font-medium hover:bg-red-700">Избриши дискусија</button>
            </div>
        </form>
    </div>
</div>

@push('scripts-forum-show')
<script>
    function openForumModal() {
        const modal = document.getElementById('forumModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeForumModal() {
        const modal = document.getElementById('forumModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openDeleteForumModal() {
        const modal = document.getElementById('deleteForumModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDeleteForumModal() {
        const modal = document.getElementById('deleteForumModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openDeleteThreadModal(actionUrl, threadName) {
        const modal = document.getElementById('deleteThreadModal');
        const form = document.getElementById('deleteThreadForm');
        const nameSpan = document.getElementById('deleteThreadName');

        form.setAttribute('action', actionUrl);
        nameSpan.textContent = threadName;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDeleteThreadModal() {
        const modal = document.getElementById('deleteThreadModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.getElementById('forumModal').addEventListener('click', function (e) {
        if (e.target === this) closeForumModal();
    });

    document.getElementById('deleteForumModal').addEventListener('click', function (e) {
        if (e.target === this) closeDeleteForumModal();
    });

    document.getElementById('deleteThreadModal').addEventListener('click', function (e) {
        if (e.target === this) closeDeleteThreadModal();
    });
</script>
@endpush

@endsection 