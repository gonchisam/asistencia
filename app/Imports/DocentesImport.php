<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class DocentesImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Asumimos que tu Excel/CSV tiene las columnas 'name' y 'email'
        // Si el email ya existe, Maatwebsite\Excel lo saltará automáticamente
        // gracias a la regla 'unique' de abajo.

        return new User([
            'name'     => $row['name'],
            'email'    => $row['email'],
            'role'     => 'docente', // Asignamos el rol 'docente' por defecto
            'password' => Hash::make('password'), // ¡Contraseña por defecto!
        ]);
    }

    /**
     * Define las reglas de validación para cada fila.
     */
    public function rules(): array
    {
        // 3. AÑADIMOS LA REGLA DE VALIDACIÓN PARA EL ROL
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            
            // Basado en tu migración, los roles son: superadmin, administrador, docente
            'role' => ['required', 'string', Rule::in(['superadmin', 'administrador', 'docente'])],
        ];
    }

    /**
     * Mensajes de validación personalizados.
     */
    public function customValidationMessages()
    {
        // 4. AÑADIMOS LOS MENSAJES DE ERROR PARA EL ROL
        return [
            'name.required' => 'El nombre es obligatorio en la fila :row.',
            'email.required' => 'El email es obligatorio en la fila :row.',
            'email.email' => 'El email no tiene un formato válido en la fila :row.',
            'email.unique' => 'El email :value ya existe en el sistema (fila :row).',
            
            'role.required' => 'El rol es obligatorio en la fila :row.',
            'role.in' => 'El rol ":value" no es válido (usar: superadmin, administrador, o docente) en la fila :row.',
        ];
    }
}