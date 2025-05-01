<?php

namespace App\Http\Controllers;

use App\Models\Estancia;
use App\Models\EstanciaHabitacion;
use App\Models\EstanciaHabitacionHuesped;
use App\Models\Habitacion;
use App\Models\Huesped;
use App\Models\Pago;
use App\Models\RegistroIngresosSalidas;
use App\Models\TipoHabitacion;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EstanciaController extends Controller
{
    public function index(Request $request)
    {
        $query = Estancia::with(['huespedTitular', 'habitaciones'])
            ->whereNotIn('nEstado', [1, 5]);

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
            $query->whereDate('dFechaCheckIn', '>=', $checkIn);
        }
        if ($request->has('checkOut') && !empty($request->checkOut)) {
            $checkOut = $request->input('checkOut');
            $query->whereDate('dFechaCheckOut', '<=', $checkOut);
        }

        // Obtener los datos y formatear
        $estancias = $query->get()->map(function ($estancia) {

            if ($estancia->nEstado === 1) {
                $checkOutDate = Carbon::parse($estancia->dFechaReservaCheckOut);
            }

            if ($estancia->nEstado === 2) {
                $checkOutDate = Carbon::parse($estancia->dFechaReservaCheckOut);
            }

            if ($estancia->nEstado === 3) {
                $checkOutDate = Carbon::parse($estancia->dFechaCheckOut);
            }

            if ($estancia->nEstado === 4) {
                $checkOutDate = Carbon::parse($estancia->dFechaCheckOut);
            }

            if ($estancia->nEstado === 5) {
                $checkOutDate = Carbon::parse($estancia->dFechaReservaCheckOut);
            }

            // Obtener los números de habitación (nNroHabitacion) relacionados con la estancia
            $nNroHabitaciones = $estancia->habitaciones->map(function ($habitacionEstancia) {
                $habitacion = Habitacion::find($habitacionEstancia->habitacion_id);
                return $habitacion ? $habitacion->nNroHabitacion : null;
            })->filter()->join(', ');

            return [
                'id' => $estancia->id,
                'cNombres' => $estancia->huespedTitular->cNombres ?? null,
                'cApellidos' => $estancia->huespedTitular->cApellidos ?? null,
                'cNroCelular' => $estancia->huespedTitular->cNroCelular ?? null,
                'cCorreo' => $estancia->huespedTitular->cCorreo ?? null,
                'cFechaCheckIn' => $estancia->dFechaCheckIn ? Carbon::parse($estancia->dFechaCheckIn)->format('d/m/Y H:i') : null,
                'cFechaCheckOut' => $checkOutDate ? Carbon::parse($checkOutDate)->format('d/m/Y H:i') : null,
                'cFechaCheckOutProgramado' => $estancia->dFechaReservaCheckOut ? Carbon::parse($estancia->dFechaReservaCheckOut)->format('d/m/Y H:i') : null,
                'nEstado' => $estancia->nEstado,
                'cHabitaciones' => $nNroHabitaciones,
            ];
        });

        // Filtro por número de habitación con LIKE en la colección
        if ($request->has('cNroHabitacion') && !empty($request->cNroHabitacion)) {
            $cNroHabitacion = $request->input('cNroHabitacion');
            $estancias = $estancias->filter(function ($estancia) use ($cNroHabitacion) {
                return stripos($estancia['cHabitaciones'], $cNroHabitacion) !== false;
            });
        }


        return response()->json([
            'success' => true,
            'data' => $estancias->values() // Resetear las claves después del filtro
        ], 200);
    }

    public function show(Request $request)
    {
        $id = $request->query('id'); // Obtener el parámetro 'id' desde la consulta

        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'El parámetro id es requerido'
            ], 400);
        }

        $estancia = Estancia::findOrFail($id);

        // Calcular el total de noches que estará
        $checkInDate = Carbon::parse($estancia->dFechaCheckIn);

        if ($estancia->nEstado === 1) {
            $checkOutDate = Carbon::parse($estancia->dFechaReservaCheckOut);
        }

        if ($estancia->nEstado === 2) {
            $checkOutDate = Carbon::parse($estancia->dFechaReservaCheckOut);
        }

        if ($estancia->nEstado === 3) {
            $checkOutDate = Carbon::parse($estancia->dFechaCheckOut);
        }

        if ($estancia->nEstado === 4) {
            $checkOutDate = Carbon::parse($estancia->dFechaCheckOut);
        }

        if ($estancia->nEstado === 5) {
            $checkOutDate = Carbon::parse($estancia->dFechaReservaCheckOut);
        }


        $checkInReserva = Carbon::parse($estancia->dFechaReservaCheckIn)->startOfDay();
        $checkOutReserva = Carbon::parse($estancia->dFechaReservaCheckOut)->startOfDay();
        $totalNochesReserva = $checkInReserva->diffInDays($checkOutReserva);


        $totalNoches = $checkInDate->diffInDays($checkOutDate);

        //$huesped = Huesped::where('id', $estancia->huespedTitular_id);

        $estanciaData = [
            'id' => $estancia->id,
            'dFechaCheckIn' => $estancia->dFechaCheckIn,
            'dFechaCheckOut' => $estancia->dFechaCheckOut,
            'dFechaCheckInProgramado' => $estancia->dFechaReservaCheckIn,
            'dFechaCheckOutProgramado' => $estancia->dFechaReservaCheckOut,
            'nTotalNoches' => $totalNoches,
            'nTotalNochesReserva' => $totalNochesReserva,
            'cNombreTitular' => $estancia->huespedTitular->cNombres . ' ' . $estancia->huespedTitular->cApellidos,
            'cNroDocumenoTitular' => $estancia->huespedTitular->cNroDocumento,
            'cNroCelularTitular' => $estancia->huespedTitular->cNroCelular,
            'cObservaciones' => $estancia->cObservaciones,
            'nEstado' => $estancia->nEstado,
            'cNroReserva' => $estancia->cNroReserva

        ];

        $lstPagos = Pago::where('estancia_id', $estancia->id)->where('nEstado', 1)->get()->map(function ($pago) {
            return [
                'id' => $pago->id,
                'cFechaPago' => $pago->dFechaPago ? Carbon::parse($pago->dFechaPago)->format('d/m/Y H:i') : null,
                'dFechaPago' => $pago->dFechaPago,
                'nTipoPago' => $pago->nTipoPago,
                'nMetodoPago' => $pago->nMetodoPago,
                'nMontoPagado' => $pago->nMontoPagado,
                'nEstado' => $pago->nEstado,
                'cNroDocumentoTitular' => $pago->cNroDocumentoTitular,
                'cNombreTitular' => $pago->cNombreTitular,
                'cCorreoTitular' => $pago->cCorreoTitular,
            ];
        });

        $lstHabitacionEstancia = EstanciaHabitacion::where('estancia_id', $estancia->id)->get()->map(function ($habitacionEstancia) {
            $habitacion = Habitacion::find($habitacionEstancia->habitacion_id);
            $tipoHabitacion = $habitacion ? TipoHabitacion::find($habitacion->tipoHabitacion_id) : null;

            // Buscar en la tabla estancia_habitacion_huespeds
            $lstHuespedes = EstanciaHabitacionHuesped::where('estanciaHabitacion_id', $habitacionEstancia->id)
                ->where('nEstado', 1)
                ->get()
                ->map(function ($estanciaHabitacionHuesped) {
                    return Huesped::find($estanciaHabitacionHuesped->huesped_id);
                });


            return [
                'estanciaHabitacion_id' => $habitacionEstancia->id,
                'habitacion_id' => $habitacionEstancia->habitacion_id,
                'nNroHabitacion' => $habitacion ? $habitacion->nNroHabitacion : null,
                'tipoHabitacion_id' => $habitacion ? $habitacion->tipoHabitacion_id : null,
                'cNombreTipoHabitacion' => $tipoHabitacion ? $tipoHabitacion->cNombreTipo : null,
                'nPrecioEstanciaHabitacion' => $habitacionEstancia->nPrecioEstanciaHabitacion,
                'lstHuespedes' => $lstHuespedes, // Incluir los registros de huespeds
                'nEstado' => $habitacionEstancia->nEstado
            ];
        });
        return response()->json([
            'success' => true,
            'data' => $estanciaData,
            'lstHabitacionEstancia' => $lstHabitacionEstancia,
            'lstPagos' => $lstPagos
        ], 200);
    }

    public function update(Request $request, $id)
    {

        $estancia = Estancia::find($id);

        if (!$estancia) {
            return response()->json([
                'success' => false,
                'message' => 'Estancia no encontrada.'
            ], 404);
        }
        $estancia->nEstado = $request->input('nEstado');
        $estancia->cObservaciones = $request->input('cObservaciones');

        if ($estancia->nEstado == 2) {
            $estancia->dFechaCheckIn = $request->input('dFechaCheckIn');

            //registrar check-in

            $checkIn = [
                'cTipoMovimiento' => "Check-in",
                'dFechaMovimiento' => $request->input('dFechaCheckIn'),
                'cNroHabitacion' => $request->input('cNroHabitacion'),
                'huespedResponsable_id' =>  $request->input('huespedResponsable_id'),
                'nNroHuespedes' => $request->input('nNroHuespedes'),
                'nMontoPagar' => $request->input('nMontoPagar'),
                'nMontoPagado' => $request->input('nMontoPagado'),
                'usuarioResponsable_id' => $request->input('usuarioResponsable_id'),
                'nEstado' => 1,
            ];
            RegistroIngresosSalidas::create($checkIn);
        }
        if ($estancia->nEstado == 3) {
            $estancia->dFechaCheckOut = $request->input('dFechaCheckOut');



            //registrar check-out
            //registrar check-in

            $checkOut = [
                'cTipoMovimiento' => "Check-out",
                'dFechaMovimiento' => $request->input('dFechaCheckOut'),
                'cNroHabitacion' => $request->input('cNroHabitacion'),
                'huespedResponsable_id' =>  $request->input('huespedResponsable_id'),
                'nNroHuespedes' => $request->input('nNroHuespedes'),
                'nMontoPagar' => $request->input('nMontoPagar'),
                'nMontoPagado' => $request->input('nMontoPagado'),
                'usuarioResponsable_id' => $request->input('usuarioResponsable_id'),
                'nEstado' => 1,
            ];
            RegistroIngresosSalidas::create($checkOut);
        }

        if ($estancia->nEstado == 4) {
            $estancia->dFechaCheckOut = now();
        }

        if ($estancia->nEstado == 5 && $request->input('usuarioResponsable_id') > 0) {
            $estancia->usuarioResponsable_id = $request->input('usuarioResponsable_id');
        } //cancelar reserva

        if ($estancia->nEstado == 4 && $request->input('usuarioResponsable_id') > 0) {
            $estancia->usuarioResponsable_id = $request->input('usuarioResponsable_id');
        } // interrumpir estancia


        $estancia->save();


        if ($request->nEstadoHabitacion != 1 && $request->nEstadoHabitacion != null) {
            // Obtener los IDs de las habitaciones asociadas a la estancia
            $habitacionIds = EstanciaHabitacion::where('estancia_id', $id)
                ->where('nEstado', 1) // Filtrar solo las que tienen nEstado = 1
                ->pluck('habitacion_id'); // Extrae solo los IDs de habitaciones

            // Actualizar el estado de todas esas habitaciones
            Habitacion::whereIn('id', $habitacionIds)
                ->update(['nEstado' => $request->nEstadoHabitacion]);
        }


        return response()->json([
            'success' => true,
            'message' => 'Estancia actualizada correctamente',
            'data' => $estancia
        ], 200);
    }

    public function updatePago(Request $request)
    {

        $pago = Pago::find($request->input('id'));

        if (!$pago) {
            return response()->json([
                'success' => false,
                'message' => 'Pago no encontrado.'
            ], 404);
        }

        $pago->nEstado = $request->input('nEstado');
        $pago->save();

        return response()->json([
            'success' => true,
            'message' => 'Pago actualizado correctamente',
            'data' => $pago
        ], 200);
    }

    public function guardarPago(Request $request)
    {
        $pago = new Pago();
        $pago->estancia_id = $request->input('estancia_id');
        $pago->nTipoPago = $request->input('nTipoPago');
        $pago->nMetodoPago = $request->input('nMetodoPago');
        $pago->nMontoPagado = $request->input('nMontoPagado');
        $pago->cNroDocumentoTitular = $request->input('cNroDocumentoTitular');
        $pago->cNombreTitular = $request->input('cNombreTitular');
        $pago->cCorreoTitular = $request->input('cCorreoTitular');
        $pago->dFechaPago = $request->input('dFechaPago');
        $pago->nEstado = 1;
        $pago->save();

        return response()->json([
            'success' => true,
            'message' => 'Pago registrado correctamente',
            'data' => $pago
        ], 200);
    }
}
