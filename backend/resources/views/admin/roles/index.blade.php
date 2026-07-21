@extends('layouts.master')

@section('content')
    {{-- Page header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Staff Management</h1>
            <p class="text-sm text-gray-500">Grant or revoke Admin and Moderator roles</p>
        </div>
        <button type="button" onclick="document.getElementById('grant-role-modal').classList.remove('hidden')"
            class="bg-my-purple text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-my-purple/90">
            + Grant Role
        </button>
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

    @foreach ($users as $user)
        @if ($user->role != 'user')
            <section class="mb-8">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">{{ $user->role }}s</h2>
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
                                                <form action="{{ route('role.update', ['user' => $user->id]) }}"
                                                    method="POST">
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
                                                <form action="{{ route('role.destroy', ['user' => $user->id]) }}"
                                                    method="post">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        class="text-red-600 text-xs font-medium hover:underline">Revoke</button>
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
        @endif
    @endforeach



    </div>

    {{-- Grant Role Modal --}}
    {{-- Grant Role Modal --}}
    <div id="grant-role-modal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Grant Staff Role</h3>
                <button type="button" onclick="document.getElementById('grant-role-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>

            <form action="{{ route('role.grant') }}" method="POST">
                @csrf

                <div class="mb-4 relative">
                    <label class="text-sm font-medium text-gray-700">Search user by email</label>
                    <input type="text" id="grant-search" autocomplete="off" placeholder="Type an email..."
                        class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-my-purple/40 focus:outline-none">

                    <input type="hidden" name="user_id" id="grant-selected-user-id">

                    <div id="grant-search-results"
                        class="absolute left-0 top-full mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg hidden z-50 max-h-40 overflow-y-auto">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="text-sm font-medium text-gray-700">Assign role</label>
                    <select name="role" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Select a role</option>
                        <option value="super_admin">Super admin</option>
                        <option value="admin">Admin</option>
                        <option value="moderator">Moderator</option>
                    </select>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('grant-role-modal').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-my-purple text-white rounded-lg hover:bg-my-purple/90">Grant
                        Role</button>
                </div>
            </form>
        </div>
    </div>


    @push('scripts')
        <script>
            const roleShowTemplate = "{{ route('role.show', ['user' => '__ID__']) }}";
            const liveSearchUrl = "{{ route('role.liveSearch') }}";

            const searchInput = document.getElementById('staff-search');
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

            function renderResults(users) {
                resultsBox.innerHTML = '';

                if (users.length === 0) {
                    resultsBox.innerHTML = `<div class="px-4 py-3 text-sm text-gray-400">No matching users</div>`;
                    resultsBox.classList.remove('hidden');
                    return;
                }

                users.forEach(user => {
                    const row = document.createElement('a');
                    row.href = roleShowTemplate.replace('__ID__', user.id);
                    row.className =
                        'block px-4 py-2 hover:bg-gray-50 cursor-pointer flex justify-between items-center text-sm border-b border-gray-100 last:border-0 no-underline text-inherit';
                    row.innerHTML = `
                <span class="font-medium text-gray-800">${user.username ?? 'No username'}</span>
                <span class="text-gray-400 text-xs">${user.email}</span>
            `;
                    resultsBox.appendChild(row);
                });

                resultsBox.classList.remove('hidden');
            }
        </script>
    @endpush

    @push('scripts1')
        <script>
            // ... your existing staff-search JS stays here ...

            const grantSearchUrl = "{{ route('role.grantSearch') }}";
            const grantSearchInput = document.getElementById('grant-search');
            const grantResultsBox = document.getElementById('grant-search-results');
            const grantSelectedUserId = document.getElementById('grant-selected-user-id');

            grantSearchInput.addEventListener('input', function() {
                const query = this.value.trim();
                grantSelectedUserId.value = ''; // clear selection if user types again

                if (query.length < 2) {
                    grantResultsBox.classList.add('hidden');
                    return;
                }

                fetch(`${grantSearchUrl}?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(users => renderGrantResults(users))
                    .catch(err => console.error(err));
            });

            function renderGrantResults(users) {
                grantResultsBox.innerHTML = '';

                if (users.length === 0) {
                    grantResultsBox.innerHTML = `<div class="px-4 py-3 text-sm text-gray-400">No matching users</div>`;
                    grantResultsBox.classList.remove('hidden');
                    return;
                }

                users.forEach(user => {
                    const row = document.createElement('div');
                    row.className = 'px-3 py-2 hover:bg-gray-50 cursor-pointer flex justify-between text-sm';
                    row.innerHTML = `
                <span class="font-medium text-gray-800">${user.username ?? 'No username'}</span>
                <span class="text-gray-400 text-xs">${user.email}</span>
            `;

                    row.addEventListener('click', () => {
                        grantSearchInput.value = user.email;
                        grantSelectedUserId.value = user.id;
                        grantResultsBox.classList.add('hidden');
                    });

                    grantResultsBox.appendChild(row);
                });

                grantResultsBox.classList.remove('hidden');
            }
        </script>
    @endpush
@endsection
