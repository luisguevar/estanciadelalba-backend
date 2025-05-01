<?php

namespace App\Http\Controllers;

use App\Models\Estancia;
use App\Models\EstanciaHabitacion;
use App\Models\Pago;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PanelController extends Controller
{
    public function index(Request $request)
    {
        /* RESUMENES */


        // Obtener estancias con nEstado 2 (para calcular pagos pendientes y porcentaje)
        $estanciasEstado2 = Estancia::where('nEstado', 2)->get();
        $totalEstancias = $estanciasEstado2->count();
        $estanciasConPagoPendiente = 0;
        $estanciasCompletamentePagadas = 0;
        $totalPagosPendientes = 0;
        $totalPagosCompletos = 0;

        foreach ($estanciasEstado2 as $estancia) {
            $pagosEstancia = Pago::where('estancia_id', $estancia->id)
                ->where('nEstado', 1)
                ->get();

            $totalEstancia = EstanciaHabitacion::where('estancia_id', $estancia->id)
                ->sum('nPrecioEstanciaHabitacion');

            $totalPagado = $pagosEstancia->sum('nMontoPagado');

            // Si no se ha pagado completamente
            if ($totalPagado < $totalEstancia) {
                $estanciasConPagoPendiente++;
                $totalPagosPendientes += $pagosEstancia->count();
            } else {
                $estanciasCompletamentePagadas++;
                $totalPagosCompletos += $totalPagado;
            }
        }

        // Calcular el porcentaje de pagos pendientes
        $porcentajePendiente = $totalEstancias > 0 ? intval(($estanciasConPagoPendiente / $totalEstancias) * 100) : 0;
        $porcentajePendiente = 100 - $porcentajePendiente;

        // Formatear el total de pagos completos
        $totalPagosCompletos = number_format($totalPagosCompletos, 2, '.', '');

        $fechaInicio = $request->query('dpFechaIni');
        $fechaFin = $request->query('dpFechaFin');

        $query = Pago::where('nEstado', 1);

        if ($fechaInicio && $fechaFin) {
            // Convertir a solo fecha (eliminar hora)
            $fechaInicio = Carbon::parse($fechaInicio)->toDateString(); // 'YYYY-MM-DD'
            $fechaFin = Carbon::parse($fechaFin)->toDateString(); // 'YYYY-MM-DD'

            // Filtrar solo por la fecha sin considerar la hora
            $query->whereDate('dFechaPago', '>=', $fechaInicio)
                ->whereDate('dFechaPago', '<=', $fechaFin);
        }

        $totalIngresos = $query->sum('nMontoPagado');


        //2. CHECK-IN

        $estanciasPorCheckIn = Estancia::where('nEstado', 1)->get();
        $estanciasPorReservas  = Estancia::where('cNroReserva', '<>', null)->get();

        $totalEstanciasPorCheckIn = $estanciasPorCheckIn->count();
        $totalReservas = $estanciasPorReservas->count();
        $porcentajeCheckInPendiente = $totalReservas > 0 ? intval(($totalEstanciasPorCheckIn / $totalReservas) * 100) : 0;
        $porcentajeCheckInPendiente = 100 - $porcentajeCheckInPendiente;


        //3. CHECK-OUT
        $estanciasPorCheckOut = Estancia::where('nEstado', 2)
            ->where(function ($query) {
                $query->whereDate('dFechaReservaCheckOut', '<=', now()); // Fecha pasada o hoy
            })
            ->get();

        $estanciasConCheckOut = Estancia::where('nEstado', [3, 4])->get();



        $totalEstanciasPorCheckOut = $estanciasPorCheckOut->count();

        $totalEstanciasConCheckOut = $estanciasConCheckOut->count();

        $totalAContar =  $totalEstanciasPorCheckOut  +   $totalEstanciasConCheckOut;

        $porcentajeCheckOutPendiente = $totalAContar > 0 ? intval(($totalEstanciasPorCheckOut / $totalAContar) * 100) : 0;
        /* $porcentajeCheckOutPendiente = 100 - $porcentajeCheckOutPendiente; */



        /* GRAFICOS */
        // Logica de Pagos
        /*  $pagos = Pago::where('nEstado', 1)->orderBy('dFechaPago', 'asc')->get(); */
        $queryPago = Pago::where('nEstado', 1);

        if ($fechaInicio && $fechaFin) {
            // Convertir a formato 'YYYY-MM-DD' para ignorar la hora
            $fechaInicio = Carbon::parse($fechaInicio)->toDateString();
            $fechaFin = Carbon::parse($fechaFin)->toDateString();

            // Filtrar por fecha sin considerar la hora
            $queryPago->whereDate('dFechaPago', '>=', $fechaInicio)
                ->whereDate('dFechaPago', '<=', $fechaFin);
        }

        // Obtener y ordenar los pagos por fecha
        $pagos = $queryPago->orderBy('dFechaPago', 'asc')->get();
        //Logica de Reservas

        // $reservas = Estancia::whereIn('nEstado', [1, 5])->orderBy('dFechaReservaCheckIn', 'asc')->get();


        // $reservasPorDia = $reservas->groupBy(function ($fecha) {
        //     return \Carbon\Carbon::parse($fecha->dFechaReservaCheckIn)->toDateString();
        // })->map(function ($item) {
        //     return [
        //         'dFechaReserva' => $item->first()->dFechaReservaCheckIn,
        //         'nNumerReservas' => $item->count()
        //     ];
        // })->values();
        $queryReserva = Estancia::whereIn('nEstado', [1, 5]);

        if ($fechaInicio && $fechaFin) {
            // Convertir a formato 'YYYY-MM-DD'
            $fechaInicio = Carbon::parse($fechaInicio)->toDateString();
            $fechaFin = Carbon::parse($fechaFin)->toDateString();

            // Filtrar por fecha sin considerar la hora
            $queryReserva->whereDate('dFechaReservaCheckIn', '>=', $fechaInicio)
                ->whereDate('dFechaReservaCheckIn', '<=', $fechaFin);
        }

        // Obtener y agrupar las reservas
        $reservas = $queryReserva->orderBy('dFechaReservaCheckIn', 'asc')->get();

        $reservasPorDia = $reservas->groupBy(function ($reserva) {
            return Carbon::parse($reserva->dFechaReservaCheckIn)->toDateString();
        })->map(function ($item) {
            return [
                'dFechaReserva' => $item->first()->dFechaReservaCheckIn,
                'nNumerReservas' => $item->count()
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'pagos' => $pagos,
                'reservas' => $reservasPorDia,
                'resumenIngresos' => [
                    'nEstanciasPagadas' => $estanciasCompletamentePagadas,
                    'nEstanciasPendientesPago' => $estanciasConPagoPendiente,
                    'cPorcentajePendiente' => $porcentajePendiente . '%',
                    'nTotalPagosCompletos' => number_format($totalIngresos, 2, '.', ''),
                    'nTotalIngresos' => number_format($totalIngresos, 2, '.', '') // Ingresos totales de todas las estancias
                ],
                'resumenCheckIn' => [
                    'nTotalReservas' => $totalReservas,
                    'nTotalPendienteCheckIn' => $totalEstanciasPorCheckIn,
                    'cPorcentajeCheckInPendiente' => $porcentajeCheckInPendiente . '%',

                ],
                'resumenCheckOut' => [
                    'nTotalEstanciasConCheckOut' => $totalEstanciasConCheckOut,
                    'nTotalEstanciasPorCheckOut' => $totalEstanciasPorCheckOut,
                    'cPorcentajeCheckOutPendiente' => $porcentajeCheckOutPendiente . '%',

                ]
            ]
        ], 200);
    }
}
