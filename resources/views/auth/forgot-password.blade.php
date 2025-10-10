<x-guest-layout>
    {{-- Contenedor principal con el estilo moderno --}}
    <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 hover:shadow-3xl w-full max-w-md mx-auto">

        <h2 class="text-3xl font-extrabold text-center text-gray-900 mb-6">
            <span class="text-blue-600">Restablecer Contraseña</span>
        </h2>

        <div class="mb-6 text-sm text-gray-600 text-center leading-relaxed">
            {{ __('¿Olvidaste tu contraseña? No hay problema. Ingresa tu correo electrónico y te enviaremos un enlace para restablecerla.') }}
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4 text-center text-sm text-green-600" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- Email Address -->
            <div class="mb-5">
                <x-input-label for="email" :value="__('Correo Electrónico')" class="text-gray-700 font-semibold mb-2" />
                <x-text-input id="email" 
                    class="block mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" 
                    type="email" 
                    name="email" 
                    :value="old('email')" 
                    required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-600 text-sm" />
            </div>

            <div class="flex items-center justify-between mt-8">
                <!-- Botón Cancelar -->
                <a href="{{ url('/') }}" 
                   class="underline text-sm text-gray-600 hover:text-blue-600 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                    {{ __('Cancelar') }}
                </a>

                <!-- Botón Restablecer -->
                <x-primary-button 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    {{ __('Enviar enlace') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
