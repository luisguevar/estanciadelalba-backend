<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Mail\ConfirmarReservaMail;
use App\Mail\PreReservaMail;
use App\Mail\RechazarReservaMail;
use App\Models\Estancia;
use App\Models\EstanciaHabitacion;
use App\Models\EstanciaHabitacionHuesped;
use App\Models\Habitacion;
use App\Models\HabitacionBloqueoReserva;
use App\Models\HabitacionPrecio;
use App\Models\Huesped;
use App\Models\Pago;
use App\Models\ReservaHabitacionWeb;
use App\Models\ReservaWeb;
use App\Models\Temporada;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReservaController extends Controller
{



    public function habitaciones(Request $request)
    {



        // Parsear las fechas de entrada y salida del request y eliminar las horas
        $dpFechaCheckInOc = Carbon::parse($request->input('dpFechaCheckIn'))->toDateString();
        $dpFechaCheckOutOc = Carbon::parse($request->input('dpFechaCheckOut'))->toDateString();


        // Verificar si las fechas no se han pasado, son iguales o si la fecha de check-in es mayor a la de check-out
        if (
            !$dpFechaCheckInOc || !$dpFechaCheckOutOc ||
            $dpFechaCheckInOc === $dpFechaCheckOutOc ||
            Carbon::parse($dpFechaCheckInOc)->greaterThan(Carbon::parse($dpFechaCheckOutOc))
        ) {
            return response()->json([
                'success' => true,
                'lstHabitacionesAgrupadas' => [] // Devolver lista vacía
            ], 200);
        }

        // Obtener las habitaciones ocupadas
        $habitaciones_ocupadas = Habitacion::whereHas('estancias', function ($query) use ($dpFechaCheckInOc, $dpFechaCheckOutOc) {
            $query->whereIn('estancias.nEstado', [1, 2]) // Especificamos la tabla 'estancias'
                ->where(function ($query) use ($dpFechaCheckInOc, $dpFechaCheckOutOc) {
                    $query->where(function ($query) use ($dpFechaCheckInOc, $dpFechaCheckOutOc) {
                        // Verificamos las fechas dentro del rango sin considerar las horas
                        $query->whereDate('estancias.dFechaCheckIn', '<=', $dpFechaCheckOutOc)
                            ->whereDate('estancias.dFechaCheckOut', '>=', $dpFechaCheckInOc);
                    })
                        ->orWhere(function ($query) use ($dpFechaCheckInOc, $dpFechaCheckOutOc) {
                            // Verificamos las fechas de reserva sin considerar las horas
                            $query->whereDate('estancias.dFechaReservaCheckIn', '<=', $dpFechaCheckOutOc)
                                ->whereDate('estancias.dFechaReservaCheckOut', '>=', $dpFechaCheckInOc);
                        })->orWhere(function ($query) use ($dpFechaCheckInOc, $dpFechaCheckOutOc) {
                            // Verificamos las fechas de reserva sin considerar las horas
                            $query->whereDate('estancias.dFechaCheckIn', '<=', $dpFechaCheckOutOc)
                                ->whereDate('estancias.dFechaReservaCheckOut', '>=', $dpFechaCheckInOc);
                        });
                });
        })->pluck('id'); // Extraer solo los IDs de las habitaciones ocupadas

        $habitaciones_bloqueadas = HabitacionBloqueoReserva::activosEnRango($dpFechaCheckInOc, $dpFechaCheckOutOc)
            ->pluck('habitacion_id');


        // Obtener las habitaciones desocupadas excluyendo las ocupadas y las en limpieza o bloqueadas
        $habitacionesDescoupadas = Habitacion::where('nEstado', 1) // Solo habitaciones activas
            ->whereNotIn('id', $habitaciones_ocupadas) // Excluir ocupadas
            ->whereNotIn('id', $habitaciones_bloqueadas)
            ->with([
                'habitacionServicios.servicio',
                'tipoHabitacion.tipoHabitacionCamas',
                'habitacionPrecios.dia',
                'nivel'
            ])
            ->get();

        $dpFechaCheckIn = Carbon::parse($request->input('dpFechaCheckIn'));
        $dpFechaCheckOut = Carbon::parse($request->input('dpFechaCheckOut'));
        $nTotalNoches = $dpFechaCheckOut->diffInDays($dpFechaCheckIn);

        $dpFechaCheckOut = Carbon::parse($request->input('dpFechaCheckOut'))->subDay();

        $dateRange = collect();
        for ($date = $dpFechaCheckIn; $date->lte($dpFechaCheckOut); $date->addDay()) {
            $dateRange->push([
                'date' => $date->toDateString(),
                'dia_id' => $date->dayOfWeekIso,
            ]);
        }
        $URL_GEMA = config('app.api_gema');
        $groupedHabitaciones = $habitacionesDescoupadas->groupBy(function ($habitacion) use ($request) {

            $serviciosKey = $habitacion->habitacionServicios->pluck('servicio_id')->sort()->join(',');
            $precioTotal = $this->ObtenerPrecioHab($habitacion->id, $request->input('dpFechaCheckIn'),  $request->input('dpFechaCheckIn'));
            return $habitacion->tipoHabitacion_id . '-' . $serviciosKey . '-' . $precioTotal;
        })->map(function ($group) use ($nTotalNoches, $request, $URL_GEMA) {
            $tipoHabitacion = $group->first()->tipoHabitacion;
            $nivel = $group->first()->nivel;

            // Calcular el precio total para cada habitación en el grupo
            $groupWithPrice = $group->map(function ($habitacion) use ($request, $URL_GEMA) {

                $precioTotal = $this->ObtenerPrecioHab($habitacion->id, $request->input('dpFechaCheckIn'),  $request->input('dpFechaCheckOut'));

                // Agregar el precio total a cada habitación
                return [
                    'id' => $habitacion->id,
                    'nNroHabitacion' => $habitacion->nNroHabitacion,
                    'nivelHabitacion_id' => $habitacion->nivelHabitacion_id,
                    'cImagen' => $habitacion->url_imagen, // ¡usar el accessor!

                    /*  'cImagen' => $habitacion->cImagen
                        ?  $URL_GEMA . "storage/" . $habitacion->cImagen
                        :  $URL_GEMA . "http://127.0.0.1:8000/storage/habitaciones/default.png", */
                    'nPrecioTotal' => $precioTotal, // Precio total específico para esta habitación
                ];
            });

            // Obtener el primer nPrecioTotal del grupo para la cabecera
            $precioTotalCabecera = $groupWithPrice->first()['nPrecioTotal'];
            $imagenCabecera = $groupWithPrice->first()['cImagen'];
            return [

                'tipoHabitacion_id' => $tipoHabitacion->id,
                'cNombreTipo' => $tipoHabitacion->cNombreTipo,
                'cNombreNivel' => $nivel->cNombreNivel,
                'nNumeroPersonas' => $tipoHabitacion->nAforo,
                'lstCamas' => $tipoHabitacion->tipoHabitacionCamas,
                'nCantidadHabitaciones' => $group->count(),
                'nPrecioTotal' => $precioTotalCabecera, // Colocamos el primer precio total en la cabecera
                'cImagen' => $imagenCabecera,
                'nTotalNoches' => $nTotalNoches,
                'cDescripcionHabitacion' => '',
                'habitacion_servicios' => $group->first()->habitacionServicios->map(function ($servicio) {
                    return [
                        'id' => $servicio->id,
                        'servicio_id' => $servicio->servicio_id,
                        'cNombreServicio' => $servicio->servicio->cNombreServicio,
                    ];
                }),
                'lstHabitaciones' => $groupWithPrice, // Habitaciones con su precio total
            ];
        });

        $groupedHabitaciones = $groupedHabitaciones->values()->map(function ($group, $index) {
            $group['id'] = $index + 1; // Asignar ID incremental al grupo
            return $group;
        });

        return response()->json([
            'success' => true,

            'lstHabitacionesAgrupadas' => $groupedHabitaciones->values()
        ], 200);
    }


    public function ObtenerPrecioHab($habitacionId, $fechaInicio, $fechaFin)
    {
        // Convertir fechas a objetos Carbon y formatearlas para que solo contengan día, mes y año
        $inicio = Carbon::parse($fechaInicio)->format('Y-m-d');
        $fin = Carbon::parse($fechaFin)->format('Y-m-d');

        // Convertir nuevamente las fechas a objetos Carbon para seguir utilizando métodos como diffInDays()
        $inicio = Carbon::parse($inicio);
        $fin = Carbon::parse($fin);

        // Calcular el número total de noches
        $nTotalNoches = $inicio->diffInDays($fin) ?: 1; // Si son el mismo día, es 1 noche

        // Crear un array con los días de la semana correspondientes a las noches de la estancia
        $diasReserva = collect(range(0, $nTotalNoches - 1))
            ->map(fn($i) => $inicio->copy()->addDays($i)->format('N'))
            ->toArray();

        // Inicializar el total a 0
        $total = 0;

        // Iterar sobre las noches
        foreach ($diasReserva as $dia) {
            // 1. Buscar el precio por defecto para la habitación y el día específico
            $habitacionPrecio = HabitacionPrecio::where('habitacion_id', $habitacionId)
                ->where('dia_id', $dia)
                ->first();

            // 2. Buscar si hay una temporada activa para esa noche
            $temporada = Temporada::where('habitacion_id', $habitacionId)
                ->where('dFechaInicio', '<=', $inicio)
                ->where('dFechaFin', '>=', $inicio)
                ->where('nEstado', 1)
                ->orderBy('id', 'desc')
                ->first();

            // 3. Si hay temporada activa, tomar el precio de la temporada
            if ($temporada) {
                $total += $temporada->nPrecio;
            }
            // 4. Si no hay temporada activa, tomar el precio por defecto
            else if ($habitacionPrecio) {
                $total += $habitacionPrecio->nPrecio;
            }

            // Avanzar al siguiente día
            $inicio->addDay();
        }

        return $total;

        // return response()->json([
        //     'success' => true,
        //     'total' => $total,
        // ]);
    }





    private function determinarEstadoEstancia($estancias, $dpFechaCheckIn, $dpFechaCheckOut)
    {
        foreach ($estancias as $estancia) {
            // Verificar si la estancia tiene nEstado = 4 (culminada)
            if (
                $estancia->nEstado == 4 &&
                $this->estaDentroDelRango($dpFechaCheckIn, $dpFechaCheckOut, $estancia->dFechaCheckIn, $estancia->dFechaCheckOut)
            ) {
                return ['estado' => 0, 'estancia_id' => $estancia->id, 'dFechaCheckOut' => null]; // Libre y el ID de la estancia
            }

            // Verificar si la estancia está ocupada (nEstado = 2) con fechas de check-in/out reales
            if (
                $estancia->nEstado == 2 &&
                $estancia->dFechaCheckIn &&
                $estancia->dFechaReservaCheckOut &&
                $this->estaDentroDelRango($dpFechaCheckIn, $dpFechaCheckOut, $estancia->dFechaCheckIn, $estancia->dFechaReservaCheckOut)
            ) {
                return ['estado' => 2, 'estancia_id' => $estancia->id, 'dFechaCheckOut' => $estancia->dFechaReservaCheckOut]; // Ocupada y el ID de la estancia
            }

            // Verificar si la estancia está reservada (nEstado = 1) con fechas de reserva
            if (
                $estancia->nEstado == 1 &&
                !$estancia->dFechaCheckIn &&
                !$estancia->dFechaCheckOut &&
                $estancia->dFechaReservaCheckIn &&
                $estancia->dFechaReservaCheckOut &&
                $this->estaDentroDelRango($dpFechaCheckIn, $dpFechaCheckOut, $estancia->dFechaReservaCheckIn, $estancia->dFechaReservaCheckOut)
            ) {
                return ['estado' => 1, 'estancia_id' => $estancia->id, 'dFechaCheckOut' => $estancia->dFechaReservaCheckOut]; // Reservada y el ID de la estancia
            }
        }

        // Si no hay coincidencias, la habitación está libre
        return ['estado' => 0, 'estancia_id' => null, 'dFechaCheckOut' => null]; // Libre sin estancia
    }

    private function estaDentroDelRango($inicio1, $fin1, $inicio2, $fin2)
    {
        return ($inicio1 <= $fin2) && ($fin1 >= $inicio2);
    }
    private function abreviarNombreTipo($nombreTipo)
    {
        // Reemplazar "HABITACIÓN" o "HABITACION" por "HAB", pero dejando el resto de la cadena intacta
        if (str_contains($nombreTipo, 'HABITACIÓN') || str_contains($nombreTipo, 'HABITACION')) {
            return str_replace(['HABITACIÓN', 'HABITACION'], 'HAB', $nombreTipo);
        }
        return $nombreTipo;
    }



    public function RegistrarReserva(Request $request)
    {
        try {
            // Validación de los datos recibidos
            $validatedData = $request->validate([
                'cResNombreTitular' => 'required|string|max:255',
                'cResApellidoTitular' => 'required|string|max:255',
                'cResCorreoTitular' => 'required|email|max:255',
                'cResNroDocumentoTitular' => 'required|string|max:20',
                'cResNroCelularTitular' => 'required|string|max:15',
                'cResNroCelularReferencia' => 'nullable|string|max:15',
                'nResNroAdultos' => 'required|integer|min:0|max:100',
                'dResFechaCheckIn' => 'required|date',
                'dResFechaCheckOut' => 'required|date|after:dResFechaCheckIn',
                'cResObservaciones' => 'nullable|string',
                'lstHabitaciones' => 'required|array'
            ]);

            // Establecer la hora de Check In a las 4 PM
            $checkIn = \Carbon\Carbon::parse($validatedData['dResFechaCheckIn'])->setTime(14, 0, 0);

            // Establecer la hora de Check Out a las 11 AM
            $checkOut = \Carbon\Carbon::parse($validatedData['dResFechaCheckOut'])->setTime(11, 0, 0);

            // Crear la reserva en la base de datos
            $reserva = ReservaWeb::create([
                'cResNombreTitular' => $validatedData['cResNombreTitular'],
                'cResApellidoTitular' => $validatedData['cResApellidoTitular'],
                'cResCorreoTitular' => $validatedData['cResCorreoTitular'],
                'cResNroDocumentoTitular' => $validatedData['cResNroDocumentoTitular'],
                'cResNroCelularTitular' => $validatedData['cResNroCelularTitular'],
                'cResNroCelularReferencia' => $validatedData['cResNroCelularReferencia'] ?? null,
                'nResNroAdultos' => $validatedData['nResNroAdultos'],
                'nResNroNinos' =>  $request->input('nResNroNinos'),
                'dResFechaCheckIn' => $checkIn,
                'dResFechaCheckOut' => $checkOut,
                'cResObservaciones' => $validatedData['cResObservaciones'] ?? null,
                'nTotalNoches' => $request->input('nTotalNoches'),
                'nTotalAPagar' => $request->input('nTotalAPagar'), // Se calcula en front
                'cCodigoPagoReferencia' => $request->input('cCodigoPagoReferencia')

            ]);

            // Generar el número de reserva
            $cNroReservaWeb = $this->GenerarNroReserva($reserva->id, now());

            // Asignarlo al modelo y guardar
            $reserva->cNroReservaWeb = $cNroReservaWeb;
            $reserva->save();

            $pago = Pago::create([
                'estancia_id' => 0,
                'dFechaPago' => now(),
                'nTipoPago' => 2, //total
                'nMetodoPago' => 3, //culqi
                'nMontoPagado' => $request->input('nTotalAPagar'),
                'nEstado' => 1,
                'cNroDocumentoTitular' => $validatedData['cResNroDocumentoTitular'],
                'cNombreTitular' => $validatedData['cResNroDocumentoTitular'],
                'cCorreoTitular' => $validatedData['cResCorreoTitular'],
                'nEsCulqi' => 1,
                'cCodigoPagoReferencia' => $request->input('cCodigoPagoReferencia')
            ]);

            // Iterar sobre lstHabitaciones y registrar en la tabla reserva_habitacion_webs
            foreach ($request->lstHabitaciones as $habitacion) {
                ReservaHabitacionWeb::create([
                    'reservaWeb_id' => $reserva['id'],
                    'habitacion_id' => $habitacion['id'],
                    'nNroHabitacion' => $habitacion['nNroHabitacion'],
                    'nPrecioTotal' => $habitacion['nPrecioTotal'],
                ]);
            }

            $lstHabitacionesFormat = collect($request->lstHabitaciones)->map(function ($item) {
                // Buscar la habitación en la base de datos
                $habitacion = Habitacion::with(['tipoHabitacion', 'nivel'])->find($item['id']);
                $nPrecioTotal = $item['nPrecioTotal'];

                return [
                    'cNombreTipoHabitacion' => $habitacion->tipoHabitacion->cNombreTipo ?? 'N/A',
                    'cNombreNivel' => $habitacion->nivel->cNombreNivel ?? 'N/A',
                    'nNroHabitacion' => $habitacion->nNroHabitacion,
                    'nPrecioTotal' => $nPrecioTotal,
                    'nAforo' => $habitacion->tipoHabitacion->nAforo ?? 0
                ];
            });


            $dataReserva = [
                'cFechaCheckIn' => $reserva->dResFechaCheckIn->format('d/m/Y'),
                'cFechaCheckOut' => $reserva->dResFechaCheckOut->format('d/m/Y'),
                'cTitular' => "{$reserva->cResNombreTitular} {$reserva->cResApellidoTitular}",
                'cCorreoContacto' => $reserva->cResCorreoTitular,
                'nSubTotal' => round($reserva->nTotalAPagar / 1.18, 2), // Se descuenta el IGV (18%)
                'nSubTotalIGV' => round($reserva->nTotalAPagar - ($reserva->nTotalAPagar / 1.18), 2),
                'nTotal' => round($reserva->nTotalAPagar, 2),
                'cTotalNoches' => $reserva->nTotalNoches . ' ' . ($reserva->nTotalNoches == 1 ? 'noche' : 'noches'),
                'cNroDocumento' => $reserva->cResNroDocumentoTitular
            ];

            Mail::to($validatedData['cResCorreoTitular'])
                ->cc('recepcion@estanciadelalba.com') // con copia
                ->send(new PreReservaMail($validatedData['cResNroDocumentoTitular'], $dataReserva, $lstHabitacionesFormat));

            /*   Mail::to($validatedData['cResCorreoTitular'])->send(new PreReservaMail($validatedData['cResNroDocumentoTitular'], $dataReserva, $lstHabitacionesFormat)); */

            // Respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => 'Reserva web registrada con éxito',
                'reserva' => $reserva
            ], 200);
        } catch (ValidationException $e) {
            // Manejo de errores de validación
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422); // Código 422 para errores de validación

        } catch (Exception $e) {
            // Manejo de otros errores
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al registrar la reserva',
                'error' => $e->getMessage()
            ], 500); // Código 500 para errores del servidor
        }
    }

    public function ListarHabitaciones(Request $request)
    {
        // Obtener la reserva
        $reserva = ReservaWeb::where('id', $request->id)->first();

        // Validar si la reserva existe
        if (!$reserva) {
            return response()->json([
                'success' => false,
                'message' => 'Reserva no encontrada.'
            ], 404);
        }

        // Obtener las habitaciones de la reserva
        $habitaciones = ReservaHabitacionWeb::where('reservaWeb_id', $request->id)->get();

        // Mapear habitaciones
        $lstHabitaciones = $habitaciones->map(function ($habitacion) {
            return [
                'id' => $habitacion->id,
                'nNroHabitacion' => $habitacion->nNroHabitacion,
                'reservaWeb_id' => $habitacion->reservaWeb_id,
                'cEstado' => 'Pendiente',
                'cTipoHabitacion' => optional($habitacion->habitacion->tipoHabitacion)->cNombreTipo,
                'nPrecioTotal' => $habitacion->nPrecioTotal,
                'bAdicional' => false,
                'habitacion_id' => $habitacion->habitacion_id
            ];
        });

        // Obtener los pagos solo si la reserva tiene código de pago
        $lstPagos = [];
        if (!empty($reserva->cCodigoPagoReferencia)) {
            $lstPagos = Pago::where('cCodigoPagoReferencia', $reserva->cCodigoPagoReferencia)
                ->where('nEstado', 1)
                ->get()
                ->map(function ($pago) {
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
        }

        return response()->json([
            'success' => true,
            'lstHabitaciones' => $lstHabitaciones,
            'lstPagos' => $lstPagos,
            'reserva' => $reserva
        ], 200);
    }





    public function ConfirmarReserva(Request $request)
    {
        DB::beginTransaction(); // Iniciar transacción

        try {

            $nEstadoReserva = $request->input('nEstado'); //1 reservada, 5 cancelada

            // Buscar huésped existente por cNroDocumento
            $huesped = Huesped::where('cNroDocumento', $request->input('huespedTitular.cNroDocumento'))->first();

            // Registrar un nuevo huésped si no existe
            if (!$huesped) {
                $huesped = Huesped::create([
                    'cNombres' => $request->input('huespedTitular.cNombres'),
                    'cApellidos' => $request->input('huespedTitular.cApellidos'),
                    'cCorreo' => $request->input('huespedTitular.cCorreo'),
                    'nTipoDocumento' => $request->input('huespedTitular.nTipoDocumento'),
                    'cNroDocumento' => $request->input('huespedTitular.cNroDocumento'),
                    'cNroCelular' => $request->input('huespedTitular.cNroCelular'),
                    'pais_id' => $request->input('huespedTitular.pais_id'),
                ]);
            }

            $reservaWeb = ReservaWeb::find($request->reservaWeb_id);

            // Crear la estancia sin el número de reserva
            $estancia = Estancia::create([
                'huespedTitular_id' => $huesped->id,
                'dFechaCheckIn' => $request->input('dFechaCheckIn'),
                'nTotalHabitaciones' => $request->input('nTotalHabitaciones'),
                'nEstado' => $nEstadoReserva,
                'dFechaReservaCheckIn' => Carbon::parse($request->dFechaReservaCheckIn)->format('Y-m-d H:i:s'),
                'dFechaReservaCheckOut' => Carbon::parse($request->dFechaReservaCheckOut)->format('Y-m-d H:i:s'),

                'cNroReserva' => $reservaWeb->cNroReservaWeb, // Se establece como nulo inicialmente
            ]);



            // Validar el estado para generar el número de reserva
            if ($request->input('nEstado') == 1) {

                /*  $cNroReserva = $this->GenerarNroReserva($estancia->id, $estancia->dFechaReservaCheckIn);
                $estancia->update(['cNroReserva' => $cNroReserva]); */
            } else {
                $estancia->update(['cObservaciones' => 'Reserva web cancelada']);
                /*   $estancia->update(['cNroReserva' => 'CANCELADA']); */

                if ($request->input('usuarioResponsable_id') > 0) {
                    $estancia->update(['usuarioResponsable_id' => $request->input('usuarioResponsable_id')]);
                }
            }





            // Registrar las habitaciones asociadas a la estancia
            $lstEstanciaHabitacion = $request->input('lstEstanciaHabitacion');
            foreach ($lstEstanciaHabitacion as $habitacion) {
                // Validación para asegurar que los campos necesarios no estén vacíos
                if (!empty($habitacion['habitacion_id'])) {
                    $estanciaHabitacion = EstanciaHabitacion::create([
                        'estancia_id' => $estancia->id,
                        'habitacion_id' => $habitacion['habitacion_id'],
                        'nTotalHuespedesHabitacion' => $habitacion['nTotalHuespedesHabitacion'],
                        'nPrecioEstanciaHabitacion' => $habitacion['nPrecioEstanciaHabitacion'],
                        'nEstado' => 1, // Estado fijo (ocupada)
                    ]);

                    // Validación para asegurar que la lista de huéspedes no esté vacía
                    if (!empty($habitacion['lstEstanciaHabitacionHuesped'])) {
                        // Registrar los huéspedes asociados a esta habitación
                        $lstEstanciaHabitacionHuesped = $habitacion['lstEstanciaHabitacionHuesped'];
                        foreach ($lstEstanciaHabitacionHuesped as $huespedHabitacion) {
                            // Validación para asegurar que los campos necesarios no estén vacíos
                            if (!empty($huespedHabitacion['huesped_id'])) {
                                EstanciaHabitacionHuesped::create([
                                    'huesped_id' => $huespedHabitacion['huesped_id'],
                                    'estanciaHabitacion_id' => $estanciaHabitacion->id,
                                    'nEstado' => 1, // Estado fijo (activo)
                                ]);
                            }
                        }
                    }
                }
            }


            $estancia_id =  $estancia->id;
            $cCodigoPagoReferencia =  $request->input('cCodigoPagoReferencia');
            Pago::where('cCodigoPagoReferencia', $cCodigoPagoReferencia)->update(['estancia_id' => $estancia_id]);




            if (!$reservaWeb) {
                return response()->json(['success' => false, 'message' => 'Reserva no encontrada'], 404);
            }

            $dataReserva = [
                'cFechaCheckIn' => $reservaWeb->dResFechaCheckIn ? $reservaWeb->dResFechaCheckIn->format('d/m/Y') : 'N/A',
                'cFechaCheckOut' => $reservaWeb->dResFechaCheckOut ? $reservaWeb->dResFechaCheckOut->format('d/m/Y') : 'N/A',

                'cTitular' => trim(($reservaWeb->cResNombreTitular ?? '') . ' ' . ($reservaWeb->cResApellidoTitular ?? '')),
                'cCorreoContacto' => $reservaWeb->cResCorreoTitular ?? 'N/A',
                'nSubTotal' => round(($reservaWeb->nTotalAPagar ?? 0) / 1.18, 2),
                'nSubTotalIGV' => round(($reservaWeb->nTotalAPagar ?? 0) - (($reservaWeb->nTotalAPagar ?? 0) / 1.18), 2),
                'nTotal' => round($reservaWeb->nTotalAPagar ?? 0, 2),
                'cTotalNoches' => ($reservaWeb->nTotalNoches ?? 0) . ' ' . (($reservaWeb->nTotalNoches ?? 0) == 1 ? 'noche' : 'noches'),
                'cNroDocumento' => $reservaWeb->cResNroDocumentoTitular ?? 'N/A'
            ];

            $lstHabitaciones = EstanciaHabitacion::where('estancia_id', $estancia_id)->get();

            $lstHabitacionesFormat = $lstHabitaciones->map(function ($item) {
                $habitacion = Habitacion::with(['tipoHabitacion', 'nivel'])->find($item['habitacion_id']);

                return [
                    'cNombreTipoHabitacion' => optional($habitacion->tipoHabitacion)->cNombreTipo ?? 'N/A',
                    'cNombreNivel' => optional($habitacion->nivel)->cNombreNivel ?? 'N/A',
                    'nNroHabitacion' => $habitacion->nNroHabitacion ?? 'N/A',
                    /* 'nPrecioTotal' => $item['nPrecioEstancia'] ?? 0, */
                    'nAforo' => optional($habitacion->tipoHabitacion)->nAforo ?? 0
                ];
            });


            $reservaWeb->nEstado = 2;
            $reservaWeb->save(); // Guardar los cambios en la BD
            if ($request->input('nEstado') === 5) {
                // Enviar el correo con copia
                Mail::to($dataReserva['cCorreoContacto'])
                    ->cc('recepcion@estanciadelalba.com') // copia
                    // ->bcc('otrocorreo@ejemplo.com') // copia oculta
                    ->send(new RechazarReservaMail($dataReserva['cNroDocumento'], $dataReserva, $lstHabitacionesFormat));
            } else {
                // Enviar el correo con copia
                Mail::to($dataReserva['cCorreoContacto'])
                    ->cc('recepcion@estanciadelalba.com') // copia
                    // ->bcc('otrocorreo@ejemplo.com') // copia oculta
                    ->send(new ConfirmarReservaMail($dataReserva['cNroDocumento'], $dataReserva, $lstHabitacionesFormat));
            }


            // Confirmar transacción si todo está bien
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reserrva registrada con éxito',
                'reservaWeb' => $reservaWeb,
                'dataReserva' => $dataReserva,
                'lstHabitacionesFormat' => $lstHabitacionesFormat
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir la transacción en caso de error

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al registrar la reserva. Intente nuevamente.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    function GenerarNroReserva($idEstancia, $fechaReserva)
    {
        // Obtener el año y mes de la fecha de reserva
        $fecha = new DateTime($fechaReserva);
        $anio = $fecha->format('y'); // Dos últimos dígitos del año (25)
        $mes = $fecha->format('m');  // Mes (01)

        // Construir el prefijo dinámico
        $prefijo = $anio . $mes . 'WEB';

        // Verificar la longitud del ID y completarlo con ceros a la izquierda si es necesario
        $longitudId = strlen($idEstancia);
        if ($longitudId < 7) {
            // Si el ID tiene menos de 7 caracteres, rellenar con ceros a la izquierda
            $idStr = str_pad($idEstancia, 7, '0', STR_PAD_LEFT);
        } else {
            // Si el ID tiene 7 o más caracteres, usarlo tal cual
            $idStr = $idEstancia;
        }

        // Concatenar el prefijo con el ID ajustado
        $numeroReserva = $prefijo . $idStr;

        return $numeroReserva;
    }
}
