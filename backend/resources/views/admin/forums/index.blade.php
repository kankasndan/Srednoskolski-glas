@extends('layouts.master')

@section('title', 'Forums')

@section('content')
    <div class="max-w-7xl mx-auto w-full px-4 py-6 space-y-6">

        {{-- Header + Create button --}}
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-900">Forums</h1>
            <button type="button" onclick="openForumModal()"
                class="px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
                + Create Forum
            </button>
        </div>

        {{-- Filters: type (topic / school), city, search --}}
        <form action="{{ route('forum.index') }}" method="GET" class="flex flex-wrap items-center gap-3 mb-6 w-full">

            <div class="flex-1 min-w-[200px] relative">
                <input type="text" id="forum-search" name="search" value="{{ request('search') }}"
                    placeholder="Search forum"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-my-purple/40 focus:outline-none">

                <div id="search-results"
                    class="absolute left-0 top-full mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg hidden z-50">
                </div>
            </div>

            <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All types</option>
                <option value="general" {{ request('type') == 'general' ? 'selected' : '' }}>General</option>
                <option value="school" {{ request('type') == 'school' ? 'selected' : '' }}>School</option>
            </select>

            <select name="city" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All cities</option>
                @foreach ($cities as $city)
                    <option value="{{ $city->id }}" {{ request('city') == $city->id ? 'selected' : '' }}>
                        {{ $city->name }}</option>
                @endforeach
            </select>

            <button type="submit"
                class="bg-my-purple text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-my-purple/90">
                Filter
            </button>

            @if (request()->anyFilled(['search', 'type', 'city']))
                <a href="{{ route('forum.index') }}" class="text-sm text-gray-500 hover:underline">Clear filters</a>
            @endif
        </form>

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

        {{-- Forums table --}}
        <div class="bg-white rounded-xl shadow overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b bg-gray-50">
                        <th class="px-4 py-3">Forum</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Slug</th>
                        <th class="px-4 py-3 text-right">Threads</th>
                        <th class="px-4 py-3 text-right">Followers</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">

                    {{-- Single forum row (repeat per forum) --}}
                    @foreach ($forums as $forum)
                        <tr class="hover:cursor-pointer" onclick="window.location='{{ route('forum.show', $forum->id) }}'">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    {{-- Forum icon --}}
                                    <div class="w-8 h-8 rounded-lg bg-gray-200"></div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $forum->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $forum->description }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="text-xs bg-indigo-100 text-indigo-700 rounded-full px-2 py-0.5">{{ $forum->type }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $forum->slug }}</td>
                            <td class="px-4 py-3 text-right">{{ $forum->threads_count }}</td>
                            <td class="px-4 py-3 text-right">{{ $forum->members_count }}</td>
                            <td class="px-4 py-3 text-right space-x-2 flex justify-between items-center">
                                <a class="inline text-indigo-600 hover:underline text-xs">Edit</a>
                                <a class="inline text-red-600 hover:underline text-xs">Delete</a>
                            </td>
                        </tr>
                    @endforeach
                    {{-- End single forum row --}}

                </tbody>
            </table>
        </div>

    </div>

    {{-- Pagination --}}
    <div class="flex justify-center">
        <nav class="flex gap-1 text-sm">
            @if ($forums->onFirstPage())
                <button disabled class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-400 cursor-not-allowed">
                    Previous
                </button>
            @else
                <a href="{{ $forums->previousPageUrl() }}"
                    class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">
                    Previous
                </a>
            @endif

            @if ($forums->hasMorePages())
                <a href="{{ $forums->nextPageUrl() }}"
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

    {{-- Create --}}
    <div class="fixed inset-0 bg-black/40 hidden items-center justify-center" id="forumModal">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Create</h2>
                <button type="button" onclick="closeForumModal()" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>

            <form class="space-y-3" method="POST" action="{{ route('forum.store') }}">
                <div>
                    <label class="text-sm text-gray-600">Name</label>
                    <input type="text" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" name="name">
                </div>

                <div>
                    <label class="text-sm text-gray-600">Slug</label>
                    <input type="text" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" name="slug">
                    <p class="text-xs text-gray-400 mt-1">Renaming re-links all threads, comments, saves, and follows
                        automatically.</p>
                </div>

                <div>
                    <label class="text-sm text-gray-600">Description</label>
                    <textarea rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" name="description"></textarea>
                </div>

                <div>
                    <label class="text-sm text-gray-600">Forum Type</label>
                    <select class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" name="type">
                        <option value="general">Topic Forum</option>
                        <option value="school">School Forum</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm text-gray-600">Image</label>
                        <input type="file" class="w-full text-sm border border-gray-300 p-2 rounded-md" name="imageUrl">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Banner</label>
                        <input type="file" class="w-full text-sm border border-gray-300 p-2 rounded-md"
                            name="bannerUrl">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeForumModal()"
                        class="px-4 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button
                        class="px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">Save
                        Forum</button>
                </div>
            </form>

        </div>
    </div>


    {{-- Delete Thread + Ban Author Modal --}}
    <div class="fixed inset-0 bg-black/40 hidden items-center justify-center" id="deleteThreadModal">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Delete Thread</h2>
            <p class="text-sm text-gray-600">This will permanently delete the thread. You may also sanction the author in
                the same action.</p>

            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox">
                Also ban the author of this thread
            </label>

            <div class="flex justify-end gap-2 pt-2">
                <button
                    class="px-4 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
                <button class="px-4 py-2 rounded-md bg-red-600 text-white text-sm font-medium hover:bg-red-700">Delete
                    Thread</button>
            </div>
        </div>
    </div>
    @push('scripts-forums')
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

            document.getElementById('forumModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeForumModal();
                }
            });

            const roleShowTemplate = "{{ route('forum.show', ['forum' => '__ID__']) }}";
            const liveSearchUrl = "{{ route('forum.liveSearch') }}";

            const searchInput = document.getElementById('forum-search');
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

            function renderResults(forums) {
                resultsBox.innerHTML = '';

                if (forums.length === 0) {
                    resultsBox.innerHTML = `<div class="px-4 py-3 text-sm text-gray-400">No matching forums</div>`;
                    resultsBox.classList.remove('hidden');
                    return;
                }

                forums.forEach(forum => {
                    const row = document.createElement('a');
                    row.href = roleShowTemplate.replace('__ID__', forum.id);
                    row.className =
                        'block px-4 py-2 hover:bg-gray-50 cursor-pointer flex justify-between items-center text-sm border-b border-gray-100 last:border-0 no-underline text-inherit';
                    row.innerHTML = `
                    <span class="text-gray-400 text-xs">${forum.imageUrl ? forum.imageUrl : "No image"}</span>
                <span class="font-medium text-gray-800">${forum.name ?? 'No username'}</span>
            `;
                    resultsBox.appendChild(row);
                });

                resultsBox.classList.remove('hidden');
            }
        </script>
    @endpush


@endsection
