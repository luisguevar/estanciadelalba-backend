<?php

namespace App\Observers;

use App\Models\Habitacion;
use App\Models\HabitacionIncidencia;
use Illuminate\Support\Facades\Auth;

class HabitacionObserver
{
    /**
     * Handle the Habitacion "created" event.
     */
    public function created(Habitacion $habitacion): void
    {
        //
    }

    /**
     * Handle the Habitacion "updated" event.
     */
    public function updating(Habitacion $habitacion)
    {
     
        // Solo si nEstado cambia a 2
        if ($habitacion->isDirty('nEstado') && $habitacion->nEstado == 2) {

            // Verificar si ya existe una incidencia con nEstado = 0
            $existeIncidencia = HabitacionIncidencia::where('habitacion_id', $habitacion->id)
                ->where('nEstado', 0)
                ->exists();

            if (!$existeIncidencia) {
                // Crear nueva incidencia
                HabitacionIncidencia::create([
                    'habitacion_id' => $habitacion->id,
                    'cObservaciones' => null,
                    'nEstado' => 0,
                    'usuarioCreacion_id' => Auth::id(),
                ]);
            }
        }
    }

    /**
     * Handle the Habitacion "deleted" event.
     */
    public function deleted(Habitacion $habitacion): void
    {
        //
    }

    /**
     * Handle the Habitacion "restored" event.
     */
    public function restored(Habitacion $habitacion): void
    {
        //
    }

    /**
     * Handle the Habitacion "force deleted" event.
     */
    public function forceDeleted(Habitacion $habitacion): void
    {
        //
    }
}
