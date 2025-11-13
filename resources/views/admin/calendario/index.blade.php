@extends('layouts.app')

@section('content')
    {{-- Estilos de FullCalendar --}}
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/es.global.min.js'></script>

    {{-- Contenedor Principal (Estilo Tailwind idéntico a tu Dashboard) --}}
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
        
        {{-- Encabezado de la Tarjeta --}}
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h2 class="text-2xl font-bold text-gray-800">
                📅 Calendario Académico
            </h2>
            @if($gestion)
                <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full border border-blue-200">
                    Gestión Activa: <strong>{{ $gestion->nombre }}</strong>
                </span>
            @else
                <span class="bg-red-100 text-red-800 text-sm font-medium px-3 py-1 rounded-full border border-red-200">
                    ⚠️ Sin Gestión Activa
                </span>
            @endif
        </div>

        {{-- Mensajes de Alerta --}}
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm" role="alert">
                <p class="font-bold">¡Éxito!</p>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        {{-- CONTENEDOR DEL CALENDARIO --}}
        <div id='calendar' class="w-full"></div>
    </div>

    {{-- ========================================== --}}
    {{-- MODAL TAILWIND (Ventana Emergente) --}}
    {{-- ========================================== --}}
    <div id="modalEvento" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="cerrarModal()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form action="{{ route('admin.calendario.store') }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Registrar Suspensión / Evento
                                </h3>
                                <div class="mt-4 space-y-4">
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Fecha Seleccionada</label>
                                        <input type="date" id="fechaInput" name="fecha" readonly
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 bg-gray-100 cursor-not-allowed">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Tipo de Evento</label>
                                        <select name="tipo" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-2 border">
                                            <option value="EMERGENCIA">🚨 Emergencia / Bloqueo (Suspensión)</option>
                                            <option value="FERIADO">📅 Feriado Nacional/Local</option>
                                            <option value="VACACION">🏖️ Vacación</option>
                                            <option value="TOLERANCIA">⚠️ Tolerancia (Hay clases)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Motivo / Descripción</label>
                                        <input type="text" name="descripcion" placeholder="Ej: Paro de transportes..." required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-2 border">
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Guardar Evento
                        </button>
                        <button type="button" onclick="cerrarModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Scripts del Calendario --}}
    <script>
        // Funciones para abrir/cerrar el modal Tailwind
        function abrirModal() {
            document.getElementById('modalEvento').classList.remove('hidden');
        }
        function cerrarModal() {
            document.getElementById('modalEvento').classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var eventosBackend = @json($eventos);
            var limites = @json($limites);

            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'es',
                initialView: 'dayGridMonth',
                headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listYear' },
                validRange: limites ? { start: limites.inicio, end: limites.fin } : null,
                events: eventosBackend,
                height: 'auto', // Ajuste de altura automático
                
                // Personalización de botones para que parezcan Tailwind
                buttonText: { today: 'Hoy', month: 'Mes', list: 'Lista' },
                
                dateClick: function(info) {
                    document.getElementById('fechaInput').value = info.dateStr;
                    abrirModal(); // Abrimos nuestro modal Tailwind
                }
            });
            calendar.render();
        });
    </script>

    {{-- Estilos CSS extra para "Tailwindizar" FullCalendar --}}
    <style>
        /* Sobrescribimos estilos de FullCalendar para que coincidan con Tailwind */
        .fc-toolbar-title { font-size: 1.25rem !important; font-weight: 700 !important; color: #1f2937; }
        .fc-button-primary { background-color: #3b82f6 !important; border-color: #3b82f6 !important; }
        .fc-button-primary:hover { background-color: #2563eb !important; border-color: #2563eb !important; }
        .fc-button-primary:disabled { background-color: #93c5fd !important; border-color: #93c5fd !important; }
        .fc .fc-daygrid-day.fc-day-today { background-color: #eff6ff !important; } /* Un azul muy suave para hoy */
    </style>
@endsection