@extends('layouts.app') {{-- Usamos el layout principal si estás fuera de x-app-layout --}}

@section('content')

    {{-- Contenedor principal con el mismo estilo moderno (shadow-2xl, rounded-xl) --}}
    <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 hover:shadow-3xl w-full max-w-3xl mx-auto">
        
        {{-- Encabezado con flecha y título unificado --}}
        <div class="flex items-center space-x-4 mb-8 pb-6 border-b border-gray-200">
            
            {{-- Flecha "Atrás" (Back Arrow) con estilo unificado --}}
            <a href="{{ route('admin.aulas.index') }}"
               class="text-gray-500 hover:text-blue-600 transition duration-150 ease-in-out p-2 rounded-full hover:bg-gray-100"
               title="Volver a la Gestión de Aulas">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>

            {{-- Título Principal con color azul destacado --}}
            <h2 class="text-3xl font-extrabold text-gray-900">
                <span class="text-blue-600">Editar Aula:</span> {{ $aula->nombre }}
            </h2>
        </div>

        {{-- Mensajes de estado (Opcional, si los manejas aquí o en el formulario) --}}
        @if (session('status'))
            <div class="mb-6 p-4 bg-green-100 border border-green-300 rounded-lg text-green-700 text-sm transition duration-300 ease-in-out">
                <strong class="font-semibold">¡Éxito!</strong>
                <span class="block sm:inline">{{ session('status') }}</span>
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-100 border border-red-300 rounded-lg text-red-700 text-sm">
                <strong class="font-semibold">¡Error de Validación!</strong>
                <span class="block sm:inline">Por favor, revisa los campos e intenta de nuevo.</span>
            </div>
        @endif

        {{-- Formulario de Edición --}}
        <form action="{{ route('admin.aulas.update', $aula) }}" method="POST">
            @csrf
            @method('PUT')
            
            {{-- Incluye el formulario de campos (se mantiene) --}}
            @include('admin.aulas._form')

            {{-- Nota: Asumiendo que _form ya tiene el botón de guardar. --}}
        </form>

    </div>
@endsection