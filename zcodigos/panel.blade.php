@extends('vistadashboard')
@section('contenido')
<!-- Sale & Revenue Start -->
<div class="container-fluid pt-4 px-4">

    <div class="row g-4">
        <div class="col-sm-12">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('successdelete'))
                <div class="alert alert-success">
                    {{ session('successdelete') }}
                </div>
            @endif

            @if (session('successedit'))
                <div class="alert alert-success">
                    {{ session('successedit') }}
                </div>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-sm-12">
            <a href="<?php echo asset(''); ?>ventas/crearproducto">
                <button type="submit" class="btn btn-success">CREAR PRODUCTO</button>
            </a>
        </div>
    </div>


    <div class="row g-4">
        <div class="col-sm-12">
            <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                <h1>Panel de ventas</h1>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Stock</th>
                            <th scope="col">Precio</th>
                            <th scope="col">Imagen</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $correlativo=1;
                        ?>
                        @foreach ($productos as $producto)
                            <tr>
                                <th scope="row"><?php echo $correlativo; ?></th>
                                <td>{{ $producto->nombre }}</td>
                                <td>{{ $producto->stock }}</td>
                                <td>{{ $producto->precio }}</td>
                                <td>
                                    @if($producto->imagen)
                                        <img src="{{ asset($producto->imagen) }}" alt="Imagen Actual" width="100"><br><br>
                                    @endif
                                </td>
                                <td>
                                    <!-- Botón Eliminar -->
                                    <form action="{{ route('productos.destroy', $producto->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('¿Estás seguro de que deseas eliminar este producto?')">
                                            Eliminar
                                        </button>
                                    </form>

                                    <a href="{{ route('productos.editar', $producto->id) }}" class="btn btn-warning btn-sm">Editar</a>
                                </td>
                            </tr>
                        <?php
                        $correlativo++;
                        ?>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Sale & Revenue End -->
@endsection