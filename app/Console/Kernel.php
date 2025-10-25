<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        // --- ¡LÍNEA AÑADIDA! ---
        // Ejecuta nuestro comando para procesar faltas cada cinco minutos.
        // En producción, esto requiere configurar un Cron Job en tu servidor
        // para que ejecute "php artisan schedule:run" cada minuto.
        $schedule->command('asistencia:procesar-faltas')->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}