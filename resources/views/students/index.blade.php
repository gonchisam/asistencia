@extends('layouts.app') {{-- Importante: extiende el layout principal --}}

@section('content')
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">Gestión de Estudiantes</h2>

        @if (session('status'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">¡Éxito!</strong>
                <span class="block sm:inline">{{ session('status') }}</span>
            </div>
        @endif

        <div class="flex justify-between items-center mb-4">
            <a href="{{ route('students.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-150">
                Registrar Nuevo Estudiante
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                <thead>
                    <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">Nombre</th>
                        <th class="py-3 px-6 text-left">UID RFID</th>
                        <th class="py-3 px-6 text-left">Estado</th>
                        <th class="py-3 px-6 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    @forelse ($estudiantes as $student)
                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="py-3 px-6 text-left whitespace-nowrap">{{ $student->nombre }}</td>
                            <td class="py-3 px-6 text-left">{{ $student->uid }}</td>
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
@endsection