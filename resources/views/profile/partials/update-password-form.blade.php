<section>
    {{-- Contenedor principal con el estilo moderno --}}
    <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 hover:shadow-3xl w-full max-w-2xl mx-auto">
        <header class="mb-8">
            <h2 class="text-3xl font-extrabold text-gray-900 mb-4">
                <span class="text-blue-600">{{ __('Actualizar Contraseña') }}</span>
            </h2>

            <p class="text-sm text-gray-600 leading-relaxed">
                {{ __('Asegúrate de que tu cuenta use una contraseña larga y aleatoria para mantener la seguridad.') }}
            </p>
        </header>

        <form method="post" action="{{ route('password.update') }}" class="space-y-6">
            @csrf
            @method('put')

            <div class="mb-5">
                <x-input-label for="update_password_current_password" :value="__('Contraseña Actual')" class="text-gray-700 font-semibold mb-2" />
                <div class="relative flex items-center">
                    <x-text-input 
                        id="update_password_current_password" 
                        name="current_password" 
                        type="password" 
                        class="block w-full p-3 pr-10 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" 
                        autocomplete="current-password" 
                    />
                    <button type="button" class="absolute right-0 top-1/2 -translate-y-1/2 flex items-center pr-3 focus:outline-none toggle-password" data-target="update_password_current_password">
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
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2 text-red-600 text-sm" />
            </div>

            <div class="mb-5">
                <x-input-label for="update_password_password" :value="__('Nueva Contraseña')" class="text-gray-700 font-semibold mb-2" />
                <div class="relative flex items-center">
                    <x-text-input 
                        id="update_password_password" 
                        name="password" 
                        type="password" 
                        class="block w-full p-3 pr-10 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" 
                        autocomplete="new-password" 
                    />
                    <button type="button" class="absolute right-0 top-1/2 -translate-y-1/2 flex items-center pr-3 focus:outline-none toggle-password" data-target="update_password_password">
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
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2 text-red-600 text-sm" />
            </div>

            <div class="mb-5">
                <x-input-label for="update_password_password_confirmation" :value="__('Confirmar Contraseña')" class="text-gray-700 font-semibold mb-2" />
                <div class="relative flex items-center">
                    <x-text-input 
                        id="update_password_password_confirmation" 
                        name="password_confirmation" 
                        type="password" 
                        class="block w-full p-3 pr-10 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" 
                        autocomplete="new-password" 
                    />
                    <button type="button" class="absolute right-0 top-1/2 -translate-y-1/2 flex items-center pr-3 focus:outline-none toggle-password" data-target="update_password_password_confirmation">
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
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2 text-red-600 text-sm" />
            </div>

            <div class="flex items-center justify-between mt-8">
                <div>
                    @if (session('status') === 'password-updated')
                        <p
                            x-data="{ show: true }"
                            x-show="show"
                            x-transition
                            x-init="setTimeout(() => show = false, 2000)"
                            class="text-sm text-green-600 font-semibold"
                        >{{ __('¡Contraseña actualizada correctamente!') }}</p>
                    @endif
                </div>

                <x-primary-button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    {{ __('Guardar Nueva Contraseña') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleButtons = document.querySelectorAll('.toggle-password');

        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const eyeOpen = this.querySelector('.eye-open');
                const eyeClosed = this.querySelector('.eye-closed');

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