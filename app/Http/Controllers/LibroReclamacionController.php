<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\LibroReclamacion;
use App\Mail\ConfirmacionReclamo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\RespuestaReclamo;
use Carbon\Carbon;

class LibroReclamacionController extends Controller
{
    public function index(Request $request)
    {

        $tipoMovimiento = $request->input('tipoMovimiento', -1);
        $cNroDocumento = $request->input('cNroDocumento', null);
        $desde = $request->input('desde', null);
        $hasta = $request->input('hasta', null);


        $query = LibroReclamacion::query();

        

        // Aplicar filtro de tipo de movimiento
        if ($tipoMovimiento != -1 && $tipoMovimiento != 3) {
            $query->where('nEstadoReclamacion', $tipoMovimiento);
        }

        if ($cNroDocumento) {
            $query->where('cNroDocumentoReclamante', $cNroDocumento);
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



            return [

                'id' => $registro->id,
                'cNroHoja' => $registro->cNroHoja,
                'cNroDocumentoReclamante' => $registro->cNroDocumentoReclamante,
                'cNombreReclamante' => $registro->cNombreReclamante,
                'nMontoReclamadoBien' => $registro->nMontoReclamadoBien,
                'cDescripcionBien' => $registro->cDescripcionBien,
                'cFechaRegistro' => $registro->created_at->format('d-m-Y H:i'),
                'nEstadoReclamacion' => (int)$registro->nEstadoReclamacion

            ];
        });


        return response()->json([
            'success' => true,
            'data' => $lstRegistros
        ], 200);
    }

    public function store(Request $request)
    {
        // 1. Validar datos
        $validated = $request->validate([
            'cNombreReclamante' => 'required|string',
            'cDomicilioReclamante' => 'required|string',
            'nTipoDocumentoReclamante' => 'required|in:1,2,3,4',
            'cNroDocumentoReclamante' => 'required|string',
            'cTelefonoReclamante' => 'required|string',
            'cEmailReclamante' => 'required|email',
            'cApoderadoReclamante' => 'nullable|string',
            'nTipoBien' => 'required|in:0,1',
            'nMontoReclamadoBien' => 'required|numeric|min:0',
            'cDescripcionBien' => 'required|string',
            'nTipoDisconformidad' => 'required|in:0,1',
            'cDetalleDisconformidad' => 'required|string',
            'cPedidoDisconformidad' => 'required|string',
            'nMoneda' => 'required|in:0,1',
        ]);

        DB::beginTransaction();

        try {
            // 2. Guardar reclamo
            $reclamo = LibroReclamacion::create($validated);

            // 3. Enviar correo
            Mail::to($reclamo->cEmailReclamante)->cc('administracion@estanciadelalba.com')->send(new ConfirmacionReclamo($reclamo));

            DB::commit();

            // 4. Éxito
            return response()->json([
                'success' => true,
                'message' => 'Reclamo registrado y correo enviado.',
                'data' => $reclamo
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Captura específica para validación
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos.',
                'errors' => $e->errors()
            ], 400);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error en el registro o envío de correo.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $reclamo = LibroReclamacion::find($id);

        if ($reclamo) {
            // Mapea los datos del modelo individualmente
            $data = [
                'id' => $reclamo->id,
                'cNombreReclamante' => $reclamo->cNombreReclamante,
                'cFechaRegistro' => $reclamo->created_at->format('d-m-Y H:i'),
                'cDomicilioReclamante' => $reclamo->cDomicilioReclamante,
                'cTelefonoReclamante' => $reclamo->cTelefonoReclamante,
                'nTipoDocumentoReclamante' => (int)$reclamo->nTipoDocumentoReclamante,
                'cNroDocumentoReclamante' => $reclamo->cNroDocumentoReclamante,
                'cEmailReclamante' => $reclamo->cEmailReclamante,

                'nTipoBien' => $reclamo->nTipoBien,
                'nMontoReclamadoBien' => $reclamo->nMontoReclamadoBien,
                'nTipoDisconformidad' => $reclamo->nTipoDisconformidad,
                'cDescripcionBien' => $reclamo->cDescripcionBien,
                'cDetalleDisconformidad' => $reclamo->cDetalleDisconformidad,
                'cPedidoDisconformidad' => $reclamo->cPedidoDisconformidad,
                'cRespuestaProveedor' => $reclamo->cRespuestaProveedor,
                'cActualizadoPor' => $reclamo->usuarioResponsable
                    ? '(' . $reclamo->usuarioResponsable->dni . ') ' . $reclamo->usuarioResponsable->name . ' - ' . $reclamo->updated_at->format('d-m-Y H:i')
                    : null,
                'nEstadoReclamacion' => (int)$reclamo->nEstadoReclamacion,
                'nMoneda' => $reclamo->nMoneda,

            ];

            return response()->json([
                'success' => true,
                'reclamo' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Reclamo no encontrado.',
            ], 404);
        }
    }


    public function update(Request $request, $id)
    {
        // Buscar el reclamo
        $reclamo = LibroReclamacion::find($id);

        if (!$reclamo) {
            return response()->json([
                'success' => false,
                'message' => 'Reclamo no encontrado.'
            ], 404);
        }

        // Verifica si se envió al menos un campo
        if (!$request->filled('nEstadoReclamacion') && !$request->filled('cRespuestaProveedor')) {
            return response()->json([
                'success' => false,
                'message' => 'Debe enviar al menos nEstadoReclamacion o cRespuestaProveedor.'
            ], 400);
        }

        // Validación manual del estado permitido
        if ($request->filled('nEstadoReclamacion') && !in_array($request->nEstadoReclamacion, [0, 1, 2])) {
            return response()->json([
                'success' => false,
                'message' => 'Estado no permitido.'
            ], 400);
        }

        // Iniciar transacción
        DB::beginTransaction();

        try {
            // Asignar valores si son enviados
            if ($request->filled('nEstadoReclamacion')) {
                $reclamo->nEstadoReclamacion = $request->nEstadoReclamacion;
            }

            if ($request->filled('cRespuestaProveedor')) {
                $reclamo->cRespuestaProveedor = $request->cRespuestaProveedor;
                $reclamo->dFechaRespuestaProveedor = now();
            }

            $reclamo->usuarioResponsable_id = $request->usuarioResponsable_id;
            $reclamo->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Estado del reclamo actualizado correctamente.',
                'reclamo' => $reclamo
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el reclamo.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function pdf($id)
    {
        try {
            $reclamo = LibroReclamacion::find($id);
            if (!$reclamo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reclamo no encontrado.',
                ], 404);
            }
            $pdf = Pdf::loadView('pdf.libro-reclamacion', ['reclamo' => $reclamo]);
            return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="hoja_reclamacion.pdf"');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generando PDF.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function enviarRespuestaPdf(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:libro_reclamacions,id'
        ]);

        try {
            $reclamo = LibroReclamacion::find($request->id);

            if (!$reclamo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reclamo no encontrado.',
                ], 404);
            }

            $reclamo->update([
                'cRespuestaProveedor' => $request->cRespuestaProveedor,
                'nEstadoReclamacion' => $request->nEstadoReclamacion,
                'usuarioResponsable_id' => $request->usuarioResponsable_id
            ]);

            // 1. Generar el PDF en memoria
            $pdf = Pdf::loadView('pdf.libro-reclamacion', ['reclamo' => $reclamo]);
            $pdfContent = $pdf->output();

            // 2. Enviar correo con el PDF adjunto
            Mail::to($reclamo->cEmailReclamante)->cc('administracion@estanciadelalba.com')->send(
                new RespuestaReclamo($reclamo, $pdfContent)
            );

            return response()->json([
                'success' => true,
                'message' => 'Correo enviado correctamente con el PDF adjunto.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en envío de correo o generación del PDF.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
