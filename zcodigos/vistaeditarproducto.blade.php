@extends('vistadashboard')
@section('contenido')
<!-- Sale & Revenue Start -->
<div class="container-fluid pt-4 px-4">


    <div class="row g-4">
        <div class="col-sm-12">
            <a href="<?php echo asset(''); ?>ventas">
                <button type="submit" class="btn btn-warning">VOLVER A LISTA</button>
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-sm-12">
            <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                <h1>Modificar producto</h1>
            </div>
        </div>

        <form action="{{ route('productos.actualizar', $producto->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-control" value="{{ $producto->nombre }}" required>
            </div>

            <div class="mb-3">
                <label for="precio" class="form-label">Precio</label>
                <input type="number" name="precio" class="form-control" value="{{ $producto->precio }}" required step="0.01">
            </div>

            <div class="mb-3">
                <label for="stock" class="form-label">Stock</label>
                <input type="number" name="stock" class="form-control" value="{{ $producto->stock }}" required>
            </div>

            <div class="mb-3">
                <label for="imagen">Actualizar Imagen</label><br>
                @if($producto->imagen)
                    <img src="{{ asset($producto->imagen) }}" alt="Imagen Actual" width="150"><br><br>
                @endif
                <input type="file" name="imagen" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </form>

    </div>
</div>
<!-- Sale & Revenue End -->
@endsection