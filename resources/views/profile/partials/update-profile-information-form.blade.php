<section>
    {{-- Contenedor principal con el estilo moderno --}}
    <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 hover:shadow-3xl w-full max-w-2xl mx-auto">
        <header class="mb-8">
            <h2 class="text-3xl font-extrabold text-gray-900 mb-4">
                <span class="text-blue-600">{{ __('Información del Perfil') }}</span>
            </h2>

            <p class="text-sm text-gray-600 leading-relaxed">
                {{ __('Actualiza la información de perfil y la dirección de correo electrónico de tu cuenta.') }}
            </p>
        </header>

        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
            @csrf
        </form>

        <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
            @csrf
            @method('patch')

            <div class="mb-5">
                <x-input-label for="name" :value="__('Nombre')" class="text-gray-700 font-semibold mb-2" />
                <x-text-input 
                    id="name" 
                    name="name" 
                    type="text" 
                    class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" 
                    :value="old('name', $user->name)" 
                    required 
                    autofocus 
                    autocomplete="name" 
                />
                <x-input-error class="mt-2 text-red-600 text-sm" :messages="$errors->get('name')" />
            </div>

            <div class="mb-5">
                <x-input-label for="email" :value="__('Correo Electrónico')" class="text-gray-700 font-semibold mb-2" />
                <x-text-input 
                    id="email" 
                    name="email" 
                    type="email" 
                    class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" 
                    :value="old('email', $user->email)" 
                    required 
                    autocomplete="username" 
                />
                <x-input-error class="mt-2 text-red-600 text-sm" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm text-yellow-800">
                            {{ __('Tu dirección de correo electrónico no ha sido verificada.') }}

                            <button form="send-verification" class="underline text-sm text-yellow-700 hover:text-yellow-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition duration-150 ease-in-out font-semibold">
                                {{ __('Haz clic aquí para volver a enviar el correo electrónico de verificación.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-medium text-sm text-green-600">
                                {{ __('Un nuevo enlace de verificación ha sido enviado a tu dirección de correo electrónico.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-between mt-8">
                <div>
                    @if (session('status') === 'profile-updated')
                        <p
                            x-data="{ show: true }"
                            x-show="show"
                            x-transition
                            x-init="setTimeout(() => show = false, 2000)"
                            class="text-sm text-green-600 font-semibold"
                        >{{ __('¡Perfil actualizado correctamente!') }}</p>
                    @endif
                </div>

                <x-primary-button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    {{ __('Guardar Cambios') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</section>