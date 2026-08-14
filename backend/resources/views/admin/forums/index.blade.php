@extends('layouts.master')

@section('title', 'Форуми')

@section('content')
    <div class="max-w-7xl mx-auto w-full px-4 py-6 space-y-6">

        {{-- Header + Create button --}}
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-900">Форуми</h1>
            @can('create forums')
                <button type="button" onclick="openForumModal()"
                    class="px-4 py-2 rounded-md bg-my-purple text-white text-sm font-medium hover:bg-my-purple/90">
                    + Креирај форум
                </button>
            @endcan
        </div>

        {{-- Filters: type (topic / school), city, search --}}
        <form action="{{ route('forum.index') }}" method="GET" class="flex flex-wrap items-center gap-3 mb-6 w-full">

            <div class="flex-1 min-w-[200px] relative">
                <input type="text" id="forum-search" name="search" value="{{ request('search') }}"
                    placeholder="Пребарај форум"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-my-purple/40 focus:outline-none">

                <div id="search-results"
                    class="absolute left-0 top-full mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg hidden z-50">
                </div>
            </div>

            <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Сите типови</option>
                <option value="general" {{ request('type') == 'general' ? 'selected' : '' }}>Општ</option>
                <option value="school" {{ request('type') == 'school' ? 'selected' : '' }}>Училиште</option>
            </select>

            <select name="city" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Сите градови</option>
                @foreach ($cities as $city)
                    <option value="{{ $city->id }}" {{ request('city') == $city->id ? 'selected' : '' }}>
                        {{ $city->name }}</option>
                @endforeach
            </select>

            <button type="submit"
                class="bg-my-purple text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-my-purple/90">
                Филтрирај
            </button>

            @if (request()->anyFilled(['search', 'type', 'city']))
                <a href="{{ route('forum.index') }}" class="text-sm text-gray-500 hover:underline">Исчисти филтри</a>
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
                        <th class="px-4 py-3">Форум</th>
                        <th class="px-4 py-3">Тип</th>
                        <th class="px-4 py-3">Слаг</th>
                        <th class="px-4 py-3 text-right">Дискусии</th>
                        <th class="px-4 py-3 text-right">Следбеници</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">

                    {{-- Single forum row (repeat per forum) --}}
                    @foreach ($forums as $forum)
                        <tr class="hover:cursor-pointer hover:bg-gray-100"
                            onclick="window.location='{{ route('forum.show', $forum->id) }}'">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    {{-- Forum icon --}}
                                    @if ($forum->imageUrl)
                                        <img src="{{ $forum->imageUrl }}" alt=""
                                            class="w-8 h-8 rounded-lg object-cover bg-gray-100">
                                    @else
                                        <div class="w-8 h-8 rounded-lg bg-gray-200"></div>
                                    @endif
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $forum->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $forum->description }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="text-xs bg-my-purple/10 text-my-purple rounded-full px-2 py-0.5">{{ match ($forum->type) {
                                        'general' => 'Општ',
                                        'school' => 'Училишен',
                                        default => $forum->type,
                                    } }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $forum->slug }}</td>
                            <td class="px-4 py-3 text-right">{{ $forum->threads_count }}</td>
                            <td class="px-4 py-3 text-right">{{ $forum->members_count }}</td>

                        </tr>
                    @endforeach
                    {{-- End single forum row --}}

                </tbody>
            </table>
        </div>

    </div>

    {{-- Pagination --}}
    <div class="flex justify-center mb-10">
        <nav class="flex gap-1 text-sm">
            @if ($forums->onFirstPage())
                <button disabled class="px-3 py-1.5 rounded-md border border-gray-200 text-gray-400 cursor-not-allowed">
                    Претходна
                </button>
            @else
                <a href="{{ $forums->previousPageUrl() }}"
                    class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">
                    Претходна
                </a>
            @endif

            @if ($forums->hasMorePages())
                <a href="{{ $forums->nextPageUrl() }}"
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

    {{-- Create --}}
    <div class="fixed inset-0 bg-black/40 hidden items-center justify-center" id="forumModal">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Креирај</h2>
                <button type="button" onclick="closeForumModal()" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>

            <form class="space-y-3" method="POST" action="{{ route('forum.store') }}" enctype="multipart/form-data">
                @csrf

                <div>
                    <label class="text-sm text-gray-600">Име</label>
                    <input type="text" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" name="name"
                        id="schoolName" required>
                </div>

                <div>
                    <label class="text-sm text-gray-600">Слаг</label>
                    <input type="text" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" name="slug"
                        placeholder="Опционално — се генерира од името ако е празно" id="shcoolSlug">
                    <p class="text-xs text-gray-400 mt-1">Остави празно за автоматско генерирање from the name. Must match frontend
                        slug rules.</p>
                </div>

                <div>
                    <label class="text-sm text-gray-600">Опис</label>
                    <textarea rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" name="description"
                        required></textarea>
                </div>



                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm text-gray-600">Икона</label>
                        <input type="file" accept="image/*"
                            class="w-full text-sm border border-gray-300 p-2 rounded-md" name="icon">
                        <p class="text-xs text-gray-400 mt-1">Опционално. Се прикачува на ImageKit. Otherwise uses
                            /icons/&#123;slug&#125;.svg</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Банер</label>
                        <input type="file" accept="image/*"
                            class="w-full text-sm border border-gray-300 p-2 rounded-md" name="banner">
                        <p class="text-xs text-gray-400 mt-1">Опционално. Се прикачува на ImageKit. Otherwise uses
                            /banners/&#123;slug&#125;.svg</p>
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

    @push('scripts-forums')
        <script>
            const forumTypeSelect = document.getElementById("forumTypeSelect");
            const schoolModal = document.getElementById("schoolModal");

            const schoolSelect = document.getElementById("schoolModalSelect");

            const schoolName = document.getElementById("schoolName");
            const schoolSlug = document.getElementById("schoolSlug");



            if (forumTypeSelect && schoolModal) {
                forumTypeSelect.addEventListener("change", () => {
                    if (forumTypeSelect.value === "school") {
                        schoolModal.classList.remove("hidden");
                    } else {
                        schoolModal.classList.add("hidden");
                        schoolName.value = "";
                        schoolName.readOnly = false;
                    }
                });
            }

            if (schoolSelect) {
                schoolSelect.addEventListener("change", () => {
                    const selectedOption = schoolSelect.options[schoolSelect.selectedIndex];
                    schoolName.value = selectedOption.text;
                    schoolName.readOnly = true;
                })
            }


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
                    resultsBox.innerHTML = `<div class="px-4 py-3 text-sm text-gray-400">Нема совпаѓачки форуми</div>`;
                    resultsBox.classList.remove('hidden');
                    return;
                }

                forums.forEach(forum => {
                    const row = document.createElement('a');
                    row.href = roleShowTemplate.replace('__ID__', forum.id);
                    row.className =
                        'block px-4 py-2 hover:bg-gray-50 cursor-pointer flex justify-between items-center text-sm border-b border-gray-100 last:border-0 no-underline text-inherit';
                    row.innerHTML = `
                    <span class="text-gray-400 text-xs">${forum.imageUrl ? forum.imageUrl : "Нема слика"}</span>
                <span class="font-medium text-gray-800">${forum.name ?? 'Нема корисничко име'}</span>
            `;
                    resultsBox.appendChild(row);
                });

                resultsBox.classList.remove('hidden');
            }
        </script>
    @endpush


@endsection
