@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6 bg-gray-50 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800">✏️ Editar Gestión: {{ $gestione->nombre }}</h2>
        </div>
        
        <div class="p-6">
            <form action="{{ route('admin.gestiones.update', $gestione) }}" method="POST">
                @method('PUT')
                @include('admin.gestiones._form')
                
                <div class="mt-8 flex justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('admin.gestiones.index') }}" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-md transition-colors">
                        Actualizar Datos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection