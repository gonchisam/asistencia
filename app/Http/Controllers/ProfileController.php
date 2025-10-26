<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel; // <--- 1. AÑADIR ESTE USE
use App\Imports\DocentesImport;      // <--- 2. AÑADIR ESTE USE
use Maatwebsite\Excel\Validators\ValidationException;



class ProfileController extends Controller
{
    /**
     * Muestra el formulario de perfil del usuario.
     * Esta función es la que pasa los datos del usuario a la vista.
     */
    public function edit(Request $request): View
    {
        // 2. OBTÉN LA LISTA DE DOCENTES
        //    Basado en tu migración, el rol es 'docente'
        $docentes = User::where('role', 'docente')->orderBy('name')->get();

        // 3. PASA LA NUEVA VARIABLE A LA VISTA
        return view('profile.edit', [
            'user' => $request->user(),
            'docentes' => $docentes, // <--- Añade esto
        ]);
    }

    /**
     * Actualiza la información del perfil del usuario.
     * Valida los datos y guarda los cambios en la base de datos.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Actualiza la contraseña del usuario.
     * Valida la contraseña actual y la nueva antes de guardarla.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);
    
        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);
    
        return Redirect::route('profile.edit')->with('status', 'password-updated');
    }

    /**
     * Elimina la cuenta del usuario.
     * Requiere que el usuario confirme su contraseña antes de eliminar la cuenta.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $this->authorize('delete-account');
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function showImportForm(): View
    {
        // Esta es la nueva vista que crearemos en el Paso 3
        return view('profile.importar-docentes');
    }

    /**
     * Importa docentes desde un archivo Excel/CSV.
     */
    public function importDocentes(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv,txt'
        ]);

        try {
            Excel::import(new DocentesImport, $request->file('file'));

            // ==== 2. MODIFICAR REDIRECT (en éxito) ====
            // Redirigir de vuelta a la página de importación
            return Redirect::route('profile.showImportForm')
                ->with('status', 'docentes-imported')
                ->with('success-message', 'Docentes importados correctamente.');

        } catch (ValidationException $e) {
            $failuress = $e->failures();
            $errorMessages = [];
            foreach ($failuress as $failure) {
                $errorMessages[] = 'Fila ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }

            // ==== 3. MODIFICAR REDIRECT (en error de validación) ====
            return Redirect::route('profile.showImportForm')
                ->with('status', 'docentes-import-failed')
                ->withErrors(['import' => $errorMessages]);
        } catch (\Exception $e) {
            
            // ==== 4. MODIFICAR REDIRECT (en error general) ====
            return Redirect::route('profile.showImportForm')
                ->with('status', 'docentes-import-failed')
                ->withErrors(['import' => 'Ocurrió un error inesperado: ' . $e->getMessage()]);
        }
    }
}