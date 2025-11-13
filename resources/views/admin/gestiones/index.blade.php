@extends('layouts.app')

@section('content')
<div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
    <div class="flex justify-between items-center mb-6 border-b pb-4">
        <h2 class="text-2xl font-bold text-gray-800">📅 Gestiones Académicas</h2>
        <a href="{{ route('admin.gestiones.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200 shadow-md flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Nueva Gestión
        </a>
    </div>

    @include('admin.partials._session-messages')

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 text-gray-700 uppercase text-sm font-bold">
                    <th class="py-3 px-4 border-b rounded-tl-lg">Nombre</th>
                    <th class="py-3 px-4 border-b">Inicio</th>
                    <th class="py-3 px-4 border-b">Fin</th>
                    <th class="py-3 px-4 border-b text-center">Estado</th>
                    <th class="py-3 px-4 border-b rounded-tr-lg text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="text-gray-600">
                @forelse($gestiones as $gestion)
                <tr class="hover:bg-gray-50 border-b transition duration-150">
                    <td class="py-3 px-4 font-medium text-gray-900">{{ $gestion->nombre }}</td>
                    <td class="py-3 px-4">{{ $gestion->fecha_inicio->format('d/m/Y') }}</td>
                    <td class="py-3 px-4">{{ $gestion->fecha_fin->format('d/m/Y') }}</td>
                    <td class="py-3 px-4 text-center">
                        @if($gestion->actual)
                            <span class="bg-green-100 text-green-800 text-xs font-bold px-3 py-1 rounded-full border border-green-200 shadow-sm">ACTIVA</span>
                        @else
                            <span class="bg-gray-100 text-gray-500 text-xs px-3 py-1 rounded-full border border-gray-200">Inactiva</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-right flex justify-end items-center gap-2">
                        @if(!$gestion->actual)
                            {{-- Botón para Activar (Abre Modal) --}}
                            <button onclick="abrirModalActivacion({{ $gestion->id }}, '{{ $gestion->nombre }}')" 
                                    class="text-sm bg-green-100 text-green-700 py-1 px-3 rounded-full hover:bg-green-200 font-bold transition flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Activar
                            </button>
                        @endif

                        <a href="{{ route('admin.gestiones.edit', $gestion) }}" class="text-indigo-600 hover:text-indigo-900 font-medium p-1">Editar</a>
                        
                        <form action="{{ route('admin.gestiones.destroy', $gestion) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Seguro?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900 font-medium p-1">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-8 text-gray-400">No hay gestiones registradas aún.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL DE CONFIRMACIÓN DE CONTRASEÑA --}}
<div id="modalPassword" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm transition-opacity" onclick="cerrarModalActivacion()"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Confirmar Cambio de Gestión
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 mb-4">
                                    Estás a punto de activar la <strong id="nombreGestionModal"></strong>. Esto requiere permisos de administrador.
                                </p>
                                
                                {{-- El Formulario se inyecta aquí --}}
                                <form id="formActivacion" method="POST" action="">
                                    @csrf
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Ingresa tu contraseña:</label>
                                    <input type="password" name="password_confirm" required autofocus
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    
                                    @if($errors->has('password_confirm'))
                                        <span class="text-red-500 text-xs mt-1">{{ $errors->first('password_confirm') }}</span>
                                        {{-- Script simple para reabrir modal si hay error --}}
                                        <script>document.addEventListener("DOMContentLoaded", ()=>{ document.getElementById('modalPassword').classList.remove('hidden'); });</script>
                                    @endif

                                    <div class="mt-5 sm:flex sm:flex-row-reverse">
                                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                                            Confirmar
                                        </button>
                                        <button type="button" onclick="cerrarModalActivacion()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                            Cancelar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function abrirModalActivacion(id, nombre) {
        document.getElementById('nombreGestionModal').innerText = nombre;
        // Construimos la ruta dinámicamente
        let url = "{{ route('admin.gestiones.activar', ':id') }}";
        url = url.replace(':id', id);
        document.getElementById('formActivacion').action = url;
        
        document.getElementById('modalPassword').classList.remove('hidden');
    }
    function cerrarModalActivacion() {
        document.getElementById('modalPassword').classList.add('hidden');
    }
</script>
@endsection