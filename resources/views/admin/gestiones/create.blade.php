@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6 bg-gray-50 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800">✨ Nueva Gestión Académica</h2>
            <p class="text-gray-600 text-sm mt-1">Configura el nuevo año escolar y sus periodos de descanso.</p>
        </div>
        
        <div class="p-6">
            <form action="{{ route('admin.gestiones.store') }}" method="POST">
                @include('admin.gestiones._form')
                
                <div class="mt-8 flex justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('admin.gestiones.index') }}" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-md transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Guardar y Configurar Calendario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection