<div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Tarjeta RFID</th>
                    <th>Acción</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                </tr>
            </thead>
            <tbody>
                @forelse($asistencias as $registro)
                <tr wire:key="{{ $registro->id }}">
                    <td>{{ $registro->nombre }}</td>
                    <td>
                        <span class="badge badge-light font-monospace">
                            {{ $registro->uid }}
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-{{ $registro->accion == 'ENTRADA' ? 'success' : 'danger' }}">
                            {{ $registro->accion }}
                        </span>
                    </td>
                    <td>{{ $registro->fecha }}</td>
                    <td>{{ $registro->hora }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">No hay registros de asistencia aún</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-3">
        <button wire:click="refreshList" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-sync-alt"></i> Actualizar
        </button>
    </div>
</div>