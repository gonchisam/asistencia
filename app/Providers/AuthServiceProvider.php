<?php

namespace App\Providers;

// Importa Gate y el modelo User
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        /*
        |--------------------------------------------------------------------------
        | Definición de Gates para Roles
        |--------------------------------------------------------------------------
        */

        // Gate para Administradores y SuperAdmin (pueden gestionar estudiantes)
        Gate::define('manage-students', function (User $user) {
            return in_array($user->role, ['superadmin', 'administrador']);
        });

        // Gate para Administradores y SuperAdmin (pueden eliminar cuentas)
        // El rol 'docente' no podrá
        Gate::define('delete-account', function (User $user) {
            return in_array($user->role, ['superadmin', 'administrador']);
        });
    }
}