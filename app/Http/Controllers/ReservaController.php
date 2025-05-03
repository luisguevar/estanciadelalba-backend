<?php

namespace App\Http\Controllers;

use App\Models\Estancia;
use App\Models\Habitacion;
use App\Models\ReservaWeb;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReservaController extends Controller
{
    public function index(Request $request)
    {
        $query = Estancia::with(['huespedTitular', 'habitaciones'])
            ->whereNotIn('nEstado', [2, 3, 4])->orderBy('created_at', 'desc');

        // Filtro por nombres y apellidos
        if ($request->has('cNombres') && !empty($request->input('cNombres'))) {
            $cNombres = $request->input('cNombres');
            $query->whereHas('huespedTitular', function ($q) use ($cNombres) {
                $q->whereRaw("CONCAT(cNombres, ' ', cApellidos) LIKE ?", ["%$cNombres%"]);
            });
        }



        // Filtro por fechas de check-in y check-out
        if ($request->has('checkIn') && !empty($request->checkIn)) {
            $checkIn = $request->input('checkIn');
            $query->whereDate('dFechaReservaCheckIn', '>=', $checkIn);
        }
        if ($request->has('checkOut') && !empty($request->checkOut)) {
            $checkOut = $request->input('checkOut');
            $query->whereDate('dFechaReservaCheckOut', '<=', $checkOut);
        }
        if ($request->has('cNroReserva') && !empty($request->cNroReserva)) {
            $cNroReserva = $request->input('cNroReserva');
            $query->where('cNroReserva', 'LIKE', "%$cNroReserva%");
        }



        // Obtener los datos y formatear
        $estancias = $query->get()->map(function ($estancia) {
            // Obtener los números de habitación (nNroHabitacion) relacionados con la estancia
            $nNroHabitaciones = $estancia->habitaciones->map(function ($habitacionEstancia) {
                $habitacion = Habitacion::find($habitacionEstancia->habitacion_id);
                return $habitacion ? $habitacion->nNroHabitacion : null;
            })->filter()->join(', ');

            return [
                'id' => $estancia->id,
                'nEstado' => $estancia->nEstado,
                'cNombres' => $estancia->huespedTitular->cNombres ?? null,
                'cApellidos' => $estancia->huespedTitular->cApellidos ?? null,
                'cNroCelular' => $estancia->huespedTitular->cNroCelular ?? null,
                'cNroReserva' => $estancia->cNroReserva,
                'cCorreo' => $estancia->huespedTitular->cCorreo ?? null,
                'cFechaCheckIn' => $estancia->dFechaReservaCheckIn ? Carbon::parse($estancia->dFechaReservaCheckIn)->format('d/m/Y H:i') : null,
                'cFechaCheckOut' => $estancia->dFechaReservaCheckOut ? Carbon::parse($estancia->dFechaReservaCheckOut)->format('d/m/Y H:i') : null,
                'dFechaCheckIn' => $estancia->dFechaReservaCheckIn,
                'dFechaCheckOut' => $estancia->dFechaReservaCheckOut,
                'nAccion' => 1,
                'cHabitaciones' => $nNroHabitaciones,
                'cNroDocumenoTitular' => $estancia->huespedTitular->cNroDocumento ?? null,
                'nTotalNochesReserva' => 0, // Se calcula en front
                'nTotalAPagar' => 0, // Se calcula en front
                'reservaWeb_id' => 0,
                'cCodigoPagoReferencia' => ''
            ];
        });





        // Filtro por número de habitación con LIKE en la colección
        if ($request->has('cNroHabitacion') && !empty($request->cNroHabitacion)) {
            $cNroHabitacion = $request->input('cNroHabitacion');
            $estancias = $estancias->filter(function ($estancia) use ($cNroHabitacion) {
                return stripos($estancia['cHabitaciones'], $cNroHabitacion) !== false;
            });
        }

        // Obtener las reservas web y darles el formato necesario
        $reservasWeb = ReservaWeb::where('nEstado', '<>', 2)->get()->map(function ($reserva) {
            return [
                'id' => $reserva->id,
                'nEstado' => $reserva->nEstado,
                'cNombres' => $reserva->cResNombreTitular ?? null,
                'cApellidos' => $reserva->cResApellidoTitular ?? null,
                'cNroCelular' => $reserva->cResNroCelularTitular ?? null,
                'cNroReserva' => $reserva->cNroReservaWeb ?? '',
                'cCorreo' => $reserva->cResCorreoTitular ?? null,
                'cFechaCheckIn' => $reserva->dResFechaCheckIn ? Carbon::parse($reserva->dResFechaCheckIn)->format('d/m/Y H:i') : null,
                'cFechaCheckOut' => $reserva->dResFechaCheckOut ? Carbon::parse($reserva->dResFechaCheckOut)->format('d/m/Y H:i') : null,
                'dFechaCheckIn' => $reserva->dResFechaCheckIn,
                'dFechaCheckOut' => $reserva->dResFechaCheckOut,
                'nAccion' => 2, // Suponiendo un estado predeterminado
                'cHabitaciones' => $reserva->habitaciones->pluck('nNroHabitacion')->join(', '), // Concatenar los números de habitación
                'cNroDocumenoTitular' => $reserva->cResNroDocumentoTitular ?? null,
                'nTotalNochesReserva' => $reserva->nTotalNoches, // Se calcula en front,
                'nTotalAPagar' => $reserva->nTotalAPagar, // Se calcula en front
                'reservaWeb_id' => $reserva->id,
                'cCodigoPagoReferencia' => $reserva->cCodigoPagoReferencia ?? ''
            ];
        });
        // Combinar estancias y reservas web
        if ($reservasWeb->isNotEmpty() && $estancias->isNotEmpty()) {
            $resultados = $reservasWeb->merge($estancias);
        } else {
            $resultados = $reservasWeb->isNotEmpty() ? $reservasWeb : $estancias;
        }



        return response()->json([
            'success' => true,
            'data' => $resultados->values() // Resetear las claves después del filtro
        ], 200);
    }

    public function calendarData()
    {
        $reservas = Estancia::whereNotIn('nEstado', [2, 3, 4])->get();

        $lstReservasFormateadas = [];
        $fechasCheckIn = [];  // Array para guardar las fechas en las que ya se añadió 'Check-in'

        foreach ($reservas as $estancia) {
            $checkInDate = $estancia->dFechaReservaCheckIn;
            $fechaSoloDia = date('Y-m-d', strtotime($checkInDate)); // Solo día, mes y año

            // Obtener los números de habitación (nNroHabitacion) relacionados con la estancia
            $nNroHabitaciones = $estancia->habitaciones->map(function ($habitacionEstancia) {
                $habitacion = Habitacion::find($habitacionEstancia->habitacion_id);
                return $habitacion ? $habitacion->nNroHabitacion : null;
            })->filter()->join(', ');

            // Verificar si ya se ha agregado 'Check-in' para esta fecha
            if (!in_array($fechaSoloDia, $fechasCheckIn)) {
                // Añadir el evento 'Check-in' solo si no se ha añadido previamente para este día
                $lstReservasFormateadas[] = [
                    'title' => 'Reserva',
                    'start' => $fechaSoloDia // Fecha completa
                ];

                // Marcar esta fecha como ya agregada
                $fechasCheckIn[] = $fechaSoloDia;
            }

            // Añadir el evento con el nombre del huésped y las habitaciones en una nueva línea
            $lstReservasFormateadas[] = [
                'title' => $estancia->huespedTitular->cNombres . ' ' . $estancia->huespedTitular->cApellidos . ',' . "\n" . 'Hab: ' . $nNroHabitaciones,
                'start' => $checkInDate // Fecha completa
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $lstReservasFormateadas
        ], 200);
    }


    public function verificarDisponibilidad(Request $request)
    {
        $checkIn = $request->input('dFechaCheckIn');
        $checkOut = $request->input('dFechaCheckOut');
        $habitacionId = $request->input('habitacion_id'); // Obtener el ID de la habitación

        if (!$habitacionId || !$checkIn || !$checkOut) {
            return response()->json([
                'success' => false,
                'message' => 'Los parámetros habitacion_id, checkIn y checkOut son obligatorios.'
            ], 400);
        }

        // Para reservas
        $habitacionDisponible = Habitacion::where('id', $habitacionId)
            ->whereDoesntHave('estancias', function ($query) use ($checkIn, $checkOut) {
                $query->where('estancias.dFechaReservaCheckIn', '<', $checkOut)
                    ->where('estancias.dFechaReservaCheckOut', '>', $checkIn)
                    ->where('estancias.nEstado', '!=', 3) // Excluir estancias culminadas
                    ->where('estancias.nEstado', '!=', 5) // Excluir estancias canceladas
                    ->where('estancias.nEstado', '!=', 4); // Excluir estancias interrumpidas
            })->exists();


        //Para estancias

        $estanciaDisponible = Habitacion::where('id', $habitacionId)
            ->whereDoesntHave('estancias', function ($query) use ($checkIn, $checkOut) {
                $query->where('estancias.dFechaCheckIn', '<', $checkOut)
                    ->where('estancias.dFechaReservaCheckOut', '>', $checkIn)
                    ->where('estancias.nEstado', '!=', 3) // Excluir estancias culminadas
                    ->where('estancias.nEstado', '!=', 5) // Excluir estancias canceladas
                    ->where('estancias.nEstado', '!=', 4); // Excluir estancias interrumpidas
            })->exists();





        if ($habitacionDisponible &&  $estanciaDisponible) {
            return response()->json([
                'success' => true,
                'bDisponibilidad' => true,
                'message' => 'La habitación está disponible para las fechas seleccionadas.'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'bDisponibilidad' => false,
            'message' => 'La habitación no está disponible para las fechas seleccionadas.'
        ], 200);
    }
}
