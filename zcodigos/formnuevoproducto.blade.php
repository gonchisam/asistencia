@extends('vistadashboard')
@section('contenido')
<!-- Sale & Revenue Start -->
<div class="container-fluid pt-4 px-4">

    <div class="row g-4">
        <div class="col-sm-12">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Error:</strong> Corrige los siguientes campos:<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

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
                <h1>Agregar nuevo producto</h1>
            </div>
        </div>

<!--
@csrf es una directiva en Laravel que se utiliza para incluir un token de seguridad CSRF (Cross-Site Request Forgery) dentro de los formularios HTML. 
Cuando se usa la directiva @csrf dentro de un formulario Blade, Laravel genera un campo oculto (<input type="hidden">) con un token único que será verificado al recibir la solicitud en el servidor.
sin ese código el guardado no se activa 
-->

                <form action="<?php echo asset(''); ?>ventas/guardarproducto" method="POST" enctype="multipart/form-data">
                   @csrf
                    <div class="col-sm-12">
                        <div class="bg-light rounded h-100 p-4">
                            <h6 class="mb-4">Complete el formulario</h6>

                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="floatingInput"
                                    placeholder="Nombre del producto" name="nombre" value="{{ old('nombre') }}" required autocomplete="off">
                                <label for="floatingInput">Nombre del producto</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="floatingPassword"
                                    placeholder="Precio" name="precio" value="{{ old('precio') }}" required autocomplete="off">
                                <label for="floatingPassword">Precio</label>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="floatingPassword"
                                    placeholder="Stock" name="stock" value="{{ old('stock') }}" required autocomplete="off">
                                <label for="floatingPassword">Stock</label>
                            </div>

                            <div class="mb-3">
                                <label for="imagen">Subir Imagen</label>
                                <input type="file" name="imagen" class="form-control">
                            </div>
                            
                            <div class="form-floating">
                                <button type="submit" class="btn btn-primary w-100">GUARDAR PRODUCTO</button>
                            </div>
                        </div>
                    </div>
                </form>

    </div>
</div>
<!-- Sale & Revenue End -->
@endsection