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
                        
                        {{-- =============================================== --}}
                        {{-- ========= INICIO: NUEVA PESTAÑA DOCENTES ======== --}}
                        {{-- =============================================== --}}
                        {{-- Solo visible para admin/superadmin, basado en tu migración --}}
                        @if(auth()->user()->role == 'administrador' || auth()->user()->role == 'superadmin')
                        <button
                            id="docentes-tab"
                            class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 ease-in-out border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
                            data-tab="docentes"
                        >
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.824-2.167-1.943-2.39a4.125 4.125 0 0 0-1.12 0c-1.119.223-1.943 1.277-1.943 2.39v.003m0 0a4.125 4.125 0 0 0-7.533-2.493 9.337 9.337 0 0 0 4.121.952 9.38 9.38 0 0 0 2.625-.372M15 19.128c-.418 0-.79-.037-1.15-.107a4.125 4.125 0 0 1-4.699-3.412 4.125 4.125 0 0 1 4.699-3.412 4.125 4.125 0 0 1 0 6.824c.36.07.732.107 1.15.107m0 0a4.125 4.125 0 0 0 4.699 3.412 4.125 4.125 0 0 0 0-6.824 4.125 4.125 0 0 0-4.699 3.412M12 6.003c.12.003.236.01.349.023a3.8 3.8 0 0 1 3.5 3.5c.012.112.02.228.02.347m0 0c-.12-.003-.236-.01-.349-.023a3.8 3.8 0 0 0-3.5-3.5C8.36 6.013 8.244 6.006 8.124 6.003m3.876 0A3.875 3.875 0 0 0 8.124 9.876m3.876 0A3.875 3.875 0 0 1 15.876 9.876m0 0A3.875 3.875 0 0 1 12 13.753m0 0A3.875 3.875 0 0 1 8.124 9.876m3.876 0A3.875 3.875 0 0 0 15.876 9.876m0 0A3.875 3.875 0 0 0 12 6.003" />
                                </svg>
                                Lista de Docentes
                            </span>
                        </button>
                        @endif
                        {{-- =============================================== --}}
                        {{-- ============ FIN: NUEVA PESTAÑA DOCENTES ======== --}}
                        {{-- =============================================== --}}

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

                    {{-- =============================================== --}}
                    {{-- ========= INICIO: NUEVO CONTENIDO PESTAÑA ======= --}}
                    {{-- =============================================== --}}
                    @if(auth()->user()->role == 'administrador' || auth()->user()->role == 'superadmin')
                    <div id="docentes-content" class="tab-content hidden">
                        {{-- CAMBIO IMPORTANTE: Cambiar max-w-2xl por w-full para que ocupe todo el ancho disponible --}}
                        <div class="w-full">
                            @include('profile.partials.list-docentes')
                        </div>
                    </div>
                    @endif
                    {{-- =============================================== --}}
                    {{-- ============ FIN: NUEVO CONTENIDO PESTAÑA ======= --}}
                    {{-- =============================================== --}}

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

                // Remover clase active de todos los botones y restaurar el estilo inactivo
                tabButtons.forEach(button => {
                    // Quitamos las clases de ACTIVO
                    button.classList.remove('active', 'border-blue-500', 'text-blue-600');
                    // Agregamos las clases de INACTIVO
                    button.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
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
                    // Quitamos las clases de INACTIVO
                    activeButton.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                    // Agregamos las clases de ACTIVO
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