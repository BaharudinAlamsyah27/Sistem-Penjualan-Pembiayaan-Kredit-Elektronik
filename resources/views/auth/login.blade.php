<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Kata Sandi')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Ingat saya') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Lupa kata sandi?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Masuk') }}
            </x-primary-button>
        </div>

        <!-- Masuk Cepat Demo Portofolio (1-Klik) -->
        <div class="mt-6 p-4 rounded-xl border border-gray-200 bg-gray-50/60">
            <div class="text-sm font-semibold text-gray-800">
                Masuk Cepat Demo Portofolio (1-Klik)
            </div>
            <p class="mt-1 text-xs text-gray-500">
                Pilih peran di bawah untuk langsung mencoba aplikasi tanpa mengetik akun.
            </p>
            <div class="mt-3 grid grid-cols-3 gap-2">
                <button type="button"
                    onclick="demoLogin('admin@nexa.com')"
                    class="px-3 py-2 rounded-lg text-xs font-semibold text-white bg-[#2557a7] hover:bg-[#1e4687] transition">
                    Super Admin
                </button>
                <button type="button"
                    onclick="demoLogin('gesi@nexa.com')"
                    class="px-3 py-2 rounded-lg text-xs font-semibold text-white bg-emerald-700 hover:bg-emerald-800 transition">
                    Cabang Gesi
                </button>
                <button type="button"
                    onclick="demoLogin('gemolong@nexa.com')"
                    class="px-3 py-2 rounded-lg text-xs font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-100 transition">
                    Cabang Gemolong
                </button>
            </div>
        </div>
    </form>

    <script>
        function demoLogin(email) {
            const form = document.querySelector('form[action="{{ route('login') }}"]');
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password';
            form.submit();
        }
    </script>
</x-guest-layout>
