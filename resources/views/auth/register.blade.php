<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Nombre Completo')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Correo Electrónico')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4 relative">
            <x-input-label for="password" :value="__('Contraseña')" />
            <div class="relative flex items-center">
                <x-text-input id="password" class="block w-full pr-10"
                              type="password"
                              name="password"
                              required autocomplete="new-password" />
                <button type="button" class="absolute right-0 top-1/2 -translate-y-1/2 flex items-center pr-3 focus:outline-none toggle-password">
                    <svg class="h-5 w-5 text-gray-400 eye-open" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg class="h-5 w-5 text-gray-400 eye-closed hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 .45-1.42 1.15-2.73 2.15-3.86M10.707 5.293a10.05 10.05 0 013.29-1.217C16.48 4 20.27 6.943 21.542 11c-.45 1.42-1.15 2.73-2.15 3.86" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4 relative">
            <x-input-label for="password_confirmation" :value="__('Confirme Contraseña')" />
            <div class="relative flex items-center">
                <x-text-input id="password_confirmation" class="block w-full pr-10"
                              type="password"
                              name="password_confirmation" required autocomplete="new-password" />
                <button type="button" class="absolute right-0 top-1/2 -translate-y-1/2 flex items-center pr-3 focus:outline-none toggle-password">
                    <svg class="h-5 w-5 text-gray-400 eye-open" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg class="h-5 w-5 text-gray-400 eye-closed hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 .45-1.42 1.15-2.73 2.15-3.86M10.707 5.293a10.05 10.05 0 013.29-1.217C16.48 4 20.27 6.943 21.542 11c-.45 1.42-1.15 2.73-2.15 3.86" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-4 relative">
            <x-input-label for="api_key" :value="__('Código de Autenticación')" />
            <div class="relative flex items-center">
                <x-text-input id="api_key" class="block w-full pr-10" type="password" name="api_key" required />
                <button type="button" class="absolute right-0 top-1/2 -translate-y-1/2 flex items-center pr-3 focus:outline-none toggle-password">
                    <svg class="h-5 w-5 text-gray-400 eye-open" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg class="h-5 w-5 text-gray-400 eye-closed hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 .45-1.42 1.15-2.73 2.15-3.86M10.707 5.293a10.05 10.05 0 013.29-1.217C16.48 4 20.27 6.943 21.542 11c-.45 1.42-1.15 2.73-2.15 3.86" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('api_key')" class="mt-2" />
        </div>

        <div class="flex flex-col mt-6">
            <!-- Botones principales en una fila -->
            <div class="flex items-center justify-between w-full">
                <!-- Botón Cancelar a la izquierda -->
                <a href="{{ url('/') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 underline decoration-2 hover:decoration-gray-700">
                    {{ __('Cancelar') }}
                </a>
                
                <!-- Botón Registrarse a la derecha -->
                <x-primary-button class="ms-4">
                    {{ __('Registrarse') }}
                </x-primary-button>
            </div>

            <!-- Enlace "¿Ya estás registrado?" debajo del botón Registrarse -->
            <div class="flex justify-end mt-3">
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                    {{ __('¿Ya estás registrado?') }}
                </a>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleButtons = document.querySelectorAll('.toggle-password');

            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const parentDiv = this.closest('.relative');
                    const input = parentDiv.querySelector('input');
                    const eyeOpen = parentDiv.querySelector('.eye-open');
                    const eyeClosed = parentDiv.querySelector('.eye-closed');

                    // Alterna el tipo del input
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);

                    // Alterna la visibilidad de los íconos
                    eyeOpen.classList.toggle('hidden');
                    eyeClosed.classList.toggle('hidden');
                });
            });
        });
    </script>
</x-guest-layout>