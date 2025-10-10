<section class="space-y-6">
    {{-- Contenedor principal con el estilo moderno --}}
    <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 hover:shadow-3xl w-full max-w-2xl mx-auto">
        <header class="mb-6">
            <h2 class="text-3xl font-extrabold text-gray-900 mb-4">
                <span class="text-red-600">{{ __('Eliminar Cuenta') }}</span>
            </h2>

            <p class="text-sm text-gray-600 leading-relaxed">
                {{ __('Una vez que su cuenta sea eliminada, todos sus recursos y datos serán borrados permanentemente. Antes de eliminar su cuenta, por favor descargue cualquier dato o información que desee conservar.') }}
            </p>
        </header>

        <x-danger-button
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
        >{{ __('Eliminar Cuenta') }}</x-danger-button>

        <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
            {{-- Contenedor del modal con el mismo estilo --}}
            <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 w-full max-w-md mx-auto">
                <form method="post" action="{{ route('profile.destroy') }}" class="space-y-6">
                    @csrf
                    @method('delete')

                    <h2 class="text-2xl font-extrabold text-gray-900 text-center">
                        <span class="text-red-600">{{ __('¿Está seguro?') }}</span>
                    </h2>

                    <p class="text-sm text-gray-600 text-center leading-relaxed">
                        {{ __('Una vez que su cuenta sea eliminada, todos sus recursos y datos serán borrados permanentemente. Por favor, ingrese su contraseña para confirmar que desea eliminar permanentemente su cuenta.') }}
                    </p>

                    <div class="mb-5">
                        <x-input-label for="password" value="{{ __('Contraseña') }}" class="text-gray-700 font-semibold mb-2" />
                        
                        <div class="relative flex items-center">
                            <x-text-input
                                id="password"
                                name="password"
                                type="password"
                                class="block w-full p-3 pr-10 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 transition duration-150 ease-in-out"
                                placeholder="{{ __('Ingrese su contraseña') }}"
                            />
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
                        
                        <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2 text-red-600 text-sm" />
                    </div>

                    <div class="flex justify-between items-center mt-8">
                        <x-secondary-button 
                            x-on:click="$dispatch('close')"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-6 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                        >
                            {{ __('Cancelar') }}
                        </x-secondary-button>

                        <x-danger-button 
                            class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        >
                            {{ __('Eliminar Cuenta') }}
                        </x-danger-button>
                    </div>
                </form>
            </div>
        </x-modal>
    </div>
</section>

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