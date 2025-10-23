@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Contenedor principal con pestañas --}}
            <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
                {{-- Navegación de pestañas --}}
                <div class="border-b border-gray-200">
                    <nav class="flex space-x-8 px-6" aria-label="Tabs">
                        {{-- Pestaña Información del Perfil --}}
                        <button
                            id="profile-tab"
                            class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 ease-in-out border-blue-500 text-blue-600"
                            data-tab="profile"
                        >
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Información del Perfil
                            </span>
                        </button>

                        {{-- Pestaña Contraseña --}}
                        <button
                            id="password-tab"
                            class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 ease-in-out border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
                            data-tab="password"
                        >
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                Contraseña
                            </span>
                        </button>

                        {{-- Pestaña Eliminar Cuenta (solo visible para usuarios autorizados) --}}
                        @can('delete-account')
                        <button
                            id="delete-tab"
                            class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 ease-in-out border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
                            data-tab="delete"
                        >
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Eliminar Cuenta
                            </span>
                        </button>
                        @endcan
                    </nav>
                </div>

                {{-- Contenido de las pestañas --}}
                <div class="p-6">
                    {{-- Pestaña Información del Perfil --}}
                    <div id="profile-content" class="tab-content active">
                        <div class="max-w-2xl">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>

                    {{-- Pestaña Contraseña --}}
                    <div id="password-content" class="tab-content hidden">
                        <div class="max-w-2xl">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>

                    {{-- Pestaña Eliminar Cuenta (solo visible para usuarios autorizados) --}}
                    @can('delete-account')
                    <div id="delete-content" class="tab-content hidden">
                        <div class="max-w-2xl">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <style>
        .tab-button {
            position: relative;
            transition: all 0.3s ease-in-out;
        }

        .tab-button:hover {
            transform: translateY(-1px);
        }

        .tab-button.active {
            border-color: #3b82f6;
            color: #3b82f6;
        }

        .tab-content {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            // Función para cambiar de pestaña
            function switchTab(tabName) {
                // Ocultar todos los contenidos
                tabContents.forEach(content => {
                    content.classList.remove('active');
                    content.classList.add('hidden');
                });

                // Remover clase active de todos los botones
                tabButtons.forEach(button => {
                    button.classList.remove('active', 'border-blue-500', 'text-blue-600');
                    button.classList.add('border-transparent', 'text-gray-500');
                });

                // Mostrar el contenido seleccionado
                const activeContent = document.getElementById(`${tabName}-content`);
                if (activeContent) {
                    activeContent.classList.remove('hidden');
                    activeContent.classList.add('active');
                }

                // Activar el botón seleccionado
                const activeButton = document.getElementById(`${tabName}-tab`);
                if (activeButton) {
                    activeButton.classList.remove('border-transparent', 'text-gray-500');
                    activeButton.classList.add('border-blue-500', 'text-blue-600', 'active');
                }
            }

            // Agregar event listeners a los botones
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const tabName = this.getAttribute('data-tab');
                    switchTab(tabName);
                });
            });

            // Activar la primera pestaña por defecto
            switchTab('profile');
        });
    </script>
@endsection