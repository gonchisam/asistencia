<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Asistencias RFID</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Toastify (para notificaciones) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <!-- Estilos personalizados -->
    <style>
        .badge-entrada {
            background-color: #28a745;
        }
        .badge-salida {
            background-color: #dc3545;
        }
        .card-header {
            background-color: #343a40;
            color: white;
        }
        .font-monospace {
            font-family: 'SFMono-Regular', Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        }
        .status-card {
            transition: all 0.3s ease;
        }
        .status-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
    </style>
    @livewireStyles
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="mb-0"><i class="fas fa-id-card-alt me-2"></i> Sistema de Asistencia RFID</h2>
                        <div>
                            <span class="badge bg-primary">
                                <i class="fas fa-wifi me-1"></i>
                                <span id="connection-status">Conectado</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjetas de Resumen -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card status-card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Total Registros</h5>
                                <h2 class="mb-0" id="total-registros">0</h2>
                            </div>
                            <div class="icon-circle">
                                <i class="fas fa-database fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card status-card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Entradas Hoy</h5>
                                <h2 class="mb-0" id="entradas-hoy">0</h2>
                            </div>
                            <div class="icon-circle">
                                <i class="fas fa-sign-in-alt fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card status-card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Salidas Hoy</h5>
                                <h2 class="mb-0" id="salidas-hoy">0</h2>
                            </div>
                            <div class="icon-circle">
                                <i class="fas fa-sign-out-alt fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body">
                        <form id="filtros-form">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="fecha" class="form-label">Fecha</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha">
                                </div>
                                <div class="col-md-3">
                                    <label for="estudiante" class="form-label">Estudiante</label>
                                    <select class="form-select" id="estudiante" name="estudiante">
                                        <option value="">Todos</option>
                                        <option value="Carlos">Carlos</option>
                                        <option value="Paul">Paul</option>
                                        <option value="Gonchi">Gonchi</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="accion" class="form-label">Acción</label>
                                    <select class="form-select" id="accion" name="accion">
                                        <option value="">Todas</option>
                                        <option value="ENTRADA">Entrada</option>
                                        <option value="SALIDA">Salida</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-filter me-1"></i> Filtrar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Asistencias -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-list me-2"></i>Registros Recientes</h4>
                        <div>
                            <button id="exportar-excel" class="btn btn-sm btn-success me-2">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </button>
                            <button id="recargar-datos" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-sync-alt me-1"></i> Recargar
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @livewire('asistencia-list')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para detalles -->
    <div class="modal fade" id="detalleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalle de Asistencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detalle-contenido">
                    <!-- Contenido dinámico -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Toastify -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <!-- Pusher -->
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    <!-- Laravel Echo -->
    <script src="{{ asset('js/echo.js') }}"></script>
    <!-- SheetJS (para exportar Excel) -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    
    @livewireScripts
    
    <script>
        // Inicializar Echo/Pusher
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: '{{ env('PUSHER_APP_KEY') }}',
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
            forceTLS: true,
            encrypted: true,
            authEndpoint: '/broadcasting/auth'
        });

        // Escuchar eventos en tiempo real
        window.Echo.channel('asistencias')
            .listen('NuevaAsistencia', (data) => {
                // Mostrar notificación
                Toastify({
                    text: `Nueva ${data.accion}: ${data.nombre}`,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: data.accion === 'ENTRADA' ? "#28a745" : "#dc3545",
                }).showToast();
                
                // Actualizar contadores
                actualizarContadores();
                
                // Livewire se encarga de actualizar la tabla automáticamente
            });
            
        // Función para actualizar los contadores
        function actualizarContadores() {
            $.ajax({
                url: '/api/contadores',
                method: 'GET',
                success: function(response) {
                    $('#total-registros').text(response.total);
                    $('#entradas-hoy').text(response.entradas_hoy);
                    $('#salidas-hoy').text(response.salidas_hoy);
                }
            });
        }
        
        // Exportar a Excel
        $('#exportar-excel').click(function() {
            $.ajax({
                url: '/api/asistencias/export',
                method: 'GET',
                success: function(data) {
                    const ws = XLSX.utils.json_to_sheet(data);
                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, "Asistencias");
                    XLSX.writeFile(wb, "asistencias.xlsx");
                }
            });
        });
        
        // Recargar datos
        $('#recargar-datos').click(function() {
            Livewire.emit('refreshList');
            actualizarContadores();
        });
        
        // Inicializar al cargar la página
        $(document).ready(function() {
            actualizarContadores();
            
            // Configurar fecha por defecto
            const today = new Date().toISOString().split('T')[0];
            $('#fecha').val(today);
        });
    </script>
</body>
</html>