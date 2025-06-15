<?php

namespace App\Http\Controllers;

use App\Models\Comprobante;
use App\Models\Estancia;
use App\Models\Habitacion;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacturacionController extends Controller
{
    public function BuscarEstancias(Request $request)
    {
        $query = Estancia::with(['huespedTitular', 'habitaciones', 'estanciaHabitaciones', 'pagos'])
            ->orderBy('created_at', 'desc');

        // Filtro por nombres y apellidos
        if ($request->has('cNombres') && !empty($request->input('cNombres'))) {
            $cNombres = $request->input('cNombres');
            $query->whereHas('huespedTitular', function ($q) use ($cNombres) {
                $q->whereRaw("CONCAT(cNombres, ' ', cApellidos) LIKE ?", ["%$cNombres%"]);
            });
        }

        if ($request->has('checkIn') && !empty($request->checkIn)) {
            $checkIn = $request->input('checkIn');
            $query->where(function ($q) use ($checkIn) {
                $q->whereDate('dFechaCheckIn', '>=', $checkIn)
                    ->orWhere(function ($subQ) use ($checkIn) {
                        $subQ->whereNull('dFechaCheckIn')
                            ->whereDate('dFechaReservaCheckIn', '>=', $checkIn);
                    });
            });
        }

        if ($request->has('checkOut') && !empty($request->checkOut)) {
            $checkOut = $request->input('checkOut');
            $query->where(function ($q) use ($checkOut) {
                $q->whereDate('dFechaCheckOut', '<=', $checkOut)
                    ->orWhere(function ($subQ) use ($checkOut) {
                        $subQ->whereNull('dFechaCheckOut')
                            ->whereDate('dFechaReservaCheckOut', '<=', $checkOut);
                    });
            });
        }

        // Obtener los datos y formatear
        $estancias = $query->get()->map(function ($estancia) {

            // Obtener fecha de entrada
            $fechaEntrada = $estancia->dFechaCheckIn
                ? Carbon::parse($estancia->dFechaCheckIn)
                : ($estancia->dFechaReservaCheckIn ? Carbon::parse($estancia->dFechaReservaCheckIn) : null);

            // Obtener fecha de salida
            $fechaSalida = $estancia->dFechaCheckOut
                ? Carbon::parse($estancia->dFechaCheckOut)
                : ($estancia->dFechaReservaCheckOut ? Carbon::parse($estancia->dFechaReservaCheckOut) : null);

            // Obtener habitaciones y calcular detalles adicionales
            $lstHabitaciones = $estancia->estanciaHabitaciones->map(function ($estanciaHabitacion) use ($fechaEntrada, $fechaSalida) {
                $fechaEntradaHabitacion = $fechaEntrada ? Carbon::parse($fechaEntrada) : null;
                $fechaSalidaHabitacion = $fechaSalida ? Carbon::parse($fechaSalida) : null;

                // Calcular el número de noches
                $nNoches = 0;


                $checkInCalculado = Carbon::parse($fechaEntradaHabitacion)->startOfDay();
                $checkOutCalculado = Carbon::parse($fechaSalidaHabitacion)->startOfDay();
                $nNoches = $checkInCalculado->diffInDays($checkOutCalculado);

                if ($nNoches == 0) {
                    $nNoches = 1;
                }

                $habitacion = $estanciaHabitacion->habitacion;
                $cNombreTipo =  $habitacion->tipoHabitacion->cNombreTipo ?? null;
                $cAbreNombreTipo = $cNombreTipo;
                $cNroHabitacion = $estanciaHabitacion->habitacion->nNroHabitacion  ?? null;
                $meses = [
                    'Jan' => 'Ene',
                    'Feb' => 'Feb',
                    'Mar' => 'Mar',
                    'Apr' => 'Abr',
                    'May' => 'May',
                    'Jun' => 'Jun',
                    'Jul' => 'Jul',
                    'Aug' => 'Ago',
                    'Sep' => 'Sep',
                    'Oct' => 'Oct',
                    'Nov' => 'Nov',
                    'Dec' => 'Dic'
                ];

                // Usar el formato en inglés, y luego reemplazar los nombres de los meses
                $cFechaEntrada = $fechaEntradaHabitacion ? strtr($fechaEntradaHabitacion->format('d M y H:i'), $meses) : null;
                $cFechaSalida = $fechaSalidaHabitacion ? strtr($fechaSalidaHabitacion->format('d M y H:i'), $meses) : null;


                if (str_contains($cNombreTipo, 'HABITACIÓN')) {
                    $cAbreNombreTipo = str_replace('HABITACIÓN', 'HAB', $cNombreTipo);
                }

                $cDescripcion =  $cAbreNombreTipo . " " . $cNroHabitacion . ", " . $nNoches . " NOC del " .   $cFechaEntrada . " hasta " .   $cFechaSalida . ".";

                $nPrecio = $estanciaHabitacion->nPrecioEstanciaHabitacion;
                // Filtrar solo las habitaciones con estado 1
                if ($estanciaHabitacion->nEstado == 1) {
                    return [
                        'cNroHabitacion' => $cNroHabitacion,
                        'cFechaEntrada' => $fechaEntradaHabitacion ? $fechaEntradaHabitacion->format('d/m/Y H:i') : null,
                        'cFechaSalida' =>   $fechaSalidaHabitacion ? $fechaSalidaHabitacion->format('d/m/Y H:i') : null,
                        'cNroNoches' => $nNoches,
                        'cSubTotal' => $estanciaHabitacion->nPrecioEstanciaHabitacion ?? 0,
                        'nValorUnitario' => round($nPrecio * 0.82),
                        'nPrecioUnitario' => $nPrecio,
                        'nTotal' => $nPrecio,
                        'cNombreTipoHabitacion' => $cNombreTipo,
                        'cDescripcion' => $cDescripcion,
                        'nCantidad' => 1,
                        'cUnidadMedida' => "ZZ"

                    ];
                }

                return null;
            })->filter();

            // Obtener documento del huésped titular
            $cNroDocumento = $estancia->huespedTitular->cNroDocumento ?? null;

            // Calcular total a pagar (suma de nPrecioEstanciaHabitacion de estanciaHabitaciones con nEstado = 1)
            $nTotalAPagar = $estancia->estanciaHabitaciones
                ->where('nEstado', 1)
                ->sum('nPrecioEstanciaHabitacion');

            // Calcular total pagado (suma de nMontoPagado de pagos con nEstado = 1)
            $nTotalPagado = $estancia->pagos
                ->where('nEstado', 1)
                ->sum('nMontoPagado');

            // Obtener habitaciones (nNroHabitacion)
            $nNroHabitaciones = $estancia->estanciaHabitaciones
                ->filter(function ($eh) {
                    return $eh->nEstado == 1 && $eh->habitacion;
                })
                ->map(function ($eh) {
                    return $eh->habitacion->nNroHabitacion;
                })
                ->filter()
                ->join(', ');


            return [
                'cNroDocumento' => $cNroDocumento,
                'cNombreTitularCompleto' => $estancia->huespedTitular
                    ? $estancia->huespedTitular->cNombres . ' ' . $estancia->huespedTitular->cApellidos
                    : null,
                'cCorreo' => $estancia->huespedTitular->cCorreo ?? null,
                'cFechaEntrada' => $fechaEntrada ? $fechaEntrada->format('d/m/Y H:i') : '',
                'cFechaSalida' => $fechaSalida ? $fechaSalida->format('d/m/Y H:i') : '',
                'lstHabitaciones' => $lstHabitaciones->values(),

                'nEstado' => $estancia->nEstado,
                'nTotalAPagar' => $nTotalAPagar,
                'nTotalPagado' => $nTotalPagado,
                'cHabitaciones' =>  $nNroHabitaciones,


            ];
        });

        return response()->json([
            'success' => true,
            'data' => $estancias->values()
        ], 200);
    }

    public function GenerarComprobante(Request $request)
    {
        try {



            $nTipoComprobante = (int)$request->input('nTipoComprobante');

            $ultimoComprobante = Comprobante::orderByDesc('created_at')->where('nTipoComprobante', $nTipoComprobante)->first();

            $nNumero = optional($ultimoComprobante)->nNumero ?? 0;
            if ($nNumero == 0) {
                $nNumero = 1;
            } else {
                $nNumero = $nNumero + 1;
            }
            $cSerie = (string)$request->input('cSerie');
            $nTipoDocumento = $request->input('nTipoDocumento');
            $cClienteNroDocumento = (string)$request->input('cClienteNroDocumento');
            $cClienteNombre = (string)$request->input('cClienteNombre');
            $cDireccion = (string)$request->input('cDireccion');
            $cCorreo = (string)$request->input('cCorreo');
            $cTotalGravada = (string)$request->input('cTotalGravada');
            $cTotalIgv = (string)$request->input('cTotalIgv');
            $cTotal  = (string)$request->input('cTotal');
            $usuarioResponsable_id = $request->input('usuarioResponsable_id');

            $items = [];

            // Verificar si 'lstHabitacion' existe y es un array no vacío
            $habitaciones = $request->input('lstHabitacion');  // Usar 'lstHabitacion' con H mayúscula
            if (is_array($habitaciones) && !empty($habitaciones)) {




                foreach ($habitaciones as $habitacion) {

                    $nTotal = (int)$habitacion['cSubTotal'];
                    $igv = 18;
                    $valor_unitario  =  $nTotal / (1 + ($igv / 100)); //sin igv
                    $valor_igv = $valor_unitario * ($igv / 100);
                    $precio_unitario =  $nTotal; // con igv
                    $total = $nTotal;

                    $items[] = [
                        "unidad_de_medida" => strval($habitacion['cUnidadMedida']), // strval("NIU"),
                        "codigo" => strval($habitacion['cNroHabitacion']),
                        "codigo_producto_sunat" => strval("90111800"),
                        "descripcion" => strval($habitacion['cDescripcion']),
                        "cantidad" => $habitacion['nCantidad'],
                        "valor_unitario" => $valor_unitario,
                        "precio_unitario" => $precio_unitario,
                        "subtotal" =>  $valor_unitario,
                        "tipo_de_igv" => 1,
                        "igv" => $valor_igv,
                        "total" =>  $total,
                        "anticipo_regularizacion" => strval("false")
                    ];
                }
            } else {
                // Maneja el caso donde 'lstHabitacion' no es un array o está vacío
                // Puede ser un log, un mensaje de error, o un valor predeterminado.
                $items = [];

                return response()->json([
                    'error' => true,
                    'mensaje' => "no se proporcionó items",
                ], 500);
            }



            $data = [
                "operacion" => "generar_comprobante",
                "tipo_de_comprobante" =>   $nTipoComprobante, // "1",
                "serie" => $cSerie, //"F001",
                "numero" => $nNumero,
                "sunat_transaction" => "1", //VENTA INTERNA
                "cliente_tipo_de_documento" =>  $nTipoDocumento, //1 dni, 6 ruc
                "cliente_numero_de_documento" =>  $cClienteNroDocumento, //20983838383
                "cliente_denominacion" => $cClienteNombre, //JUAN PEREZ SAC
                "cliente_direccion" => $cDireccion, //"CALLE LIBERTAD 116 MIRAFLORES - LIMA - PERU",
                "cliente_email" =>  $cCorreo, //"tucliente@gmail.com",
                "cliente_email_1" => "",
                "cliente_email_2" => "",
                "fecha_de_emision" => now()->format('d-m-Y'),
                "fecha_de_vencimiento" => "",
                "moneda" => "1",
                "porcentaje_de_igv" => 18.00,
                "total_gravada" =>  $cTotalGravada, // "600",
                "total_igv" => $cTotalIgv, // "108",
                "total" => $cTotal, //"708",
                "detraccion" => "false",
                "observaciones" => "",
                "enviar_automaticamente_a_la_sunat" => "true",
                "enviar_automaticamente_al_cliente" => "false",
                "items" => $items
            ];

            /*  return response()->json([
                'error' => true,
                'mensaje' => $data,
            ], 500); */
            $client = new Client();

            $URL_NUVEFACT  = config('app.url_nuvefact');
            $TOKEN_NUVEFACT = config('app.token_nuvefact');



            $url =   $URL_NUVEFACT;
            $token =  $TOKEN_NUVEFACT;

            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Token token="' . $token . '"',
                    'Content-Type'  => 'application/json',
                ],
                'body' => json_encode($data),
                'verify' => false
            ]);

            $responseBody = json_decode($response->getBody()->getContents(), true);



            DB::beginTransaction();
            try {
                $comprobante = Comprobante::create([

                    'nTipoComprobante' => $nTipoComprobante,
                    'nTipoDocumento' => $nTipoDocumento,
                    'cClienteNroDocumento' => $cClienteNroDocumento,
                    'cClienteNombre' => $cClienteNombre,
                    'cDireccion' => $cDireccion,
                    'cCorreo' => $cCorreo,
                    'cTotalGravada' => $cTotalGravada,
                    'cTotalIgv' => $cTotalIgv,
                    'cTotal' => $cTotal,
                    'cSerie'                   => $responseBody['serie'],
                    'nNumero'                  => $responseBody['numero'],
                    'cEnlace'                  => $responseBody['enlace'],
                    'bAceptadaPorSunat'      => $responseBody['aceptada_por_sunat'],
                    'cSunatDescripcion'       => $responseBody['sunat_description'],
                    'cSunatNote'              => $responseBody['sunat_note'],
                    'cSunatResponseCode'      => $responseBody['sunat_responsecode'],
                    'cSunatSoapCode'        => $responseBody['sunat_soap_error'],
                    'bAnulado'                 => $responseBody['anulado'],
                    'cCadenaParaCodigoQR'   => $responseBody['cadena_para_codigo_qr'],
                    'cCodigoHash'             => $responseBody['codigo_hash'],
                    'cCodigoBarras'        => $responseBody['codigo_de_barras'],
                    'cKey'                     => $responseBody['key'],
                    'cDigitesValue'            => $responseBody['digest_value'],
                    'cEnlacePDF'          => $responseBody['enlace_del_pdf'],
                    'cEnlaceXML'          => $responseBody['enlace_del_xml'],
                    'cEnlaceCDR'          => $responseBody['enlace_del_cdr'],
                    'usuarioResponsable_id' => $usuarioResponsable_id,
                ]);



                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'error' => true,
                    'mensaje' => $e->getMessage(),
                ], 500);
            }





            return response()->json($responseBody);
        } catch (ClientException $e) {
            // Capturar error 4xx como el 400 Bad Request
            $responseBody = json_decode($e->getResponse()->getBody()->getContents(), true);

            return response()->json([
                'error' => true,
                'codigo' => $responseBody['codigo'] ?? null,
                'mensaje' => $responseBody['errors'] ?? 'Error desconocido al procesar el comprobante',
            ], $e->getResponse()->getStatusCode());
        } catch (\Exception $e) {
            // Errores generales
            return response()->json([
                'error' => true,
                'mensaje' => $e->getMessage(),
            ], 500);
        }
    }

    public function ObtenerComprobante(Request $request)
    {
        try {
            $id = (int)$request->input('id');
            $tipo_de_comprobante = (int)$request->input('tipo_de_comprobante');
            $serie = $request->input('serie');
            $numero = $request->input('numero');

            $data = [
                "operacion" => "consultar_comprobante",
                "tipo_de_comprobante" => $tipo_de_comprobante,
                "serie" => $serie,
                "numero" => $numero,
            ];

            $client = new Client();
            $URL_NUVEFACT  = config('app.url_nuvefact');
            $TOKEN_NUVEFACT = config('app.token_nuvefact');

            $response = $client->request('POST', $URL_NUVEFACT, [
                'headers' => [
                    'Authorization' => 'Token token="' . $TOKEN_NUVEFACT . '"',
                    'Content-Type'  => 'application/json',
                ],
                'body' => json_encode($data),
                'verify' => false
            ]);

            $responseBody = json_decode($response->getBody()->getContents(), true);

            DB::beginTransaction();
            try {
                $comprobante = Comprobante::findOrFail($id);
                $comprobante->bAceptadaPorSunat = $responseBody['aceptada_por_sunat'];
                $comprobante->save();


                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'error' => true,
                    'bAceptadaPorSunat' => $responseBody['aceptada_por_sunat'],
                    'mensaje' => $e->getMessage(),
                ], 500);
            }

            return response()->json([
                'bAceptadaPorSunat' => $responseBody['aceptada_por_sunat'],
            ]);
        } catch (ClientException $e) {
            $responseBody = json_decode($e->getResponse()->getBody()->getContents(), true);

            return response()->json([
                'error' => true,
                'codigo' => $responseBody['codigo'] ?? null,
                'mensaje' => $responseBody['errors'] ?? 'Error desconocido al procesar el comprobante',
            ], $e->getResponse()->getStatusCode());
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => $e->getMessage(),
            ], 500);
        }
    }

    public function AnularComprobante(Request $request)
    {
        try {
            $id = (int)$request->input('id');
            $tipo_de_comprobante = (int)$request->input('tipo_de_comprobante');
            $serie = $request->input('serie');
            $numero = $request->input('numero');
            $motivo = $request->input('motivo');

            $data = [
                "operacion" => "generar_anulacion",
                "tipo_de_comprobante" => $tipo_de_comprobante,
                "serie" => $serie,
                "numero" => $numero,
                "motivo" => $motivo
            ];

            $client = new Client();
            $URL_NUVEFACT  = config('app.url_nuvefact');
            $TOKEN_NUVEFACT = config('app.token_nuvefact');

            $response = $client->request('POST', $URL_NUVEFACT, [
                'headers' => [
                    'Authorization' => 'Token token="' . $TOKEN_NUVEFACT . '"',
                    'Content-Type'  => 'application/json',
                ],
                'body' => json_encode($data),
                'verify' => false
            ]);

            $responseBody = json_decode($response->getBody()->getContents(), true);

            DB::beginTransaction();
            try {
                $comprobante = Comprobante::findOrFail($id);
                $comprobante->bAnulado = true;
                $comprobante->save(); // 


                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'error' => true,
                    'bAnulado' => false,
                    'mensaje' => $e->getMessage(),
                ], 500);
            }

            return response()->json([
                'bAnulado' => true,
            ]);
        } catch (ClientException $e) {
            $responseBody = json_decode($e->getResponse()->getBody()->getContents(), true);

            return response()->json([
                'error' => true,
                'codigo' => $responseBody['codigo'] ?? null,
                'mensaje' => $responseBody['errors'] ?? 'Error desconocido al procesar el comprobante',
            ], $e->getResponse()->getStatusCode());
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => $e->getMessage(),
            ], 500);
        }
    }
}
