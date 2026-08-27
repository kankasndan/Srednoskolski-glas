<!DOCTYPE html>
<html lang="mk" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Админ Панел') — Средношколски Глас</title>
    <script src="https://kit.fontawesome.com/75475ebc14.js" crossorigin="anonymous"></script>
    <link rel="icon" type="image/svg" href="{{ asset('images/logo.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full overflow-hidden bg-[#f4f5f9] p-0 sm:p-4 lg:p-8">

    <header
        class="grid h-[72px] w-full grid-cols-[auto_1fr_auto] items-center gap-4 border-b border-[#E6E8F0] bg-[#f4f5f9] px-4 sm:gap-6 sm:px-8">

        <div class="flex items-center gap-3">
            <button type="button" id="sidebarToggle"
                class="flex h-9 w-9 items-center justify-center rounded-[10px] border border-[#E6E8F0] text-[#595959] hover:bg-my-purple/5 lg:hidden"
                aria-label="Отвори мени" aria-controls="sidebar" aria-expanded="false">
                <i class="fa-solid fa-bars"></i>
            </button>
            <img src="{{ asset('images/logo.svg') }}" alt="" class="w-12 sm:w-16" />
            <span class="whitespace-nowrap text-[16px] font-bold tracking-wide text-[#1F2333]">Админ Панел</span>
        </div>

        <div class="flex items-center gap-4 justify-self-end">

            @php
                $unreadCount = auth()->user()?->unreadNotifications()->count() ?? 0;
            @endphp

            <div class="relative">
                <button type="button" id="bellBtn"
                    class="relative flex h-9 w-9 items-center justify-center rounded-full border border-[#E6E8F0] text-[#595959] hover:bg-my-purple/5"
                    aria-label="Нотификации">
                    <i class="fa-regular fa-bell"></i>

                    @if ($unreadCount > 0)
                        <span
                            class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-my-pink text-[10px] font-bold text-white">
                            {{ $unreadCount }}
                        </span>
                    @endif
                </button>

                <div id="notifMenu"
                    class="absolute right-0 top-[52px] z-50 hidden w-[min(320px,calc(100vw-2rem))] rounded-[10px] border border-[#E6E8F0] bg-white p-2 shadow-lg">

                    <div class="mb-2 flex items-center justify-between px-2">
                        <span class="text-[13px] font-bold text-[#1F2333]">Нотификации</span>

                        @if ($unreadCount > 0)
                            <form method="POST" action="{{ route('admin.notifications.readAll') }}">
                                @csrf
                                <button type="submit" class="text-[11px] text-my-purple hover:underline">
                                    Обележи ги сите како прочитани
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="max-h-80 overflow-y-auto">
                        @php
                            $notifications = auth()->user()?->notifications()->latest('updated_at')->take(30)->get();
                        @endphp

                        @forelse($notifications as $notification)
                            @php
                                $data = $notification->data;
                                $isUnread = is_null($notification->read_at);
                            @endphp

                            <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                                @csrf
                                <button type="submit"
                                    class="block w-full cursor-pointer rounded-[8px] px-3 py-2 text-left text-[13px] hover:bg-my-purple/5 {{ $isUnread ? 'bg-my-purple/10' : '' }}">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-bold text-[#1F2333]">
                                            {{ $data['title'] ?? 'Нотификација' }}
                                        </span>
                                        @if ($isUnread)
                                            <span class="ml-2 h-2 w-2 shrink-0 rounded-full bg-my-purple"></span>
                                        @endif
                                    </div>
                                    <div class="text-[12px] text-[#595959]">
                                        {{ $data['message'] ?? '' }}
                                    </div>
                                    @if (isset($data['report_id']) && ($data['count'] ?? 1) > 1)
                                        <div class="mt-1 text-[11px] font-semibold text-my-purple">
                                            {{ $data['count'] }} пријави
                                        </div>
                                    @endif
                                    <div class="mt-1 text-[11px] text-[#9598A6]">
                                        {{ $notification->created_at?->diffForHumans() }}
                                    </div>
                                </button>
                            </form>
                        @empty
                            <div class="px-3 py-2 text-[13px] text-[#9598A6]">
                                Нема нотификации.
                            </div>
                        @endforelse
                    </div>

                    <a href="{{ route('admin.notifications.index') }}"
                        class="mt-1 block rounded-[8px] px-3 py-2 text-center text-[12px] font-medium text-my-purple hover:bg-my-purple/5">
                        Види ги сите
                    </a>
                </div>
            </div>

            <div class="h-8 w-px bg-[#E6E8F0]"></div>

            <div class="relative">
                <button type="button" id="userMenuBtn" class="flex items-center gap-2 rounded-[10px] px-2 py-1 hover:bg-my-purple/5">
                    <x-admin.avatar :user="$currentAdmin" size="sm" />
                    <div class="hidden text-left sm:block">
                        <div class="whitespace-nowrap text-[13px] font-bold text-[#1F2333]">
                            {{ $currentAdmin->username }}</div>
                        <div class="whitespace-nowrap text-[11px] text-[#9598A6]">
                            {{ match ($currentAdminRole) {
                                'super_admin' => 'Супер админ',
                                'admin' => 'Админ',
                                'moderator' => 'Модератор',
                                default => $currentAdminRole,
                            } }}
                        </div>
                    </div>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" class="text-[#9598A6]">
                        <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>

                <div id="userMenu"
                    class="absolute right-0 top-[52px] z-50 hidden w-[200px] rounded-[10px] border border-[#E6E8F0] bg-white p-2 shadow-lg">
                    <a href="{{ route('admin.profile', ['user' => $currentAdmin->id]) }}"
                        class="block rounded-[8px] px-3 py-2 text-[14px] text-[#595959] hover:bg-my-purple/5">Мој
                        профил</a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit"
                            class="block w-full rounded-[8px] px-3 py-2 text-left text-[14px] font-semibold text-[#DC2626] hover:bg-[#FEE2E2]">Одјави
                            се</button>
                    </form>
                </div>
            </div>

        </div>
    </header>

    <main class="relative flex h-[calc(100vh-72px)] overflow-hidden sm:h-[calc(100vh-72px-2rem)] lg:h-[calc(100vh-72px-4rem)]">

        <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-black/40 lg:hidden"></div>

        <nav id="sidebar"
            class="fixed inset-y-0 left-0 z-40 flex h-full w-[260px] shrink-0 -translate-x-full flex-col gap-0 space-y-3 overflow-y-auto border-r border-[#CCCCCC] bg-white px-4 pt-4 transition-transform lg:static lg:z-0 lg:translate-x-0 lg:bg-transparent">

            @can('view dashboard')
                <div class="px-1 pt-4 pb-1 text-[12px] font-bold uppercase tracking-wide text-[#9598A6]">
                    Почетна табла
                </div>

                <a href="{{ route('admin.dashboard') }}" data-nav-key="nav:dashboard">
                    Контролна табла
                </a>
            @endcan

            @if (auth()->user()?->can('view reports') || auth()->user()?->can('view sanctions') || auth()->user()?->can('view appeals') || auth()->user()?->can('view feedback'))
                <div class="px-1 pt-4 pb-1 text-[12px] font-bold uppercase tracking-wide text-[#9598A6]">
                    Модерација
                </div>
            @endif

            @can('view reports')
                <a href="{{ route('report.index') }}" data-nav-key="nav:reports" @if ($pendingReportsCount > 0) data-badge="{{ $pendingReportsCount }}" @endif>
                    Пријави
                </a>
            @endcan

            @can('view sanctions')
                <a href="{{ route('sanction.index') }}" data-nav-key="nav:sanctions">
                    Санкции
                </a>
            @endcan

            @can('view appeals')
                <a href="{{ route('appeal.index') }}" data-nav-key="nav:appeals" @if ($pendingAppealsCount > 0) data-badge="{{ $pendingAppealsCount }}" @endif>
                    Жалби
                </a>
            @endcan

            @can('view feedback')
                <a href="{{ route('feedback.index') }}" data-nav-key="nav:feedback" @if ($unreviewedFeedbackCount > 0) data-badge="{{ $unreviewedFeedbackCount }}" @endif>
                    Мислења
                </a>
            @endcan

            @if (auth()->user()?->can('view users') || auth()->user()?->can('view forums') || auth()->user()?->can('view schools'))
                <div class="px-1 pt-4 pb-1 text-[12px] font-bold uppercase tracking-wide text-[#9598A6]">
                    Заедница
                </div>
            @endif

            @can('view users')
                <a href="{{ route('user.index') }}" data-nav-key="nav:users">
                    Корисници
                </a>
            @endcan

            @can('view forums')
                <a href="{{ route('forum.index') }}" data-nav-key="nav:forums">
                    Форуми
                </a>
            @endcan

            @can('view schools')
                <a href="{{ route('school.index') }}" data-nav-key="nav:schools">
                    Училишта
                </a>
            @endcan

            @can('view roles page')
                <div class="px-1 pt-4 pb-1 text-[12px] font-bold uppercase tracking-wide text-[#9598A6]">
                    Систем
                </div>

                <a href="{{ route('role.index') }}" data-nav-key="nav:roles">
                    Персонал
                </a>
            @endcan

        </nav>

        <div class="h-full w-full flex-1 overflow-y-auto">
            <div class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6">
                @yield('content')
            </div>
        </div>

    </main>

    <dialog id="adminConfirmDialog"
        class="w-full max-w-md rounded-xl border border-gray-200 bg-white p-0 shadow-xl backdrop:bg-black/40">
        <form method="dialog" class="space-y-4 p-6">
            <p id="adminConfirmMessage" class="text-sm text-gray-800">Дали си сигурен?</p>
            <div class="flex justify-end gap-2">
                <button value="cancel" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100">
                    Откажи
                </button>
                <button value="confirm"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                    Потврди
                </button>
            </div>
        </form>
    </dialog>

    <script>
        function adminText(value, fallback = '') {
            const text = value == null || value === '' ? fallback : String(value);
            const node = document.createTextNode(text);
            const wrap = document.createElement('span');
            wrap.appendChild(node);
            return wrap.textContent;
        }

        function adminSearchRow({ href, primary, secondary, onClick }) {
            const row = href ? document.createElement('a') : document.createElement('div');
            if (href) {
                row.href = href;
            }
            row.className = href
                ? 'block px-4 py-2 hover:bg-gray-50 cursor-pointer flex justify-between items-center text-sm border-b border-gray-100 last:border-0 no-underline text-inherit'
                : 'block px-4 py-2 hover:bg-gray-50 cursor-pointer flex justify-between items-center text-sm border-b border-gray-100 last:border-0';

            const left = document.createElement('span');
            left.className = 'font-medium text-gray-800';
            left.textContent = primary || 'Нема корисничко име';
            row.appendChild(left);

            if (secondary != null && secondary !== '') {
                const right = document.createElement('span');
                right.className = 'text-gray-400 text-xs';
                right.textContent = secondary;
                row.appendChild(right);
            }

            if (onClick) {
                row.addEventListener('click', onClick);
            }

            return row;
        }
    </script>

    @stack('scripts')
    @stack('scripts1')
    @stack('scripts-profile')
    @stack('scripts/dashboard')
    @stack('scripts-forums')
    @stack('scripts-forum-show')
    @stack('scripts-reports')
    @stack('scripts-sanctions')
    @stack('scripts-appeals')
    @stack('scripts-user-show')
    @stack('scripts-school')
    @stack('scripts-feedback')

</body>

</html>
