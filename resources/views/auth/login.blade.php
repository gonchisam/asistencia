<x-guest-layout>
    {{-- Contenedor principal con el estilo moderno --}}
    <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 hover:shadow-3xl w-full max-w-md mx-auto">

        <h2 class="text-3xl font-extrabold text-center text-gray-900 mb-8">
            <span class="text-blue-600">Iniciar Sesión</span>
        </h2>

        <x-auth-session-status class="mb-4 text-center text-sm text-green-600" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-5"> {{-- Ajusta el margen inferior para el espacio --}}
                <x-input-label for="email" :value="__('Email')" class="text-gray-700 font-semibold mb-2" />
                <x-text-input id="email" class="block mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-600 text-sm" />
            </div>

            <div class="mb-5"> {{-- Ajusta el margen inferior para el espacio --}}
                <x-input-label for="password" :value="__('Contraseña')" class="text-gray-700 font-semibold mb-2" /> {{-- Cambié a Contraseña --}}

                <x-text-input id="password" class="block mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />

                <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-600 text-sm" />
            </div>

            <div class="block mt-6"> {{-- Ajusta el margen superior --}}
                <label for="remember_me" class="inline-flex items-center cursor-pointer"> {{-- Added cursor-pointer --}}
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" name="remember"> {{-- Changed text-indigo to text-blue --}}
                    <span class="ms-2 text-sm text-gray-700">{{ __('Recordarme') }}</span> {{-- Cambié a Recordarme --}}
                </label>
            </div>

            <div class="flex items-center justify-between mt-8"> {{-- Usamos justify-between para separar Forgot Password y Log in --}}
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-blue-600 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out" href="{{ route('password.request') }}">
                        {{ __('¿Olvidaste tu contraseña?') }}
                    </a>
                @endif

                <x-primary-button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    {{ __('Ingresar') }} {{-- Cambié a Ingresar --}}
                </x-primary-button>
            </div>

            {{-- Enlace para registrarse, si es necesario --}}
            <div class="text-center mt-6">
                <p class="text-sm text-gray-700">
                    ¿No tienes una cuenta?
                    <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-700 underline transition duration-150 ease-in-out">
                        Regístrate aquí
                    </a>
                </p>
            </div>
        </form>
    </div>
</x-guest-layout>