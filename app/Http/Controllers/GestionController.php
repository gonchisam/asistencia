<?php

namespace App\Http\Controllers;

use App\Models\GestionAcademica;
use App\Models\DiaNoLaborable;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class GestionController extends Controller
{

    public function toggleActiva(Request $request, GestionAcademica $gestione)
    {
        // 1. Validar que se envió la contraseña
        $request->validate([
            'password_confirm' => 'required|string',
        ]);

        // 2. Verificar si la contraseña coincide con la del usuario logueado
        if (!Hash::check($request->password_confirm, $request->user()->password)) {
            throw ValidationException::withMessages([
                'password_confirm' => ['La contraseña proporcionada es incorrecta.'],
            ]);
        }

        // 3. Si la contraseña es correcta, procedemos al cambio
        // Desactivar todas
        GestionAcademica::query()->update(['actual' => false]);
        
        // Activar la seleccionada
        $gestione->update(['actual' => true]);

        return redirect()->route('admin.gestiones.index')
            ->with('success', '¡Gestión activada exitosamente!');
    }
    /**
     * Muestra la lista de todas las gestiones.
     */
    public function index()
    {
        // Ordenamos por fecha de inicio descendente (la más nueva primero)
        $gestiones = GestionAcademica::orderBy('fecha_inicio', 'desc')->get();
        return view('admin.gestiones.index', compact('gestiones'));
    }

    /**
     * Muestra el formulario para crear una nueva gestión.
     */
    public function create()
    {
        return view('admin.gestiones.create');
    }

    /**
     * Guarda la nueva gestión y configura el calendario automáticamente.
     */
    public function store(Request $request)
    {
        // 1. VALIDACIÓN ROBUSTA
        $request->validate([
            'nombre'          => 'required|string|max:255',
            'fecha_inicio'    => 'required|date',
            'fecha_fin'       => 'required|date|after:fecha_inicio', // Fin debe ser después del inicio
            
            // Campos opcionales para configuración rápida del calendario
            'vacacion_inicio' => 'nullable|date|after:fecha_inicio',
            'vacacion_fin'    => 'nullable|date|after:vacacion_inicio|before:fecha_fin',
            'lunes_carnaval'  => 'nullable|date',
            'martes_carnaval' => 'nullable|date',
        ]);

        // 2. LÓGICA DE "GESTIÓN ACTIVA"
        // Si el usuario marcó "actual", desactivamos todas las demás gestiones primero.
        if ($request->has('actual') && $request->actual == '1') {
            GestionAcademica::query()->update(['actual' => false]);
        }

        // 3. CREAR LA GESTIÓN
        $gestion = GestionAcademica::create([
            'nombre'       => $request->nombre,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin'    => $request->fecha_fin,
            'actual'       => $request->has('actual'),
        ]);

        // 4. GENERACIÓN AUTOMÁTICA DE EVENTOS (MAGIA) 🪄
        
        // A) Carnaval
        if ($request->lunes_carnaval && $request->martes_carnaval) {
            $this->crearFeriado($gestion->id, $request->lunes_carnaval, 'Lunes de Carnaval', 'FERIADO');
            $this->crearFeriado($gestion->id, $request->martes_carnaval, 'Martes de Carnaval', 'FERIADO');
        }

        // B) Vacación de Invierno (Rango de fechas)
        if ($request->vacacion_inicio && $request->vacacion_fin) {
            $inicio = Carbon::parse($request->vacacion_inicio);
            $fin    = Carbon::parse($request->vacacion_fin);

            while ($inicio->lte($fin)) {
                // Ignoramos Sábados y Domingos para no llenar la BD innecesariamente
                if (!$inicio->isWeekend()) {
                    $this->crearFeriado($gestion->id, $inicio->format('Y-m-d'), 'Vacación de Invierno', 'VACACION');
                }
                $inicio->addDay();
            }
        }

        return redirect()->route('admin.gestiones.index')
            ->with('success', 'Gestión creada y calendario configurado correctamente.');
    }

    /**
     * Muestra el formulario de edición.
     */
    public function edit(GestionAcademica $gestione)
    {
        return view('admin.gestiones.edit', compact('gestione'));
    }

    /**
     * Actualiza los datos de la gestión.
     */
    public function update(Request $request, GestionAcademica $gestione)
    {
        $request->validate([
            'nombre'       => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after:fecha_inicio',
        ]);

        // Si esta gestión pasa a ser "actual", apagar las otras
        if ($request->has('actual') && $request->actual == '1') {
            GestionAcademica::where('id', '!=', $gestione->id)->update(['actual' => false]);
        }

        $gestione->update([
            'nombre'       => $request->nombre,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin'    => $request->fecha_fin,
            'actual'       => $request->has('actual'),
        ]);

        return redirect()->route('admin.gestiones.index')
            ->with('success', 'Gestión actualizada correctamente.');
    }

    /**
     * Elimina la gestión.
     */
    public function destroy(GestionAcademica $gestione)
    {
        // Protección: No dejar borrar la gestión activa vigente para no romper reportes
        if ($gestione->actual) {
             return back()->with('error', 'No puedes eliminar la gestión que está activa actualmente.');
        }

        $gestione->delete(); // Los feriados vinculados se quedan o se borran según tu migración (revisaremos esto)
        
        return redirect()->route('admin.gestiones.index')
            ->with('success', 'Gestión eliminada correctamente.');
    }

    /**
     * Función auxiliar privada para crear días no laborables más limpio.
     */
    private function crearFeriado($gestionId, $fecha, $descripcion, $tipo)
    {
        DiaNoLaborable::create([
            'fecha'       => $fecha,
            'tipo'        => $tipo,
            'descripcion' => $descripcion,
            'recurrente'  => false,
            'gestion_id'  => $gestionId,
        ]);
    }
}