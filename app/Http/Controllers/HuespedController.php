<?php

namespace App\Http\Controllers;

use App\Models\EstanciaHabitacionHuesped;
use App\Models\Huesped;
use App\Models\Pago;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;

class HuespedController extends Controller
{
    public function index(Request $request)
    {
        $query = Huesped::with('pais');

        if ($request->has('cNombres')) {
            $cNombres = $request->input('cNombres');
            $query->where(function ($q) use ($cNombres) {
                $q->where('cNombres', 'LIKE', "%$cNombres%")
                    ->orWhere('cApellidos', 'LIKE', "%$cNombres%")
                    ->orWhereRaw("CONCAT(cNombres, ' ', cApellidos) LIKE ?", ["%$cNombres%"]);
            });
        }


        if ($request->has('cNroDocumento')) {
            $cNroDocumento = $request->input('cNroDocumento');
            $query->where('cNroDocumento', 'LIKE', "%$cNroDocumento%");
        }

        $huespeds = $query->get()->map(function ($huesped) {
            // Obtener la última estancia del huésped
            $ultimaEstancia = EstanciaHabitacionHuesped::where('huesped_id', $huesped->id)
                ->join('estancia_habitacions', 'estancia_habitacion_huespeds.estanciaHabitacion_id', '=', 'estancia_habitacions.id')
                ->join('estancias', 'estancia_habitacions.estancia_id', '=', 'estancias.id')
                ->whereNotNull('estancias.dFechaCheckIn') // Asegurar que haya un check-in
                ->orderBy('estancias.dFechaCheckIn', 'desc') // Ordenar por el último check-in
                ->select('estancias.dFechaCheckIn') // Seleccionar la fecha de check-in
                ->first();

            return [
                'id' => $huesped->id,
                'cNombres' => $huesped->cNombres,
                'cApellidos' => $huesped->cApellidos,
                'cCorreo' => $huesped->cCorreo,
                'nTipoDocumento' => $huesped->nTipoDocumento,
                'cNroDocumento' => $huesped->cNroDocumento,
                'cNroCelular' => $huesped->cNroCelular,
                'cPaisOrigen' => $huesped->pais->cNombre ?? null,
                'cFechaRegistro' => $huesped->created_at->format('d/m/Y H:i'),
                'cFechaUltimaEstancia' => $ultimaEstancia
                    ? (new DateTime($ultimaEstancia->dFechaCheckIn))->format('d/m/Y H:i')
                    : 'No registra Estancias', // Texto para estancias no registradas
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $huespeds
        ], 200);
    }


    public function show(Request $request)
    {
        $id = $request->query('id'); // Obtener el parámetro 'id' desde la consulta
        $cNroDocumentoTitular = Huesped::where('id', $id)->first()->cNroDocumento;


        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'El parámetro id es requerido'
            ], 400);
        }

        $huesped = Huesped::with('pais')->find($id);

        if (!$huesped) {
            return response()->json([
                'success' => false,
                'message' => 'Huésped no encontrado'
            ], 404);
        }

        $huespedData = [
            'id' => $huesped->id,
            'cNombres' => $huesped->cNombres,
            'cApellidos' => $huesped->cApellidos,
            'cCorreo' => $huesped->cCorreo,
            'nTipoDocumento' => $huesped->nTipoDocumento,
            'cNroDocumento' => $huesped->cNroDocumento,
            'cNroCelular' => $huesped->cNroCelular,
            'pais_id' => $huesped->pais_id ?? 0,
            'cFechaRegistro' => $huesped->created_at->format('d/m/Y H:i'),
        ];

        $lstEstancias = EstanciaHabitacionHuesped::where('huesped_id', $id)
            ->whereHas('estanciaHabitacion.estancia', function ($query) {
                $query->whereNotIn('nEstado', [1, 5]); // Filtra por el nEstado de la estancia
            })
            ->with('estanciaHabitacion.estancia')
            ->get()
            ->map(function ($estanciaHabitacionHuesped) {
                return [
                    'id' => $estanciaHabitacionHuesped->estanciaHabitacion->estancia->id,
                    'cNroReserva' => optional($estanciaHabitacionHuesped->estanciaHabitacion->estancia)->cNroReserva ?? 'Sin Reserva',
                    'cFechaCheckIn' => (new DateTime($estanciaHabitacionHuesped->estanciaHabitacion->estancia->dFechaCheckIn))->format('d/m/Y H:i'),
                    'cFechaCheckOut' => (new DateTime($estanciaHabitacionHuesped->estanciaHabitacion->estancia->dFechaCheckOut))->format('d/m/Y H:i'),
                    'nEstado' => $estanciaHabitacionHuesped->estanciaHabitacion->estancia->nEstado,
                ];
            });


        $lstReservas = EstanciaHabitacionHuesped::where('huesped_id', $id)
            ->whereHas('estanciaHabitacion.estancia', function ($query) {
                $query->whereNotIn('nEstado', [2, 3, 4]); // Filtra por el nEstado de la estancia
            })
            ->with('estanciaHabitacion.estancia')
            ->get()
            ->map(function ($estanciaHabitacionHuesped) {
                return [
                    'id' => $estanciaHabitacionHuesped->estanciaHabitacion->estancia->id,
                    'cNroReserva' => $estanciaHabitacionHuesped->estanciaHabitacion->estancia->cNroReserva,
                    'cFechaReservaCheckIn' => (new DateTime($estanciaHabitacionHuesped->estanciaHabitacion->estancia->dFechaReservaCheckIn))->format('d/m/Y H:i'),
                    'cFechaReservaCheckOut' => (new DateTime($estanciaHabitacionHuesped->estanciaHabitacion->estancia->dFechaReservaCheckOut))->format('d/m/Y H:i'),
                    'nEstado' => $estanciaHabitacionHuesped->estanciaHabitacion->estancia->nEstado,
                ];
            });

        $lstPagos = Pago::where('cNroDocumentoTitular', $cNroDocumentoTitular)->where('nEstado', 1)->get()->map(function ($pago) {
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

        return response()->json([
            'success' => true,
            'data' => $huespedData,
            'lstEstancias' => $lstEstancias,
            'lstReservas' => $lstReservas,
            'lstPagos' => $lstPagos
        ], 200);
    }

    public function registrarHuespedEstancia(Request $request)
    {
        EstanciaHabitacionHuesped::create([
            'huesped_id' => $request->input('huesped_id'),
            'estanciaHabitacion_id' => $request->input('estanciaHabitacion_id'),
            'nEstado' => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Huésped registrado correctamente'
        ], 201);
    }


    public function actualizarHuespedEstancia(Request $request)
    {
        EstanciaHabitacionHuesped::where('huesped_id', $request->input('huesped_id'))
            ->where('estanciaHabitacion_id', $request->input('estanciaHabitacion_id'))
            ->update([
                'nEstado' => 0,
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Registros actualizados correctamente'
        ], 200);
    }



    public function update(Request $request, $id)
    {

        $huesped = Huesped::find($id);

        if (!$huesped) {
            return response()->json([
                'success' => false,
                'message' => 'Huésped no encontrado.'
            ], 404);
        }

        // Verificar si el correo ya existe
        $existingCorreo = Huesped::where('cCorreo', $request->input('cCorreo'))->where('id', '!=', $id)->first();
        if ($existingCorreo) {
            return response()->json([
                'success' => false,
                'message' => 'Correo existente.'
            ], 400);
        }

        // Verificar si el DNI ya existe
        $existingDocumento = Huesped::where('cNroDocumento', $request->input('cNroDocumento'))->where('id', '!=', $id)->first();
        if ($existingDocumento) {
            return response()->json([
                'success' => false,
                'message' => 'Documento existente.'
            ], 400);
        }

        $huesped->cNombres = $request->input('cNombres');
        $huesped->cApellidos = $request->input('cApellidos');
        $huesped->cCorreo = $request->input('cCorreo');
        $huesped->nTipoDocumento = $request->input('nTipoDocumento');
        $huesped->cNroDocumento = $request->input('cNroDocumento');
        $huesped->cNroCelular = $request->input('cNroCelular');
        $huesped->pais_id = $request->input('pais_id');

        $huesped->save();

        return response()->json([
            'success' => true,
            'message' => 'Huésped actualizado correctamente',
            'data' => $huesped
        ], 200);
    }
}
