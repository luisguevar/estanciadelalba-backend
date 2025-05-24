<?php

namespace App\Http\Controllers;

use App\Models\cambioHabitaciones;
use App\Models\Estancia;
use App\Models\EstanciaHabitacion;
use App\Models\EstanciaHabitacionHuesped;
use App\Models\Habitacion;
use App\Models\HabitacionPrecio;
use App\Models\Huesped;
use App\Models\Pago;
use App\Models\RegistroIngresosSalidas;
use App\Models\ReservaWeb;
use App\Models\Temporada;
use App\Models\TipoHabitacion;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RecepcionController extends Controller
{


    public function index(Request $request)
    {
        // Obtener parámetros de entrada para el rango de fechas y eliminar la hora
        $dpFechaCheckIn = Carbon::parse($request->input('dpFechaCheckIn'))->toDateString();
        $dpFechaCheckOut = Carbon::parse($request->input('dpFechaCheckOut'))->toDateString();

        // Cargar las habitaciones con las relaciones necesarias
        $habitaciones = Habitacion::with(['nivel', 'estancias', 'tipoHabitacion.tipoHabitacionCamas.tipoCama'])->get();

        // Agrupar las habitaciones por nivel
        $niveles = $habitaciones->groupBy('nivelHabitacion_id')->map(function ($habitaciones, $nivelId) use ($dpFechaCheckIn, $dpFechaCheckOut) {
            $nombreNivel = $habitaciones->first()->nivel->cNombreNivel ?? 'Nivel desconocido';

            $lstHabitaciones = $habitaciones->map(function ($habitacion) use ($dpFechaCheckIn, $dpFechaCheckOut) {
                // Determinar el estado de la estancia basado en la lógica proporcionada
                $estancias = $habitacion->estancias;
                $estancia = $this->determinarEstadoEstancia($habitacion->estancias, $dpFechaCheckIn, $dpFechaCheckOut);
                $total = $this->ObtenerPrecioHab($habitacion->id,   $dpFechaCheckIn,  $dpFechaCheckOut);
                $nNumeroCamas = $habitacion->tipoHabitacion->tipoHabitacionCamas->count();
                $nNumeroPersonas = $habitacion->tipoHabitacion->nAforo;
                $nColorHabitacion = $this->determinarColorHabitacion($habitacion, $estancia);
                $infoEstancia = [];
                if ($estancia['estancia_id'] > 0 && (/* $estancia['estado'] == 1 || */$estancia['estado'] == 2)) {
                    $infoEstancia  = $this->ObtenerEstanciaPorId($estancia['estancia_id']);
                }

                $nEstadoHabitacion = $habitacion->nEstado;



                return [
                    'id' => $habitacion->id,
                    'tipoHabitacion_id' => $habitacion->tipoHabitacion_id,
                    'nNroHabitacion' => $habitacion->nNroHabitacion,
                    'cNombreTipo' => $this->abreviarNombreTipo($habitacion->tipoHabitacion->cNombreTipo ?? ''),
                    'cNombreTipoHabitacion' => $habitacion->tipoHabitacion->cNombreTipo ?? '',
                    'nEstadoEstancia' => $estancia['estado'],
                    'estancia_id' => $estancia['estancia_id'],
                    'dFechaCheckOut' => $estancia['dFechaCheckOut'],
                    'nPrecioTotal' => $total,
                    'nNumeroCamas' => $nNumeroCamas,
                    'nNumeroPersonas' => $nNumeroPersonas,
                    'checkInDate' => $dpFechaCheckIn, // Fecha sin hora
                    'checkOutDate' => $dpFechaCheckOut, // Fecha sin hora
                    'nColorHabitacion' => $nColorHabitacion,
                    'infoEstancia' => $infoEstancia,
                    'nEstadoHabitacion' => $nEstadoHabitacion,
                    /*  '$estancias' => $estancias */

                ];
            });

            return [
                'id' => $nivelId,
                'cNombreNivel' => $nombreNivel,
                'lstHabitaciones' => $lstHabitaciones,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'lstNiveles' => $niveles,
        ], 200);
    }


    private function determinarColorHabitacion($habitacion, $estancia)
    {
        /* 
            1 blanca - vacia
            2 amarilla - sucia
            3 verde - reservada
            4 roja - en estancia
            5 azul - bloqueada
            6 amarillo y verde
            7 azul y verde

        */

        if ($habitacion->nEstado == 1 && in_array($estancia['estado'], [3, 4, 5, 0])) {
            return 1; // Blanca - vacía y disponible
        }

        if ($habitacion->nEstado == 2 && $estancia['estado'] == 1) {
            return 6; // Amarilla - sucia y reservada
        }

        if ($habitacion->nEstado == 0 && in_array($estancia['estado'], [1, 2])) {
            return 7; // Azul - bloqueado pero con reserva o estancia activa
        }


        if ($habitacion->nEstado == 2) {
            return 2; // Amarilla - sucia
        }


        if ($habitacion->nEstado == 1 && $estancia['estado'] == 1) {
            return 3; // Verde - reservada y limpia
        }

        if ($estancia['estado'] == 2) {
            return 4; // Roja - en estancia
        }

        if ($habitacion->nEstado == 0) {
            return 5; // Azul - bloqueada
        }



        return 0; // Valor por defecto
    }




    private function determinarEstadoEstancia($estancias, $dpFechaCheckIn, $dpFechaCheckOut)
    {

        //Buscar Estancias con nEstado == 2 //OCUPADO

        $estanciasOcupadas = $estancias->where('nEstado', 2);


        foreach ($estanciasOcupadas as $estancia) {
            if (
                $estancia->nEstado == 2 &&
                $estancia->dFechaCheckIn &&
                $estancia->dFechaReservaCheckOut &&
                $this->estaDentroDelRango($dpFechaCheckIn, $dpFechaCheckOut, $estancia->dFechaCheckIn, $estancia->dFechaReservaCheckOut)
            ) {
                return ['estado' => 2, 'estancia_id' => $estancia->id, 'dFechaCheckOut' => $estancia->dFechaReservaCheckOut]; // Ocupada y el ID de la estancia
            }
        }

        //Buscar Estancias con nEstado == 1 //RESERVA
        $estanciasReservadas = $estancias->where('nEstado', 1);

        foreach ($estanciasReservadas as $estancia) {
            if (

                !$estancia->dFechaCheckIn &&
                !$estancia->dFechaCheckOut &&
                $estancia->dFechaReservaCheckIn &&
                $estancia->dFechaReservaCheckOut &&
                (
                    $this->estaDentroDelRango($dpFechaCheckIn, $dpFechaCheckOut, $estancia->dFechaReservaCheckIn, $estancia->dFechaReservaCheckOut) ||
                    Carbon::parse($dpFechaCheckOut)->toDateString() == Carbon::parse($estancia->dFechaReservaCheckIn)->toDateString()

                )
            ) {
                return ['estado' => 1, 'estancia_id' => $estancia->id, 'dFechaCheckOut' => $estancia->dFechaReservaCheckOut]; // Reservada y el ID de la estancia
            }
        }

        // Si no hay coincidencias, la habitación está libre
        return ['estado' => 0, 'estancia_id' => null, 'dFechaCheckOut' => null]; // Libre sin estancia

        /*  foreach ($estancias as $estancia) {



            // Verificar si la estancia tiene nEstado = 4 (culminada)
            if (
                $estancia->nEstado == 4 &&
                $this->estaDentroDelRango($dpFechaCheckIn, $dpFechaCheckOut, $estancia->dFechaCheckIn, $estancia->dFechaCheckOut)
            ) {
                return ['estado' => 0, 'estancia_id' => $estancia->id, 'dFechaCheckOut' => null]; // Libre y el ID de la estancia
            }
        } */
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

    public function BuscadorHuesped(Request $request)
    {
        $huesped = Huesped::where('cNroDocumento', $request->query('cNroDocumento'))->first();

        return response()->json([
            'success' => true,
            'huesped' => $huesped ?? [],
        ], 200);
    }

    public function ValidadorHuesped(Request $request)
    {
        // Validar solo los campos necesarios
        $validated = $request->validate([
            'cNroDocumento' => 'required|string|max:20',
            'cNombres' => 'required|string|max:255',
            'cApellidos' => 'required|string|max:255',
        ]);

        // Buscar huésped existente por cNroDocumento
        $huesped = Huesped::where('cNroDocumento', $validated['cNroDocumento'])->first();

        // Registrar un nuevo huésped si no existe
        if (!$huesped) {
            $huesped = Huesped::create([
                'cNombres' => $validated['cNombres'],
                'cApellidos' => $validated['cApellidos'],
                'cCorreo' => $request->input('cCorreo'),
                'nTipoDocumento' => $request->input('nTipoDocumento', 1),
                'cNroDocumento' => $validated['cNroDocumento'],
                'cNroCelular' => $request->input('cNroCelular'),
                'pais_id' => $request->input('pais_id', 1),
            ]);
        }

        return response()->json([
            'success' => true,
            'huesped' => $huesped,
        ], 200);
    }



    public function BuscarHabitacion(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:habitacions,id',
            'dpFechaCheckIn' => 'required|date',
            'dpFechaCheckOut' => 'required|date|after_or_equal:dpFechaCheckIn',
        ]);

        $habitacion = Habitacion::with('tipoHabitacion')->findOrFail($validated['id']);
        $checkIn = Carbon::parse($validated['dpFechaCheckIn']);
        $checkOut = Carbon::parse($validated['dpFechaCheckOut']);

        $checkInDate = $checkIn->copy()->startOfDay();
        $checkOutDate = $checkOut->copy()->startOfDay();
        $nTotalNoches = $checkInDate->diffInDays($checkOutDate);
        $nTotalNoches = $nTotalNoches > 0 ? $nTotalNoches : 1;

        $estancias = EstanciaHabitacion::where('habitacion_id', $validated['id'])
            ->where('nEstado', 1)
            ->whereHas('estancia', function ($query) {
                $query->whereIn('nEstado', [1, 2]);
            })
            ->with('estancia')
            ->get()
            ->pluck('estancia')
            ->unique('id')
            ->map(function ($estancia) {
                return [
                    'id' => $estancia->id,
                    'dFechaCheckIn' => $estancia->dFechaCheckIn ?? $estancia->dFechaReservaCheckIn,
                    'dFechaCheckOut' => $estancia->dFechaCheckOut ?? $estancia->dFechaReservaCheckOut,
                ];
            })
            ->values(); // reindexar






        $total = $this->ObtenerPrecioHab($habitacion->id, $validated['dpFechaCheckIn'], $validated['dpFechaCheckOut']);

        return response()->json([
            'success' => true,
            'habitacion' => [
                'habitacion_id' => $habitacion->id,
                'nNroHabitacion' => $habitacion->nNroHabitacion,
                'tipoHabitacion_id' => $habitacion->tipo_habitacion_id,
                'cNombreTipoHabitacion' => $habitacion->tipoHabitacion->cNombreTipo ?? '',
                'nPrecioEstanciaHabitacion' => $total,
                'nTotalNoches' => $nTotalNoches,
                'lstHorariosOcupados' => $estancias
            ],
            'total' => $total,
        ]);
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







    public function RegistrarIngreso(Request $request)
    {
        // Validaciones
        $validator = Validator::make($request->all(), [
            'huespedTitular.cNroDocumento' => 'required',
            'huespedTitular.cNombres' => 'required|string|max:255',
            'huespedTitular.cApellidos' => 'required|string|max:255',
            'huespedTitular.cCorreo' => 'required|email|max:255',

            'dFechaReservaCheckOut' => 'required|date|after:dFechaCheckIn',
            'nTotalHabitaciones' => 'required|integer|min:1',
            'lstEstanciaHabitacion' => 'required|array|min:1',
            'lstEstanciaHabitacion.*.habitacion_id' => 'required|integer|exists:habitacions,id',
            /*   'lstEstanciaHabitacion.*.nTotalHuespedesHabitacion' => 'required|integer|min:1', */
            'lstEstanciaHabitacion.*.nPrecioEstanciaHabitacion' => 'required|integer|min:0',
            /* 'lstEstanciaHabitacion.*.lstEstanciaHabitacionHuesped' => 'required|array|min:1',
            'lstEstanciaHabitacion.*.lstEstanciaHabitacionHuesped.*.huesped_id' => 'required|integer|exists:huespeds,id', */
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        DB::beginTransaction();
        try {
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


            // Crear la estancia sin el número de reserva
            $estancia = Estancia::create([
                'huespedTitular_id' => $huesped->id,
                'dFechaCheckIn' => $request->input('dFechaCheckIn'),
                'nTotalHabitaciones' => $request->input('nTotalHabitaciones'),
                'nEstado' => $request->input('nEstado'),
                'dFechaReservaCheckIn' => $request->input('dFechaReservaCheckIn'),
                'dFechaReservaCheckOut' => $request->input('dFechaReservaCheckOut'),
                'cNroReserva' => null, // Se establece como nulo inicialmente
            ]);





            // Validar el estado para generar el número de reserva
            if ($request->input('nEstado') == 1) {
                // Generar el número de reserva basado en el ID de la estancia
                $cNroReserva = $this->GenerarNroReserva($estancia->id, $request->input('dFechaReservaCheckIn'));

                // Actualizar la estancia con el número de reserva generado
                $estancia->update(['cNroReserva' => $cNroReserva]);
            } else {

                $checkIn = [
                    'cTipoMovimiento' => "Check-in",
                    'dFechaMovimiento' => now(),
                    'cNroHabitacion' => $request->input('cNroHabitacion'), //falta
                    'huespedResponsable_id' =>  $huesped->id,
                    'nNroHuespedes' => $request->input('nNroHuespedes'), //falta
                    'nMontoPagar' => $request->input('nMontoPagar'), //falta
                    'nMontoPagado' => $request->input('nMontoPagado'), //falta 
                    'usuarioResponsable_id' => $request->input('usuarioResponsable_id'), //falta
                    'nEstado' => 1,
                ];
                RegistroIngresosSalidas::create($checkIn);

                $estancia->update(['dFechaCheckIn' => now()]);
            }





            // Registrar las habitaciones asociadas a la estancia
            /*  $lstEstanciaHabitacion = $request->input('lstEstanciaHabitacion');
            foreach ($lstEstanciaHabitacion as $habitacion) {
                $estanciaHabitacion = EstanciaHabitacion::create([
                    'estancia_id' => $estancia->id,
                    'habitacion_id' => $habitacion['habitacion_id'],
                    'nTotalHuespedesHabitacion' => $habitacion['nTotalHuespedesHabitacion'],
                    'nPrecioEstanciaHabitacion' => $habitacion['nPrecioEstanciaHabitacion'],
                    'nEstado' => 1, // Estado fijo (ocupada)
                ]);

                // Registrar los huéspedes asociados a esta habitación
                $lstEstanciaHabitacionHuesped = $habitacion['lstEstanciaHabitacionHuesped'];
                foreach ($lstEstanciaHabitacionHuesped as $huespedHabitacion) {
                    EstanciaHabitacionHuesped::create([
                        'huesped_id' => $huespedHabitacion['huesped_id'],
                        'estanciaHabitacion_id' => $estanciaHabitacion->id,
                        'nEstado' => 1, // Estado fijo (activo)
                    ]);
                }
            } */
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


            /* $lstPagos = $request->input('lstPagos');

            foreach ($lstPagos as $pago) {
                Pago::create([
                    'estancia_id' => $estancia->id,
                    'dFechaPago' => $pago['dFechaPago'],
                    'nTipoPago' => $pago['nTipoPago'],
                    'nMetodoPago' => $pago['nMetodoPago'],
                    'nMontoPagado' => $pago['nMontoPagado'],
                    'nEstado' => 1,
                    'cNroDocumentoTitular' => $pago['cNroDocumentoTitular'],
                    'cNombreTitular' => $pago['cNombreTitular'],
                    'cCorreoTitular' => $pago['cCorreoTitular'],
                    'nEstado' => 1,
                ]);
            }
 */
            $lstPagos = $request->input('lstPagos');

            foreach ($lstPagos as $pago) {
                // Validación para asegurar que los campos necesarios no estén vacíos
                if (!empty($pago['dFechaPago']) && !empty($pago['nTipoPago']) && !empty($pago['nMetodoPago']) && !empty($pago['nMontoPagado']) && !empty($pago['cNroDocumentoTitular']) && !empty($pago['cNombreTitular']) && !empty($pago['cCorreoTitular'])) {
                    Pago::create([
                        'estancia_id' => $estancia->id,
                        'dFechaPago' => $pago['dFechaPago'],
                        'nTipoPago' => $pago['nTipoPago'],
                        'nMetodoPago' => $pago['nMetodoPago'],
                        'nMontoPagado' => $pago['nMontoPagado'],
                        'nEstado' => 1,
                        'cNroDocumentoTitular' => $pago['cNroDocumentoTitular'],
                        'cNombreTitular' => $pago['cNombreTitular'],
                        'cCorreoTitular' => $pago['cCorreoTitular'],
                    ]);
                }
            }

            if ($request->input('reservaWeb_id') > 0) {
                $reservaWeb = ReservaWeb::find($request->input('reservaWeb_id'));
                $reservaWeb->update(['nEstado' => 2]);
            }










            DB::commit();

            // Respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => 'Ingreso registrado con éxito',
                'estancia' => $estancia
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            // Manejo de errores
            return response()->json([
                'success' => false,
                'message' => 'Hubo un error al registrar el ingreso',
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
        $prefijo = $anio . $mes . 'ADM';

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


    public function ObtenerEstanciaPorId($id)
    {
        // $id = $request->query('id'); // Obtener el parámetro 'id' desde la consulta

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
            ];
        });

        return $estanciaData;
    }

    public function postEstanciaHabitacion(Request $request)
    {

        try {
            DB::beginTransaction();


            $estanciaHabitacionNueva = EstanciaHabitacion::create([
                'estancia_id' => $request->input('estancia_id'),
                'habitacion_id' => $request->input('habitacion_id'),
                'nTotalHuespedesHabitacion' => 0,
                'nPrecioEstanciaHabitacion' => $request->input('nPrecioEstanciaHabitacion'),
                'nEstado' => 1,
            ]);





            DB::commit();
            return response()->json([
                "success" => true,
                "message" => "Se cambió la habitación correctamente",
                "estanciaHabitacionNueva" => $estanciaHabitacionNueva
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "error" => "Error inesperado",
                "details" => $e->getMessage(),
                "success" => false
            ], 500);
        }
    }

    public function putEstanciaHabitacion(Request $request)
    {
        try {
            DB::beginTransaction();

            // Buscar estancia
            $estancia = Estancia::findOrFail($request->input('id'));

            // Actualizar campos de estancia
            $estancia->update([
                'dFechaReservaCheckOut' => $request->input('dFechaReservaCheckOut'),
                'nTotalHabitaciones' => $request->input('nTotalHabitaciones')
            ]);

            // Obtener la lista de habitaciones
            $lstEstanciaHabitacion = $request->input('lstEstanciaHabitacion');

            foreach ($lstEstanciaHabitacion as $habitacionEstancia) {
                if (isset($habitacionEstancia['estanciaHabitacion_id']) && $habitacionEstancia['estanciaHabitacion_id'] > 0) {
                    // Actualizar habitación de estancia existente
                    $registro = EstanciaHabitacion::find($habitacionEstancia['estanciaHabitacion_id']);
                    if ($registro) {
                        $registro->update([
                            'habitacion_id' => $habitacionEstancia['habitacion_id'],
                            'nTotalHuespedesHabitacion' => $habitacionEstancia['nTotalHuespedesHabitacion'],
                            'nPrecioEstanciaHabitacion' => $habitacionEstancia['nPrecioEstanciaHabitacion'],
                            'nEstado' => $habitacionEstancia['nEstado'] ?? 1
                        ]);
                    }
                } else {
                    // Crear nueva habitación de estancia
                    EstanciaHabitacion::create([
                        'estancia_id' => $estancia->id,
                        'habitacion_id' => $habitacionEstancia['habitacion_id'],
                        'nTotalHuespedesHabitacion' => $habitacionEstancia['nTotalHuespedesHabitacion'],
                        'nPrecioEstanciaHabitacion' => $habitacionEstancia['nPrecioEstanciaHabitacion'],
                        'nEstado' => $habitacionEstancia['nEstado'] ?? 1
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Se actualizó la estancia correctamente"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "success" => false,
                "error" => "Error inesperado",
                "details" => $e->getMessage()
            ], 500);
        }
    }


    public function putCambioHabitacion(Request $request)
    {
        try {
            // Validación del Request
            $request->validate([
                'estanciaHabitacionOrigen_id' => 'required|integer',
                'nNroHabitacionOrigen' => 'required|integer',
                'nNroHabitacionDestino' => 'required|integer',
                'nPrecioEstanciaHabitacion' => 'required|numeric|min:0',
                'cObservaciones' => 'nullable|string',
                'bCambioPrecio' => 'required|boolean',
                'estancia_id' => 'required|integer',
                'habitacion_id' => 'required|integer',
                'nEstadoHabitacion' => 'required|integer',
                'cMotivoBloqueo' => 'nullable|string'
            ]);

            DB::beginTransaction();

            // Obtener la estancia y la habitación asociada
            $estanciaHabitacionOrigen = EstanciaHabitacion::findOrFail($request->input('estanciaHabitacionOrigen_id'));

            // Desactivar la estancia original
            $estanciaHabitacionOrigen->update(['nEstado' => 0]);

            // Actualizar la habitación anterior
            Habitacion::where('id', $estanciaHabitacionOrigen->habitacion_id)->update([
                'nEstado' => $request->input('nEstadoHabitacion'),
                'cMotivoBloqueo' => $request->input('cMotivoBloqueo')
            ]);

            // Obtener huéspedes de la estancia original
            $nTotalHuespedesHabitacion = EstanciaHabitacionHuesped::where('estanciaHabitacion_id', $estanciaHabitacionOrigen->id)->count();
            $nEstadoEstancia = Estancia::findOrFail($request->input('estancia_id'))->nEstado;
            $huespedResponsable_id = Estancia::findOrFail($request->input('estancia_id'))->huespedTitular_id;

            // Crear la nueva estancia en la nueva habitación
            $estanciaHabitacionNueva = EstanciaHabitacion::create([
                'estancia_id' => $request->input('estancia_id'),
                'habitacion_id' => $request->input('habitacion_id'),
                'nTotalHuespedesHabitacion' => $nTotalHuespedesHabitacion,
                'nPrecioEstanciaHabitacion' => $request->input('nPrecioEstanciaHabitacion'),
                'nEstado' => 1,
            ]);

            // Transferir huéspedes si hay
            if ($nTotalHuespedesHabitacion > 0) {
                $huespedes = EstanciaHabitacionHuesped::where('estanciaHabitacion_id', $estanciaHabitacionOrigen->id)
                    ->get()
                    ->map(function ($huespedHabitacion) use ($estanciaHabitacionNueva) {
                        return [
                            'huesped_id' => $huespedHabitacion->huesped_id,
                            'estanciaHabitacion_id' => $estanciaHabitacionNueva->id,
                            'nEstado' => 1,
                        ];
                    });

                EstanciaHabitacionHuesped::insert($huespedes->toArray());
            }

            // Registrar el cambio de habitación
            CambioHabitaciones::create([
                'estanciaHabitacionOrigen_id' => $estanciaHabitacionOrigen->id,
                'nNroHabitacionOrigen' => $request->input('nNroHabitacionOrigen'),
                'estanciaHabitacionDestino_id' => $estanciaHabitacionNueva->id,
                'nNroHabitacionDestino' => $request->input('nNroHabitacionDestino'),
                'dFechaCambio' => now(),
                'cObservaciones' => $request->input('cObservaciones'),
                'bCambioPrecio' => $request->input('bCambioPrecio'),
                'nEstadoEstancia' => $nEstadoEstancia,
                'nEstado' => 1,
                'usuarioResponsable_id' => $request->input('usuarioResponsable_id'),
                'huespedResponsable_id' =>   $huespedResponsable_id
            ]);

            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Se cambió la habitación correctamente",
                "estanciaHabitacionDestino_id" => $estanciaHabitacionNueva->id,
                "huespedesTransferidos" => $nTotalHuespedesHabitacion
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "error" => "Error inesperado",
                "details" => $e->getMessage(),
                "success" => false
            ], 500);
        }
    }
}
