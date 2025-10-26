@extends('layouts.app')

@section('content')
<div class="bg-white rounded-xl shadow-2xl p-8 max-w-5xl mx-auto">
    
    <div class="flex items-center justify-center mb-8 relative">
        <a href="{{ route('students.index') }}" 
           class="absolute left-0 bg-gray-600 hover:bg-gray-700 text-white p-3 rounded-full transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transform hover:scale-105"
           title="Regresar">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <h1 class="text-3xl font-extrabold text-gray-900">
            <span class="text-blue-600">Asignar Tarjetas RFID Pendientes</span>
        </h1>
    </div>

    @include('admin.partials._session-messages')

    <p class="text-gray-600 mb-6 text-center">
        Se encontraron <span class="font-bold text-blue-600">{{ $estudiantesPendientes->count() }}</span> estudiantes activos que necesitan una tarjeta RFID.
    </p>

    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-50 text-gray-600 uppercase text-sm leading-normal">
                <tr>
                    <th class="py-4 px-6 text-left">CI</th>
                    <th class="py-4 px-6 text-left">Nombre Completo</th>
                    <th class="py-4 px-6 text-left">Carrera</th>
                    <th class="py-4 px-6 text-center">Acción</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
                @forelse ($estudiantesPendientes as $student)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="py-4 px-6 text-left font-mono">{{ $student->ci }}</td>
                        <td class="py-4 px-6 text-left whitespace-nowrap">
                            <span class="font-medium text-gray-900">{{ $student->nombre_completo }}</span>
                        </td>
                        <td class="py-4 px-6 text-left">{{ $student->carrera }}</td>
                        <td class="py-4 px-6 text-center">
                            <button 
                                type="button" 
                                class="open-modal-btn bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out"
                                data-id="{{ $student->id }}"
                                data-nombre="{{ $student->nombre_completo }}">
                                Asignar UID
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-8 px-6 text-center text-gray-500">
                            ¡Felicidades! No hay estudiantes pendientes de asignación.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="assign-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md mx-auto transform transition-all" 
         @click.away="modalOpen = false" 
         x-transition:enter="ease-out duration-300" 
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
         x-transition:leave="ease-in duration-200" 
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
        
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Asignar Tarjeta</h2>
        
        <form id="assign-form" action="{{ route('admin.estudiantes.asignar-uid.procesar') }}" method="POST">
            @csrf
            <input type="hidden" name="student_id" id="modal_student_id">

            <div class="space-y-4">
                <div>
                    <span class="text-sm font-medium text-gray-600">Estudiante:</span>
                    <p id="modal_student_name" class="text-lg font-semibold text-blue-700"></p>
                </div>
                
                <div>
                    <x-input-label for="modal_uid_input" :value="__('Escanear Tarjeta (o ingresar UID)')" />
                    <div class="flex items-center space-x-3 mt-2">
                        <x-text-input 
                            type="text" 
                            name="uid" 
                            id="modal_uid_input" 
                            class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out"
                            placeholder="Esperando UID..." 
                            required 
                            autocomplete="off" 
                        />
                        <button type="button" 
                                class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transform hover:scale-105"
                                id="modal_refresh_button"
                                title="Actualizar UID">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>
                    <p id="modal_uid_status" class="mt-2 text-sm font-medium"></p>
                </div>

                <div class="flex items-center justify-end space-x-4 pt-4 border-t mt-6">
                    <button type="button" id="modal_cancel_button" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded-lg transition duration-150 ease-in-out">
                        Cancelar
                    </button>
                    <x-primary-button type="submit" id="modal_submit_button" disabled class="opacity-50 cursor-not-allowed">
                        Guardar Asignación
                    </x-primary-button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Elementos del Modal
    const modal = document.getElementById('assign-modal');
    const openModalButtons = document.querySelectorAll('.open-modal-btn');
    const cancelButton = document.getElementById('modal_cancel_button');
    const refreshButton = document.getElementById('modal_refresh_button');
    
    // Elementos del Formulario
    const uidInput = document.getElementById('modal_uid_input');
    const studentIdInput = document.getElementById('modal_student_id');
    const studentNameEl = document.getElementById('modal_student_name');
    const uidStatus = document.getElementById('modal_uid_status');
    const submitButton = document.getElementById('modal_submit_button');

    let typingTimer;
    const doneTypingInterval = 500; // 500ms
    let pollInterval = null;
    let isModalOpen = false;
    let isManualMode = false;

    // --- Función para iniciar el polling automático ---
    function startPolling() {
        if (!pollInterval && !isManualMode && isModalOpen) {
            uidStatus.textContent = 'Esperando lectura de UID RFID...';
            uidStatus.className = 'text-sm text-blue-600 font-medium';
            pollInterval = setInterval(pollForUid, 2000);
        }
    }

    // --- Función para detener el polling ---
    function stopPolling() {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }

    // --- Función para obtener UID automáticamente ---
    function pollForUid() {
        if (!isModalOpen || isManualMode) return;

        fetch('/api/get-uid')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.uid && uidInput.value === '') {
                    uidInput.value = data.uid;
                    stopPolling();
                    checkUid();
                }
            })
            .catch(error => {
                console.error('Error getting temporary UID:', error);
            });
    }

    // --- Función para verificar UID ---
    async function checkUid() {
        const uidValue = uidInput.value.trim().toUpperCase();
        uidInput.value = uidValue;
        
        if (!uidValue) {
            uidStatus.textContent = '';
            submitButton.disabled = true;
            submitButton.classList.add('opacity-50', 'cursor-not-allowed');
            if (!isManualMode && isModalOpen) {
                startPolling();
            }
            return;
        }

        try {
            uidStatus.textContent = 'Verificando...';
            uidStatus.className = 'text-sm text-blue-600 font-medium';
            
            const response = await fetch('{{ route('students.check_uid') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ uid: uidValue })
            });

            const data = await response.json();

            if (data.exists) {
                uidStatus.textContent = '¡Este UID ya está en uso!';
                uidStatus.className = 'text-sm text-red-600 font-bold';
                submitButton.disabled = true;
                submitButton.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                uidStatus.textContent = 'UID disponible y listo.';
                uidStatus.className = 'text-sm text-green-600 font-bold';
                submitButton.disabled = false;
                submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        } catch (error) {
            uidStatus.textContent = 'Error al verificar el UID.';
            uidStatus.className = 'text-sm text-red-600 font-bold';
            submitButton.disabled = true;
            submitButton.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    // --- Función para abrir el modal ---
    function openModal(e) {
        const button = e.currentTarget;
        const studentId = button.dataset.id;
        const studentName = button.dataset.nombre;

        // Rellenar formulario
        studentIdInput.value = studentId;
        studentNameEl.textContent = studentName;
        
        // Resetear estado
        uidInput.value = '';
        uidStatus.textContent = '';
        submitButton.disabled = true;
        submitButton.classList.add('opacity-50', 'cursor-not-allowed');
        isManualMode = false;
        
        // Mostrar modal
        modal.classList.remove('hidden');
        isModalOpen = true;
        
        // Iniciar polling automático
        startPolling();
        
        // Enfocar el input de UID automáticamente
        setTimeout(() => {
            uidInput.focus();
        }, 100);
    }

    // --- Función para cerrar el modal ---
    function closeModal() {
        modal.classList.add('hidden');
        isModalOpen = false;
        stopPolling();
        
        // Limpiar formulario al cerrar
        uidInput.value = '';
        uidStatus.textContent = '';
        uidStatus.className = 'mt-2 text-sm font-medium';
        submitButton.disabled = true;
        submitButton.classList.add('opacity-50', 'cursor-not-allowed');
        isManualMode = false;
    }

    // --- Asignar Eventos ---

    // Abrir modal
    openModalButtons.forEach(button => {
        button.addEventListener('click', openModal);
    });

    // Cerrar modal
    cancelButton.addEventListener('click', closeModal);
    
    // Cerrar modal al hacer clic en el fondo oscuro
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    // Botón de refresh para modo manual
    refreshButton.addEventListener('click', function() {
        uidInput.value = '';
        uidInput.focus();
        isManualMode = true;
        stopPolling();
        uidStatus.textContent = 'Modo manual activado. Ingrese el UID manualmente.';
        uidStatus.className = 'text-sm text-blue-600 font-medium';
        checkUid();
    });

    // Verificar UID al teclear (con 'debounce')
    uidInput.addEventListener('input', function() {
        clearTimeout(typingTimer);
        submitButton.disabled = true;
        submitButton.classList.add('opacity-50', 'cursor-not-allowed');
        
        typingTimer = setTimeout(checkUid, doneTypingInterval);
    });

    // Manejar enfoque del input (activar modo manual)
    uidInput.addEventListener('focus', function() {
        isManualMode = true;
        stopPolling();
        if (uidInput.value.trim() === '') {
            uidStatus.textContent = 'Modo manual activado. Ingrese el UID manualmente.';
            uidStatus.className = 'text-sm text-blue-600 font-medium';
        }
    });

    // Manejar pérdida de enfoque del input
    uidInput.addEventListener('blur', function() {
        if (uidInput.value.trim() === '') {
            isManualMode = false;
            uidStatus.textContent = '';
            if (isModalOpen) {
                startPolling();
            }
        }
    });

    // Convertir a mayúsculas automáticamente
    uidInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });

});
</script>
@endsection