<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto; //Incrustamos el modelo

class ControllerAdmin extends Controller
{
    public function metodoinicio()
    {
        return view('usuarios.usuarios');
    }

    public function metodoventas()
    {
        $productos=Producto::all(); //select * from productos
        //$productos=Producto::where('stock','>=',100)->get();
        return view('ventas.panel',compact('productos'));
    }

    public function nuevoproducto()
    {
        return view('ventas.formnuevoproducto');
    }

    public function nuevoproductobd(Request $request)
    {
        //https://laravel.com/docs/10.x/validation#available-validation-rules
        // Validar los datos del formulario
        // Si se quiere editar los mensajes para todo el sistema se debe editar el archivo
        // vendor\laravel\framework\src\Illuminate\Translation\lang\en\validation.php
        $request->validate([
            'nombre' => 'required|max:100',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
        ], [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.max' => 'El campo nombre no debe ser mayor de 100 caracteres.',
            'precio.required' => 'El campo precio es obligatorio.',
            'precio.numeric' => 'El campo precio debe ser un número.',
            'precio.min' => 'El campo precio debe ser al menos 0.',
            'stock.required' => 'El campo stock es obligatorio.',
            'stock.integer' => 'El campo stock debe ser un número entero.',
            'stock.min' => 'El campo stock debe ser al menos 0.'
        ]);

        /*
        // Crear el nuevo producto
        Producto::create([
            'nombre' => $request->nombre,
            'precio' => $request->precio,
            'stock' => $request->stock
        ]);
        */

        $data = $request->all();

        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $nombreArchivo = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $nombreArchivo);
            $data['imagen'] = 'uploads/' . $nombreArchivo;
        }

        Producto::create($data);

        // Redireccionar con mensaje
        return redirect()->to('/ventas')->with('success', 'Producto creado exitosamente.');
    }


    public function eliminarproductobd($id)
    {
        $producto = Producto::findOrFail($id);

        // Eliminar la imagen del servidor si existe
        if ($producto->imagen && file_exists(public_path($producto->imagen))) {
            unlink(public_path($producto->imagen));
        }
    
        $producto->delete();
        return redirect()->to('/ventas')->with('successdelete', 'Producto eliminado exitosamente.');
    }



    public function editarProducto($id)
    {
        $producto = Producto::findOrFail($id);
        return view('ventas.vistaeditarproducto', compact('producto'));
    }

    public function actualizarProducto(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string',
            'precio' => 'required|numeric',
            'stock' => 'required|integer',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
        ]);

        // Buscar el producto por ID
        $producto = Producto::findOrFail($id);

        //$producto->update($request->all());

        $data = $request->all();

        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($producto->imagen && file_exists(public_path($producto->imagen))) {
                unlink(public_path($producto->imagen));
            }

            $file = $request->file('imagen');
            $nombreArchivo = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $nombreArchivo);
            $data['imagen'] = 'uploads/' . $nombreArchivo;
        }

        $producto->update($data);

        return redirect()->to('/ventas')->with('successedit', 'Producto actualizado correctamente.');
    }
}
