<div id="modalEvento" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity opacity-0" id="modalBackdrop" onclick="cerrarModal()"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg opacity-0 scale-95" id="modalPanel">
                
                {{-- Encabezado --}}
                <div id="modalHeader" class="bg-gradient-to-r from-blue-600 to-blue-500 px-4 py-4 sm:px-6 transition-colors duration-300">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2" id="modalTitle">
                            <span>📅</span> Registrar Evento
                        </h3>
                        <button type="button" onclick="cerrarModal()" class="text-blue-100 hover:text-white transition">✕</button>
                    </div>
                </div>

                {{-- FORMULARIO PRINCIPAL (CREAR / EDITAR) --}}
                <form id="formCalendario" action="{{ route('admin.calendario.store') }}" method="POST">
                    @csrf
                    <div id="methodPut"></div> {{-- Aquí inyectaremos @method('PUT') vía JS si es editar --}}

                    <div class="px-4 py-6 sm:p-6 space-y-5">
                        
                        {{-- Fecha --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha</label>
                            <input type="date" id="fechaInput" name="fecha" readonly
                                class="block w-full rounded-lg border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed">
                        </div>

                        {{-- Tipo --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Tipo</label>
                            <select name="tipo" id="tipoInput" required class="block w-full rounded-lg border-gray-300 focus:ring-blue-500">
                                <option value="FERIADO">📅 Feriado</option>
                                <option value="VACACION">🏖️ Vacación</option>
                                <option value="EMERGENCIA">🚨 Emergencia</option>
                                <option value="TOLERANCIA">⚠️ Tolerancia</option>
                            </select>
                        </div>

                        {{-- Descripción --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Descripción</label>
                            <input type="text" name="descripcion" id="descripcionInput" required class="block w-full rounded-lg border-gray-300 focus:ring-blue-500">
                        </div>

                    </div>

                    {{-- Footer con Botones --}}
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t gap-2">
                        <button type="submit" id="btnGuardar" class="inline-flex w-full justify-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:w-auto">
                            Guardar
                        </button>
                        
                        {{-- Botón ELIMINAR (Solo visible al editar) --}}
                        <button type="button" id="btnEliminar" onclick="confirmarEliminacion()" class="hidden inline-flex w-full justify-center rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:w-auto">
                            Eliminar Evento
                        </button>

                        <button type="button" onclick="cerrarModal()" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                            Cancelar
                        </button>
                    </div>
                </form>

                {{-- FORMULARIO OCULTO PARA ELIMINAR --}}
                <form id="formEliminar" action="" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
</div>