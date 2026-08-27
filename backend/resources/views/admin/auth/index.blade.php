<!DOCTYPE html>
<html lang="mk">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Најава — Админ Панел — Средношколски Глас</title>
    <link rel="icon" type="image/svg" href="{{ asset('images/logo.svg') }}">
    <script src="https://kit.fontawesome.com/75475ebc14.js" crossorigin="anonymous"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex min-h-screen items-center justify-center bg-[#f4f5f9] p-6">

    <div class="w-full max-w-[420px] rounded-[20px] border border-[#E6E8F0] bg-white p-10 shadow-sm">

        <div class="mb-8 flex flex-col items-center space-y-6 text-center">
            <div class="flex items-center justify-center space-x-3">
                <img src="{{ asset('images/logo.svg') }}" alt="" class="w-14">
                <div class="text-xs font-bold uppercase tracking-wide">Средношколски Глас</div>
            </div>
            <h1 class="mt-1 text-[26px] font-extrabold text-[#1F2333]">Најава</h1>
        </div>

        <form class="flex flex-col gap-5" action="{{ route('admin.login') }}" method="POST">
            @csrf
            @error('credentials')
                <span class="text-xs text-red-600">{{ $message }}</span>
            @enderror
            <div>
                <label class="mb-2 block text-[14px] font-semibold text-[#1F2333]">
                    Е-пошта <span class="text-[#DC2626]">*</span>
                </label>
                <input type="email" placeholder="admin@example.com" name="email" value="{{ old('email') }}"
                    class="w-full rounded-[10px] border border-[#E0E2EC] bg-[#F7F8FC] px-4 py-3 text-[14px] text-[#3A3D4D] outline-none placeholder:text-[#9598A6] focus:border-my-purple focus:ring-2 focus:ring-my-purple/20" />
                @error('email')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label class="mb-2 block text-[14px] font-semibold text-[#1F2333]">
                    Лозинка <span class="text-[#DC2626]">*</span>
                </label>
                <div class="relative">
                    <input id="password" type="password" placeholder="••••••••" name="password"
                        class="w-full rounded-[10px] border border-[#E0E2EC] bg-[#F7F8FC] px-4 py-3 pr-12 text-[14px] text-[#3A3D4D] outline-none placeholder:text-[#9598A6] focus:border-my-purple focus:ring-2 focus:ring-my-purple/20" />
                    <button type="button" id="togglePassword"
                        class="absolute inset-y-0 right-0 flex items-center px-4 text-[#8A8FA3] transition hover:text-my-purple"
                        aria-label="Покажи лозинка">
                        <i class="fa-solid fa-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>
                @error('password')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-[14px] text-[#595959]">
                <input type="checkbox" class="h-4 w-4 rounded border-[#CCCCCC] text-my-purple focus:ring-my-purple"
                    name="remember" />
                Запомни ме
            </label>

            <button type="submit"
                class="mt-2 w-full rounded-[10px] bg-my-purple py-3 text-[15px] font-bold text-white transition hover:bg-my-purple/90">
                Најави се
            </button>
        </form>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const togglePasswordButton = document.getElementById('togglePassword');
        const togglePasswordIcon = document.getElementById('togglePasswordIcon');

        togglePasswordButton?.addEventListener('click', () => {
            const isPasswordHidden = passwordInput.type === 'password';

            passwordInput.type = isPasswordHidden ? 'text' : 'password';
            togglePasswordIcon.classList.toggle('fa-eye', !isPasswordHidden);
            togglePasswordIcon.classList.toggle('fa-eye-slash', isPasswordHidden);
            togglePasswordButton.setAttribute('aria-label', isPasswordHidden ? 'Скриј лозинка' : 'Покажи лозинка');
        });
    </script>

</body>

</html>
