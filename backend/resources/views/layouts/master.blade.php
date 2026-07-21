<!DOCTYPE html>
<html lang="mk">

<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full overflow-hidden p-8">


    <header
        class="grid h-[72px] w-full grid-cols-[auto_1fr_auto] items-center border-b border-[#E6E8F0] bg-white px-8 gap-6">

        <!-- Brand (col 1) -->
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/logo.svg') }}" class="w-16" />
            <span class="whitespace-nowrap text-[16px] font-bold tracking-wide text-[#1F2333]">Админ Панел</span>
        </div>

        <!-- Search (col 2, always takes remaining space) -->
        <div class="flex justify-center">
            <div
                class="flex w-full max-w-[420px] items-center gap-2 rounded-[10px] border border-[#D8DAE5] bg-[#F7F8FC] px-4 py-2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="shrink-0 text-[#9598A6]">
                    <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2" />
                    <path d="M21 21l-4.3-4.3" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
                <input type="text" placeholder="Пребарај корисници, пријави, дискусии..."
                    class="w-full bg-transparent text-[14px] text-[#3A3D4D] outline-none placeholder:text-[#9598A6]" />
            </div>
        </div>

        <!-- Right side (col 3) -->
        <div class="flex items-center gap-4 justify-self-end">

            <!-- Notification bell -->
            <button id="bellBtn"
                class="relative flex h-9 w-9 items-center justify-center rounded-full border border-[#E6E8F0] text-[#595959] hover:bg-[#F4F2FF]">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
                    <path d="M13.73 21a2 2 0 01-3.46 0" />
                </svg>
                <span
                    class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-[#F88DD5] text-[10px] font-bold text-white">4</span>
            </button>

            <div class="h-8 w-px bg-[#E6E8F0]"></div>

            <!-- Admin dropdown -->
            <div class="relative">
                <button id="userMenuBtn" class="flex items-center gap-2 rounded-[10px] px-2 py-1 hover:bg-[#F4F2FF]">
                  @if($currentAdmin->imageUrl)
                    <img src="{{ $currentAdmin->imageUrl }} " alt="">
                  @else
                    <div
                    class="flex h-9 w-9 items-center justify-center rounded-full bg-my-purple text-[13px] font-bold text-white">
                        А</div>
                  @endif
                    <div class="hidden text-left sm:block">
                        <div class="whitespace-nowrap text-[13px] font-bold text-[#1F2333]">{{ $currentAdmin->username }}</div>
                        <div class="whitespace-nowrap text-[11px] text-[#9598A6]">{{ $currentAdmin->role }}</div>
                    </div>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" class="text-[#9598A6]">
                        <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>

                <div id="userMenu"
                    class="absolute right-0 top-[52px] hidden w-[200px] rounded-[10px] border border-[#E6E8F0] bg-white p-2 shadow-lg z-50">
                    <a href="#"
                        class="block rounded-[8px] px-3 py-2 text-[14px] text-[#595959] hover:bg-[#F4F2FF]">Мој
                        профил</a>
                    <a href="#"
                        class="block rounded-[8px] px-3 py-2 text-[14px] text-[#595959] hover:bg-[#F4F2FF]">Поставки</a>
                    <a href="#"
                        class="block rounded-[8px] px-3 py-2 text-[14px] font-semibold text-[#DC2626] hover:bg-[#FEE2E2]">Одјави
                        се</a>
                </div>
            </div>

        </div>

    </header>

    <main class="flex h-[calc(100vh-72px)] justify-center overflow-hidden">

        <nav id="sidebar"
            class="flex h-full shrink-0 flex-col gap-2 overflow-y-auto border-r border-[#CCCCCC] pr-4 pl-4 pt-8"></nav>

        <div class="h-full flex-1 overflow-y-auto max-w-6xl mx-auto w-full px-8 py-8">
            @yield('content')
        </div>

    </main>

    @stack("scripts")
    @stack("scripts1")

</body>

</html>
