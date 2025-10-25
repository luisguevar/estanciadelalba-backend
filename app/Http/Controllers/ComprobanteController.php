<?php

namespace App\Http\Controllers;

use App\Models\Comprobante;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ComprobanteController extends Controller
{

    public function index(Request $request)
    {
        // Obtener parámetros de la solicitud
        $usuarioResponsable = $request->input('usuarioResponsable', 0);
        $tipoMovimiento = $request->input('tipoMovimiento', 5);
        $cNroDocumento = $request->input('cNroDocumento', null); //del huesped
        $desde = $request->input('desde', null);
        $hasta = $request->input('hasta', null);

        // Convertir tipoMovimiento a texto
        $nTipoComprobante = null;
        if ($tipoMovimiento == 1) {
            $nTipoComprobante = 1;
        } elseif ($tipoMovimiento == 2) {
            $nTipoComprobante = 2;
        } elseif ($tipoMovimiento == 3) {
            $nTipoComprobante = 3;
        } elseif ($tipoMovimiento == 4) {
            $nTipoComprobante = 4;
        }

        // Construir consulta base
        $query = Comprobante::with([
            'usuarioResponsable:id,name,email,dni',
            'detalles'
        ])->orderBy('created_at', 'desc');

        // Aplicar filtro de usuario responsable
        if ($usuarioResponsable != 0) {
            $query->where('usuarioResponsable_id', $usuarioResponsable);
        }

        // Aplicar filtro de tipo de movimiento
        if ($nTipoComprobante > 0) {
            $query->where('nTipoComprobante', $nTipoComprobante);
        }

        if ($cNroDocumento) {
            $query->where('cClienteNroDocumento', $cNroDocumento);
        }
        // Aplicar filtros de fecha
        if ($desde) {
            $query->whereDate('created_at', '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate('created_at', '<=', $hasta);
        }

        // Obtener los registros filtrados

        $registros = $query->get();


        $lstRegistros = $registros->map(function ($registro) {

            $fechaCreacion = $registro->created_at;
            $limiteAnulacion = $fechaCreacion->copy()->addHours(12);
            $ahora = Carbon::now();

            return [
                'id' => $registro->id,
                'nTipoComprobante' => (int) $registro->nTipoComprobante,
                'cSerie' => $registro->cSerie,
                'nNumero' => $registro->nNumero,
                'cEnlace' => $registro->cEnlace,
                'cEnlacePDF' => $registro->cEnlacePDF,
                'cEnlaceXML' => $registro->cEnlaceXML,
                'cEnlaceCDR' => $registro->cEnlaceCDR,
                'bAceptadaPorSunat' => $registro->bAceptadaPorSunat,
                'bAnulado' => $registro->bAnulado,
                'nTipoDocumento' => $registro->nTipoDocumento,
                'cClienteNroDocumento' => $registro->cClienteNroDocumento,
                'cClienteNombre' => $registro->cClienteNombre,
                'cTotalGravada' => $registro->cTotalGravada,
                'cTotalIgv' => $registro->cTotalIgv,
                'cTotal' => $registro->cTotal,
                'cUsuarioResponsable' => '(' . $registro->usuarioResponsable->dni . ') ' . $registro->usuarioResponsable->name,
                'cFechaCreacion' => $registro->created_at->format('d-m-Y H:i'),
                'bDisponibleAnulacion' => $ahora->lt($limiteAnulacion), // true si estamos antes de +12h
                'cDireccion' => $registro->cDireccion,
                'cCorreo' => $registro->cCorreo,
                'lstDetalles' => $registro->detalles->map(function ($d) {
                    return [
                        'nCantidad'      => $d->nCantidad,
                        'cDescripcion'   => $d->cDescripcion,
                        'cNroHabitacion' => $d->cNroHabitacion,
                        'cUnidadMedida'  => $d->cUnidadMedida,
                        'cSubTotal'      => $d->cSubTotal,
                    ];
                }),



            ];
        });


        return response()->json([
            'success' => true,
            'data' => $lstRegistros
        ], 200);
    }
}
