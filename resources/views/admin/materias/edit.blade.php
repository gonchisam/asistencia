@extends('layouts.app')

@section('content')

    {{-- Contenedor principal con estilo moderno --}}
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-2xl p-8 transform transition-all duration-300 hover:shadow-3xl">
                
                {{-- Encabezado con flecha y título unificado --}}
                <div class="flex items-center space-x-4 mb-8 pb-6 border-b border-gray-200">
                    
                    {{-- Flecha "Atrás" (Back Arrow) --}}
                    <a href="{{ route('admin.materias.index') }}"
                       class="text-gray-500 hover:text-blue-600 transition duration-150 ease-in-out p-2 rounded-full hover:bg-gray-100"
                       title="Volver a la Gestión de Materias">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>

                    {{-- Título Principal usando $materia --}}
                    <h2 class="text-3xl font-extrabold text-gray-900">
                        <span class="text-blue-600">Editar Materia:</span> {{ $materia->nombre }} ✏️
                    </h2>
                </div>

                <div class="p-6 text-gray-900">
                    {{-- Mensajes de error de validación (el parcial también los maneja) --}}

                    {{-- Formulario de Edición usando $materia --}}
                    <form action="{{ route('admin.materias.update', $materia) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        {{-- Incluye el formulario de campos --}}
                        @include('admin.materias._form', ['buttonText' => 'Actualizar Materia'])
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection