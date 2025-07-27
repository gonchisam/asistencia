<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Estudiantes</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Gestión de Estudiantes</h1>

        @if (session('status'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('status') }}</span>
            </div>
        @endif

        <div class="flex flex-wrap gap-4 mb-6">
            <a href="{{ route('dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-block transition duration-150 ease-in-out">
                Volver a Inicio
            </a>
            <a href="{{ route('students.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-block transition duration-150 ease-in-out">
                Ver Lista de Estudiantes
            </a>
            <a href="{{ route('students.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-block transition duration-150 ease-in-out">
                Registrar Nuevo Estudiante
            </a>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($estudiantes as $student)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $student->nombre }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $student->uid }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if ($student->estado == 1)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Activo</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactivo</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('students.edit', $student->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-4">Editar</a>
                                @if ($student->estado == 1)
                                <form action="{{ route('students.destroy', $student->id) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de que quieres dar de baja a este estudiante? Esto cambiará su estado a inactivo.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Dar de Baja</button>
                                </form>
                                @else
                                <form action="{{ route('students.restore', $student->id) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de que quieres reactivar a este estudiante?');">
                                    @csrf
                                    @method('PATCH') {{-- Usamos PATCH para una acción de actualización parcial --}}
                                    <button type="submit" class="text-green-600 hover:text-green-900">Reactivar</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No hay estudiantes registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4">
                {{ $estudiantes->links() }}
            </div>
        </div>
    </div>
</body>
</html>