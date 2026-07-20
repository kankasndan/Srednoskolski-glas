<!DOCTYPE html>
<html lang="mk">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Најави се - Средношколски Глас</title>
    <script src="https://kit.fontawesome.com/75475ebc14.js" crossorigin="anonymous"></script>
    @vite('resources/css/app.css', 'resources/js/app.js')
    <style>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />body {
            font-family: 'Manrope', Arial, sans-serif;
            background: #f4f5f9;
            @ margin: 0;
        }
    </style>
</head>

<body class="flex min-h-screen items-center justify-center p-6 bg-[f4f5f9]">

    <div class="w-full max-w-[420px] rounded-[20px] border border-[#E6E8F0] bg-white p-10 shadow-sm">

        <div class="mb-8 flex flex-col items-center text-center space-y-6">
            <div class="flex justify-center items-center space-x-3">
                <img src="{{ asset('images/logo.svg') }}" alt="" class="w-14">
                <div class="text-xs font-bold uppercase tracking-wide">Средношколски Глас</div>
            </div>
            <h1 class="mt-1 text-[26px] font-extrabold text-[#1F2333]">Најавa</h1>
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
                <input type="email" placeholder="admin@example.com" name="email"
                    class="w-full rounded-[10px] border border-[#E0E2EC] bg-[#F7F8FC] px-4 py-3 text-[14px] text-[#3A3D4D] outline-none focus:border-[#582FF5] focus:ring-2 focus:ring-[#582FF5]/20 placeholder:text-[#9598A6]" />
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
                        class="w-full rounded-[10px] border border-[#E0E2EC] bg-[#F7F8FC] px-4 py-3 pr-12 text-[14px] text-[#3A3D4D] outline-none focus:border-[#582FF5] focus:ring-2 focus:ring-[#582FF5]/20 placeholder:text-[#9598A6]" />
                    <button type="button" id="togglePassword"
                        class="absolute inset-y-0 right-0 flex items-center px-4 text-[#8A8FA3] transition hover:text-[#582FF5]"
                        aria-label="Покажи лозинка">
                        <i class="fa-solid fa-eye" id="togglePasswordIcon"></i>
                    </button>
                    @error('password')
                        <span class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <label class="flex items-center gap-2 text-[14px] text-[#595959]">
                <input type="checkbox" class="h-4 w-4 rounded border-[#CCCCCC] text-[#582FF5] focus:ring-[#582FF5]" name="remember" />
                Запомни ме
            </label>

            <button type="submit"
                class="mt-2 w-full rounded-[10px] bg-[#582FF5] py-3 text-[15px] font-bold text-white transition hover:bg-[#4A26D6]">
                Најави се
                <script>
                    const passwordInput = document.getElementById('password');
                    const togglePasswordButton = document.getElementById('togglePassword');
                    const togglePasswordIcon = document.getElementById('togglePasswordIcon');

                    togglePasswordButton.addEventListener('click', () => {
                        const isPasswordHidden = passwordInput.type === 'password';

                        passwordInput.type = isPasswordHidden ? 'text' : 'password';
                        togglePasswordIcon.classList.toggle('fa-eye', !isPasswordHidden);
                        togglePasswordIcon.classList.toggle('fa-eye-slash', isPasswordHidden);
                        togglePasswordButton.setAttribute('aria-label', isPasswordHidden ? 'Скриј лозинка' : 'Покажи лозинка');
                    });
                </script>

            </button>

        </form>

    </div>

</body>

</html>
