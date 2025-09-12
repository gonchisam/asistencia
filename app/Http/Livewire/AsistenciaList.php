<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Asistencia;

class AsistenciaList extends Component
{
    public $asistencias;

    protected $listeners = [
        'echo:asistencias,NuevaAsistencia' => 'refreshList',
        // Alternativa si usaste broadcastAs():
        // 'echo:asistencias,nueva.asistencia' => 'refreshList'
    ];

    public function mount()
    {
        $this->refreshList();
    }

    public function refreshList($payload = null)
    {
        $this->asistencias = Asistencia::latest()
            ->take(20)
            ->get()
            ->map(function($item) {
                // Formatear fechas si es necesario
                $item->fecha_formateada = \Carbon\Carbon::parse($item->fecha)->format('d/m/Y');
                return $item;
            });
    }

    public function render()
    {
        return view('livewire.asistencia-list');
    }
}