@extends('layouts.master')

@section('title', 'Forum Details')

@section('content')
<div class="max-w-7xl mx-auto w-full px-4 py-6 space-y-6">

    {{-- Back link --}}
    <a href="{{ route('forum.index') }}" class="text-sm text-indigo-600 hover:underline mb-6">
        &larr; Back to Forums
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
        <div class="h-32 w-full bg-gray-200"></div>

        <div class="p-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-16 h-16 rounded-xl bg-gray-300 -mt-14 border-4 border-white shrink-0"></div>

                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-semibold text-gray-900">{{ $forum->name }}</h1>
                        <span class="text-xs bg-indigo-100 text-indigo-700 rounded-full px-2 py-0.5">{{ $forum->type }}</span>
                    </div>
                    @if($forum->school && $forum->school->city)
                        <p class="text-sm text-gray-500 mt-1">City: {{ $forum->school->city->name }}</p>
                    @endif
                    <p class="text-sm text-gray-500 mt-1">Slug: {{ $forum->slug }}</p>
                    <p class="text-gray-600 text-sm mt-2 max-w-xl">
                        {{ $forum->description }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="openForumModal()"
                    class="px-4 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                    Edit Forum
                </button>
                <button type="button" onclick="openDeleteForumModal()"
                    class="px-4 py-2 rounded-md bg-red-600 text-white text-sm font-medium hover:bg-red-700">
                    Delete Forum
                </button>
            </div>
        </div>
    </div>

    {{-- Stats row: threads, comments, followers --}}
    <div class="flex justify-between items-center w-full gap-4">
        <div class="bg-white rounded-xl shadow p-5">
            <h6 class="text-sm text-gray-500 mb-1">Total Threads</h6>
            <p class="text-2xl font-semibold text-gray-900">{{ $forum->threads_count }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <h6 class="text-sm text-gray-500 mb-1">Followers</h6>
            <p class="text-2xl font-semibold text-gray-900">{{ $forum->members_count }}</p>
        </div>
    </div>

    {{-- Threads in this forum --}}
    <div class="bg-white rounded-xl shadow">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Threads in this Forum</h2>
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
                            <span class="text-xs bg-gray-100 text-gray-500 rounded-full px-2 py-0.5">Anonymous</span>
                            <span class="text-xs bg-orange-100 text-orange-600 rounded-full px-2 py-0.5">Under review</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">by {{ $thread->user->username }} · 2 hours ago</p>
                    </div>
                </div>

                <div class="flex items-center gap-6 text-sm text-gray-500">
                    <span>▲ {{ $thread->upvotes }} upvotes</span>
                    <span>💬 {{ $thread->comments->count() }} comments</span>
                    <button type="button"
                    {{-- {{ route('thread.destroy', ['thread' => $thread->id]) }} --}}
                        onclick="openDeleteThreadModal('', '{{ $thread->name }}')"
                        class="text-red-600 hover:underline text-xs">
                        Delete
                    </button>
                </div>
            </div>
            @empty
            {{-- End single thread row --}}

            {{-- Empty state --}}
            <div class="px-5 py-10 text-center text-sm text-gray-400">
                No threads have been posted in this forum yet.
            </div>

            @endforelse

        </div>

        {{-- Pagination for threads --}}
        <div class="flex justify-center p-3">
            <nav class="flex gap-1 text-sm">
                @if ($threads->onFirstPage())
                    <button disabled class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-400 cursor-not-allowed">
                        Previous
                    </button>
                @else
                    <a href="{{ $threads->previousPageUrl() }}"
                        class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">
                        Previous
                    </a>
                @endif

                @if ($threads->hasMorePages())
                    <a href="{{ $threads->nextPageUrl() }}"
                        class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">
                        Next
                    </a>
                @else
                    <button disabled class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-400 cursor-not-allowed">
                        Next
                    </button>
                @endif
            </nav>
        </div>
    </div>

</div>

{{-- Edit Forum Modal --}}
<div class="fixed inset-0 bg-black/40 hidden items-center justify-center" id="forumModal">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Edit Forum</h2>
            <button type="button" onclick="closeForumModal()" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <form class="space-y-3" method="POST" action="{{ route('forum.update', ['forum' => $forum->id]) }}" enctype="multipart/form-data">
            @csrf
            @method('patch')
            <div>
                <label class="text-sm text-gray-600">Name</label>
                <input type="text" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" name="name" value="{{ $forum->name }}">
            </div>

            <div>
                <label class="text-sm text-gray-600">Slug</label>
                <input type="text" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" name="slug" value="{{ $forum->slug }}">
                <p class="text-xs text-gray-400 mt-1">Renaming re-links all threads, comments, saves, and follows
                    automatically.</p>
            </div>

            <div>
                <label class="text-sm text-gray-600">Description</label>
                <textarea rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" name="description">{{ $forum->description }}</textarea>
            </div>

            <div>
                <label class="text-sm text-gray-600">Forum Type</label>
                <select class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" name="type">
                    <option value="general" {{ $forum->type == 'general' ? 'selected' : '' }}>Topic Forum</option>
                    <option value="school" {{ $forum->type == 'school' ? 'selected' : '' }}>School Forum</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-sm text-gray-600">Image</label>
                    <input type="file" class="w-full text-sm border border-gray-300 p-2 rounded-md" name="imageUrl">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Banner</label>
                    <input type="file" class="w-full text-sm border border-gray-300 p-2 rounded-md" name="bannerUrl">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeForumModal()"
                    class="px-4 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit"
                    class="px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">Save
                    Forum</button>
            </div>
        </form>
    </div>
</div>

{{-- Delete Forum Modal --}}
<div class="fixed inset-0 bg-black/40 hidden items-center justify-center" id="deleteForumModal">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
        <h2 class="text-lg font-semibold text-gray-900">Delete Forum</h2>
        <p class="text-sm text-gray-600">
            This will permanently delete <span class="font-medium text-gray-900">{{ $forum->name }}</span> and unlink all its threads, comments, saves, and follows. This action cannot be undone.
        </p>

        <form method="POST" action="{{ route('forum.destroy', ['forum' => $forum->id]) }}">
            @csrf
            @method('delete')
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeDeleteForumModal()"
                    class="px-4 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit"
                    class="px-4 py-2 rounded-md bg-red-600 text-white text-sm font-medium hover:bg-red-700">Delete Forum</button>
            </div>
        </form>
    </div>
</div>

{{-- Delete Thread + Ban Author Modal --}}
<div class="fixed inset-0 bg-black/40 hidden items-center justify-center" id="deleteThreadModal">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
        <h2 class="text-lg font-semibold text-gray-900">Delete Thread</h2>
        <p class="text-sm text-gray-600">
            This will permanently delete <span class="font-medium text-gray-900" id="deleteThreadName"></span>. You may also sanction the author in the same action.
        </p>

        <form method="POST" id="deleteThreadForm" action="">
            @csrf
            @method('delete')

            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="ban_author" value="1">
                Also ban the author of this thread
            </label>

            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeDeleteThreadModal()"
                    class="px-4 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit"
                    class="px-4 py-2 rounded-md bg-red-600 text-white text-sm font-medium hover:bg-red-700">Delete Thread</button>
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