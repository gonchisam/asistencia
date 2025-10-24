@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Crear Nuevo Periodo
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.periodos.store') }}" method="POST">
                        @csrf
                        {{-- Incluye el formulario CORRECTO para periodos --}}
                        @include('admin.periodos._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection