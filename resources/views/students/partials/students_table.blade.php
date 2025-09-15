<table class="min-w-full bg-white border border-gray-200 rounded-lg">
    <thead>
        <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
            <th class="py-3 px-6 text-left">
                <a href="{{ route('students.index', ['sort' => 'nombre', 'direction' => request('sort') == 'nombre' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->except(['sort', 'direction', 'page'])) }}"
                   class="flex items-center space-x-1 font-semibold text-gray-700 hover:text-gray-900 transition-colors" data-sort="nombre">
                    <span>Nombre</span>
                    @if(request('sort') == 'nombre')
                        <span>{{ request('direction') == 'asc' ? '↓' : '↑' }}</span>
                    @endif
                </a>
            </th>
            <th class="py-3 px-6 text-left">
                <a href="{{ route('students.index', ['sort' => 'uid', 'direction' => request('sort') == 'uid' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->except(['sort', 'direction', 'page'])) }}"
                   class="flex items-center space-x-1 font-semibold text-gray-700 hover:text-gray-900 transition-colors" data-sort="uid">
                    <span>UID RFID</span>
                    @if(request('sort') == 'uid')
                        <span>{{ request('direction') == 'asc' ? '↓' : '↑' }}</span>
                    @endif
                </a>
            </th>
            <th class="py-3 px-6 text-left">
                <a href="{{ route('students.index', ['sort' => 'carrera', 'direction' => request('sort') == 'carrera' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->except(['sort', 'direction', 'page'])) }}"
                   class="flex items-center space-x-1 font-semibold text-gray-700 hover:text-gray-900 transition-colors" data-sort="carrera">
                    <span>Carrera</span>
                    @if(request('sort') == 'carrera')
                        <span>{{ request('direction') == 'asc' ? '↓' : '↑' }}</span>
                    @endif
                </a>
            </th>
            <th class="py-3 px-6 text-left">
                <a href="{{ route('students.index', ['sort' => 'año', 'direction' => request('sort') == 'año' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->except(['sort', 'direction', 'page'])) }}"
                   class="flex items-center space-x-1 font-semibold text-gray-700 hover:text-gray-900 transition-colors" data-sort="año">
                    <span>Año</span>
                    @if(request('sort') == 'año')
                        <span>{{ request('direction') == 'asc' ? '↓' : '↑' }}</span>
                    @endif
                </a>
            </th>
            <th class="py-3 px-6 text-left">
                <a href="{{ route('students.index', ['sort' => 'estado', 'direction' => request('sort') == 'estado' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->except(['sort', 'direction', 'page'])) }}"
                   class="flex items-center space-x-1 font-semibold text-gray-700 hover:text-gray-900 transition-colors" data-sort="estado">
                    <span>Estado</span>
                    @if(request('sort') == 'estado')
                        <span>{{ request('direction') == 'asc' ? '↓' : '↑' }}</span>
                    @endif
                </a>
            </th>
            <th class="py-3 px-6 text-center">Acciones</th>
        </tr>
    </thead>
    <tbody class="text-gray-600 text-sm font-light">
        @forelse ($estudiantes as $student)
            <tr class="border-b border-gray-200 hover:bg-gray-100">
                <td class="py-3 px-6 text-left whitespace-nowrap">{{ $student->nombre }} {{ $student->primer_apellido }} {{ $student->segundo_apellido }}</td>
                <td class="py-3 px-6 text-left">{{ $student->uid }}</td>
                <td class="py-3 px-6 text-left">{{ $student->carrera }}</td>
                <td class="py-3 px-6 text-left">{{ $student->año }}</td>
                <td class="py-3 px-6 text-left">
                    <span class="px-2 py-1 font-semibold leading-tight rounded-full {{ $student->estado == 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $student->estado == 1 ? 'Activo' : 'Inactivo' }}
                    </span>
                </td>
                <td class="py-3 px-6 text-center">
                    <a href="{{ route('students.edit', $student->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Editar</a>
                    @if ($student->estado == 1)
                    <form action="{{ route('students.destroy', $student->id) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de que quieres dar de baja a este estudiante? Esto cambiará su estado a inactivo.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Dar de Baja</button>
                    </form>
                    @else
                    <form action="{{ route('students.restore', $student->id) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de que quieres reactivar a este estudiante?');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="text-green-600 hover:text-green-900">Reactivar</button>
                    </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No hay estudiantes registrados.</td>
            </tr>
        @endforelse
    </tbody>
</table>
<div class="p-4 pagination">
    {{ $estudiantes->appends(request()->query())->links() }}
</div>