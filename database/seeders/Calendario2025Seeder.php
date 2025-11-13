<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Calendario2025Seeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear la Gestión 2025
        $gestionId = DB::table('gestiones_academicas')->insertGetId([
            'nombre' => 'Gestión 2025',
            'fecha_inicio' => '2025-02-03', // Lunes típico de inicio
            'fecha_fin' => '2025-11-30',
            'actual' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $feriados = [
            // Fijos (Recurrentes)
            ['2025-01-01', 'Año Nuevo', 'FERIADO', true],
            ['2025-01-22', 'Día del Est. Plurinacional', 'FERIADO', true],
            ['2025-05-01', 'Día del Trabajo', 'FERIADO', true],
            ['2025-06-21', 'Año Nuevo Aymara', 'FERIADO', true],
            ['2025-08-06', 'Día de la Independencia', 'FERIADO', true],
            ['2025-09-14', 'Efeméride Cochabamba', 'FERIADO', true], // ¡Cochabamba!
            ['2025-11-02', 'Todos Santos', 'FERIADO', true],
            ['2025-12-25', 'Navidad', 'FERIADO', true],
            
            // Móviles para 2025 (No recurrentes porque cambian de fecha)
            ['2025-03-03', 'Carnaval (Lunes)', 'FERIADO', false],
            ['2025-03-04', 'Carnaval (Martes)', 'FERIADO', false],
            ['2025-04-18', 'Viernes Santo', 'FERIADO', false],
            ['2025-06-19', 'Corpus Christi', 'FERIADO', false],
        ];

        foreach ($feriados as $f) {
            DB::table('dias_no_laborables')->insert([
                'fecha' => $f[0],
                'descripcion' => $f[1],
                'tipo' => $f[2],
                'recurrente' => $f[3],
                'gestion_id' => $gestionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // Ejemplo de Vacación de Invierno (Rango)
        // Digamos del 30 de Junio al 11 de Julio
        $inicioInvierno = Carbon::parse('2025-06-30');
        $finInvierno = Carbon::parse('2025-07-11');
        
        while ($inicioInvierno->lte($finInvierno)) {
            if (!$inicioInvierno->isWeekend()) { // Solo insertar días de semana
                 DB::table('dias_no_laborables')->insert([
                    'fecha' => $inicioInvierno->format('Y-m-d'),
                    'descripcion' => 'Vacación de Invierno',
                    'tipo' => 'VACACION',
                    'recurrente' => false,
                    'gestion_id' => $gestionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $inicioInvierno->addDay();
        }
    }
}