<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Periodo: {{ $periodo->nombre }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.periodos.update', $periodo) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('admin.periodos._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>