@extends('layouts.app')

@section('content')
    {{-- Estilos de FullCalendar --}}
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/es.global.min.js'></script>

    {{-- Contenedor Principal --}}
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
        
        {{-- Encabezado --}}
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                📅 Calendario Académico
            </h2>
            @if($gestion)
                <span class="bg-blue-100 text-blue-800 text-sm font-medium px-4 py-1.5 rounded-full border border-blue-200 shadow-sm">
                    Gestión Activa: <strong>{{ $gestion->nombre }}</strong>
                </span>
            @else
                <span class="bg-red-100 text-red-800 text-sm font-medium px-4 py-1.5 rounded-full border border-red-200">
                    ⚠️ Sin Gestión Activa
                </span>
            @endif
        </div>

        {{-- Mensajes --}}
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-r shadow-sm flex items-center" role="alert">
                <svg class="w-6 h-6 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        {{-- CALENDARIO --}}
        <div id='calendar' class="w-full"></div>
    </div>

    {{-- INCLUIMOS EL MODAL FLOTANTE (Partial) --}}
    @include('admin.calendario._modal_form')

    {{-- Scripts del Calendario y Animaciones del Modal --}}
    <script>
        // Variables Globales
        const modal = document.getElementById('modalEvento');
        const modalBackdrop = document.getElementById('modalBackdrop');
        const modalPanel = document.getElementById('modalPanel');
        
        // Elementos del Formulario
        const form = document.getElementById('formCalendario');
        const methodDiv = document.getElementById('methodPut');
        const btnGuardar = document.getElementById('btnGuardar');
        const btnEliminar = document.getElementById('btnEliminar');
        const modalTitle = document.getElementById('modalTitle');
        const modalHeader = document.getElementById('modalHeader');

        // Inputs
        const fechaInput = document.getElementById('fechaInput');
        const tipoInput = document.getElementById('tipoInput');
        const descInput = document.getElementById('descripcionInput');

        // URLs Base (Generadas por Blade)
        const urlStore = "{{ route('admin.calendario.store') }}";
        const urlUpdateBase = "{{ route('admin.calendario.update', ':id') }}";
        const urlDestroyBase = "{{ route('admin.calendario.destroy', ':id') }}";

        // ==============================
        // Lógica para ABRIR el Modal
        // ==============================

        // MODO CREAR (Click en día vacío)
        function abrirModalCrear(fecha) {
            resetForm();
            fechaInput.value = fecha;
            form.action = urlStore;
            
            // Estilos Crear
            modalTitle.innerHTML = "<span>✨</span> Nuevo Evento";
            modalHeader.className = "bg-gradient-to-r from-blue-600 to-blue-500 px-4 py-4 sm:px-6";
            btnGuardar.innerText = "Guardar Evento";
            
            mostrarModalEfecto();
        }

        // MODO EDITAR (Click en evento existente)
        function abrirModalEditar(info) {
            resetForm();
            
            // 1. Rellenar datos
            fechaInput.value = info.event.startStr;
            tipoInput.value = info.event.extendedProps.tipo;
            descInput.value = info.event.extendedProps.descripcion;

            // 2. Configurar URLs
            let id = info.event.id;
            let updateUrl = urlUpdateBase.replace(':id', id);
            let destroyUrl = urlDestroyBase.replace(':id', id);

            form.action = updateUrl;
            document.getElementById('formEliminar').action = destroyUrl;

            // 3. Inyectar PUT y mostrar botón Eliminar
            methodDiv.innerHTML = '<input type="hidden" name="_method" value="PUT">';
            btnEliminar.classList.remove('hidden');

            // 4. Estilos Editar
            modalTitle.innerHTML = "<span>✏️</span> Editar Evento";
            modalHeader.className = "bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-4 sm:px-6"; // Color diferente
            btnGuardar.innerText = "Actualizar Cambios";

            mostrarModalEfecto();
        }

        function resetForm() {
            form.reset();
            methodDiv.innerHTML = ''; // Quitar PUT
            btnEliminar.classList.add('hidden'); // Ocultar borrar
        }

        function confirmarEliminacion() {
            if(confirm('¿Estás seguro de eliminar este día no laborable? Las clases se restablecerán para esta fecha.')) {
                document.getElementById('formEliminar').submit();
            }
        }

        // ==============================
        // Efectos Visuales (Igual que antes)
        // ==============================
        function mostrarModalEfecto() {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modalBackdrop.classList.remove('opacity-0');
                modalPanel.classList.remove('opacity-0', 'scale-95');
                modalPanel.classList.add('opacity-100', 'scale-100');
            }, 50);
        }

        function cerrarModal() {
            modalBackdrop.classList.add('opacity-0');
            modalPanel.classList.remove('opacity-100', 'scale-100');
            modalPanel.classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        // ==============================
        // Inicialización FullCalendar
        // ==============================
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var eventosBackend = @json($eventos); // Ahora incluye 'id' y 'extendedProps'
            var limites = @json($limites);

            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'es',
                initialView: 'dayGridMonth',
                headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listYear' },
                validRange: limites ? { start: limites.inicio, end: limites.fin } : null,
                events: eventosBackend,
                height: 'auto',
                buttonText: { today: 'Hoy', month: 'Mes', list: 'Lista' },

                // CLICK EN DÍA VACÍO -> CREAR
                dateClick: function(info) {
                    abrirModalCrear(info.dateStr);
                },

                // CLICK EN EVENTO -> EDITAR
                eventClick: function(info) {
                    abrirModalEditar(info);
                }
            });
            calendar.render();
        });
    </script>
    {{-- Estilos CSS extra --}}
    <style>
        .fc-toolbar-title { font-size: 1.5rem !important; font-weight: 800 !important; color: #1f2937; }
        .fc-button-primary { background-color: #2563eb !important; border-color: #2563eb !important; border-radius: 0.5rem !important; padding: 0.4rem 1rem !important; font-weight: 600 !important; }
        .fc-button-primary:hover { background-color: #1d4ed8 !important; border-color: #1d4ed8 !important; }
        .fc .fc-daygrid-day.fc-day-today { background-color: #eff6ff !important; }
        /* Quitar subrayados feos de los eventos */
        .fc-event { cursor: pointer; border: none !important; border-radius: 4px; padding: 2px 4px; }
    </style>
@endsection