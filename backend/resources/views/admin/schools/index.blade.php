@extends('layouts.master')

@section('title', 'Училишта')

@section('content')
    <x-admin.page-header title="Училишта" subtitle="Прегледај училишта и нивните градови.">
        @can('create schools')
            <button type="button" onclick="openSchoolModal()"
                class="rounded-lg bg-my-purple px-4 py-2 text-sm font-medium text-white hover:bg-my-purple/90">
                + Креирај училиште
            </button>
        @endcan
    </x-admin.page-header>

        {{-- Filters: type (topic / school), city, search --}}
        <form action="{{ route('school.index') }}" method="GET" class="flex flex-wrap items-center gap-3 mb-6 w-full"
            id="schoolFilterForm">

            <div class="flex-1 min-w-[200px] relative">
                <input type="text" id="school-search" name="search" value="{{ request('search') }}"
                    placeholder="Пребарај училиште"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-my-purple/40 focus:outline-none">

                <div id="search-results"
                    class="absolute left-0 top-full mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg hidden z-50">
                </div>
            </div>

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

            @if (request()->anyFilled(['search', 'city']))
                <a href="{{ route('school.index') }}" class="text-sm text-gray-500 hover:underline">Исчисти филтри</a>
            @endif
        </form>

        <x-admin.flash />

        {{-- Shcools table --}}
        <div class="bg-white rounded-xl shadow overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b bg-gray-50">
                        <th class="px-4 py-3">Училиште</th>
                        <th class="px-4 py-3">Град</th>
                        <th class="px-4 py-3">Ученици</th>
                        <th class="px-4 py-3">Акции</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">

                    @forelse ($schools as $school)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $school->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="text-xs bg-my-purple/10 text-my-purple rounded-full px-2 py-0.5">{{ $school->city->name }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $school->studentData->count() }}</td>
                            <td class="px-4 py-3">
                                @can('delete schools')
                                    {{-- Values travel in data-* attributes: Blade's escaping is not
                                         enough inside an inline handler, where the HTML parser decodes
                                         entities before the JS string is parsed. --}}
                                    <button type="button"
                                        class="js-delete-school text-red-500 text-sm rounded-lg hover:text-red-600 cursor-pointer"
                                        data-school-id="{{ $school->id }}"
                                        data-school-name="{{ $school->name }}">
                                        Избриши
                                    </button>
                                @endcan
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">
                                Нема совпаѓачки училишта
                            </td>
                        </tr>
                    @endforelse
                    {{-- End single school row --}}

                </tbody>
            </table>
        </div>

        <x-admin.pagination :paginator="$schools" />

    {{-- Create --}}
    <div class="fixed inset-0 bg-black/40 hidden items-center justify-center" id="schoolModal">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Креирај училиште</h2>
                <button type="button" onclick="closeSchoolModal()" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>

            <form class="space-y-3" method="POST" action="{{ route('school.store') }}" enctype="multipart/form-data">
                @csrf


                <div>
                    <label class="text-sm text-gray-600">Име</label>
                    <input type="text" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" name="name"
                        id="schoolName" required>
                </div>

                <div id="schoolCityField">
                    <label class="text-sm text-gray-600">Град</label>
                    <select class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" name="city"
                        id="schoolModalSelect">
                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeSchoolModal()"
                        class="px-4 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">Откажи</button>
                    <button type="submit"
                        class="px-4 py-2 rounded-md bg-my-purple text-white text-sm font-medium hover:bg-my-purple/90">Зачувај училиште</button>
                </div>
            </form>

        </div>
    </div>

    {{-- Delete School Modal (global, reused for any school) --}}
    <div class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50" id="deleteSchoolModal">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Избриши училиште</h2>
                <button type="button" onclick="closeDeleteSchoolModal()"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>

            <p class="text-sm text-gray-700">
                Дали си сигурен дека сакаш да го избришеш
                <span class="font-semibold" id="deleteSchoolName"></span>?
            </p>

            <form id="deleteSchoolForm" method="POST">
                @csrf
                @method('DELETE')

                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" onclick="closeDeleteSchoolModal()"
                        class="px-4 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                        Откажи
                    </button>
                    <button type="submit"
                        class="px-4 py-2 rounded-md bg-red-600 text-white text-sm font-medium hover:bg-red-700">
                        Избриши училиште
                    </button>
                </div>
            </form>
        </div>
    </div>


    @push('scripts-school')
        <script>
            function openSchoolModal() {
                const modal = document.getElementById('schoolModal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeSchoolModal() {
                const modal = document.getElementById('schoolModal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            document.getElementById('schoolModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeSchoolModal();
                }
            });

            // Delete School modal
            function openDeleteSchoolModal(schoolId, schoolName) {
                const modal = document.getElementById('deleteSchoolModal');
                const nameSpan = document.getElementById('deleteSchoolName');
                const form = document.getElementById('deleteSchoolForm');

                if (modal && nameSpan && form) {
                    nameSpan.textContent = schoolName;

                    // Set the form action to the correct route for this school
                    form.action = "{{ route('school.delete', '__ID__') }}".replace('__ID__', schoolId);

                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }
            }

            document.addEventListener('click', function(e) {
                const trigger = e.target.closest('.js-delete-school');
                if (!trigger) {
                    return;
                }

                openDeleteSchoolModal(trigger.dataset.schoolId, trigger.dataset.schoolName ?? '');
            });

            function closeDeleteSchoolModal() {
                const modal = document.getElementById('deleteSchoolModal');
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            }

            document.getElementById('deleteSchoolModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeDeleteSchoolModal();
                }
            });

            const roleShowTemplate = "{{ route('school.index', ['school' => '__ID__']) }}";
            const liveSearchUrl = "{{ route('school.liveSearch') }}";

            const searchInput = document.getElementById('school-search');
            const resultsBox = document.getElementById('search-results');
            const filterForm = document.getElementById('schoolFilterForm');


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

            function renderResults(schools) {
                resultsBox.innerHTML = '';

                if (schools.length === 0) {
                    resultsBox.innerHTML = `<div class="px-4 py-3 text-sm text-gray-400">Нема совпаѓачки училишта</div>`;
                    resultsBox.classList.remove('hidden');
                    return;
                }

                schools.forEach(school => {
                    resultsBox.appendChild(adminSearchRow({
                        primary: school.name,
                        onClick: (e) => {
                            e.preventDefault();
                            if (searchInput) {
                                searchInput.value = school.name ?? '';
                            }
                            resultsBox.classList.add('hidden');
                            if (filterForm) {
                                filterForm.submit();
                            }
                        },
                    }));
                });

                resultsBox.classList.remove('hidden');
            }
        </script>
    @endpush


@endsection
