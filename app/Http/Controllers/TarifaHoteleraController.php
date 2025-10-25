<?php

namespace App\Http\Controllers;

use App\Models\Dia;
use App\Models\Habitacion;
use App\Models\HabitacionIncidencia;
use App\Models\HabitacionPrecio;
use App\Models\Servicio;
use App\Models\HabitacionServicio; // Modelo para la tabla HabitacionServicio
use App\Models\Temporada;
use App\Models\TipoCama;
use App\Models\TipoHabitacion;
use App\Models\TipoHabitacionCama;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TarifaHoteleraController extends Controller
{




    public function index(Request $request)
    {
        // Obtén el parámetro de fecha desde el request
        $dFechaInicioSemana = $request->input('dFechaInicioSemana');
        $fecha = Carbon::parse($dFechaInicioSemana);

        // Obtiene el día de la semana (lunes = 1, domingo = 7)
        $diaSemana = $fecha->dayOfWeekIso;

        // Obtener todas las habitaciones con su tipo
        $habitaciones = Habitacion::with('tipoHabitacion', 'nivel')
            ->orderBy('nNroHabitacion', 'asc')
            ->get();

        // Obtener todos los servicios
        $servicios = Servicio::orderBy('cNombreServicio', 'asc')->get();
        $tipoHabitaciones  = TipoHabitacion::orderBy('id', 'asc')->get();
        // Generar lista de días ordenados a partir del día dado
        $dias = [1, 2, 3, 4, 5, 6, 7]; // Lunes a Domingo
        $diasDisponibles = collect($dias)->map(function ($dia) use ($diaSemana, $fecha) {
            // Calcular el desplazamiento para ordenar correctamente los días
            $offset = ($dia - $diaSemana) % 7;
            $offset = $offset < 0 ? $offset + 7 : $offset;

            // Obtener la fecha correspondiente a ese día
            $fechaDia = $fecha->copy()->addDays($offset);

            // Obtener el nombre del día desde la tabla 'dias' (suponiendo que existe el modelo Dia)
            $diaInfo = Dia::find($dia);

            return [
                'id' => $dia,
                'cNombreDia' => $diaInfo ? $diaInfo->cNombreDia : 'Desconocido',
                'cAbreviado' => $diaInfo ? $diaInfo->cAbreviado : 'Des',
                'fechaDia' => $fechaDia->toDateString(), // Formato YYYY-MM-DD
                'cFechaAbrev' => $fechaDia->format('j/m'), // Formato "d/MM" (2/02)
            ];
        })->sortBy('fechaDia')->values(); // Ordenar por fecha

        $lstHabitaciones = $habitaciones->map(function ($habitacion) use ($diaSemana, $fecha,  $servicios) {
            $precios = HabitacionPrecio::where('habitacion_id', $habitacion->id)
                ->with('dia')
                ->get()
                ->map(function ($precio) use ($diaSemana, $fecha, $habitacion) {
                    $dias = [1, 2, 3, 4, 5, 6, 7]; // Lunes a Domingo
                    $indiceDia = array_search($precio->dia->id, $dias);

                    $diasAjuste = ($indiceDia + 1) - $diaSemana;
                    if ($diasAjuste < 0) {
                        $diasAjuste += 7;
                    }

                    $fechaDia = $fecha->copy()->addDays($diasAjuste);

                    // Buscar si hay una temporada activa para esta fecha y habitación
                    $temporada = Temporada::where('habitacion_id', $habitacion->id)
                        ->whereDate('dFechaInicio', '<=', $fechaDia)
                        ->whereDate('dFechaFin', '>=', $fechaDia)
                        ->where('nEstado', 1) // Temporada activa
                        ->orderBy('id', 'desc') // La temporada con el ID más alto
                        ->first();

                    // Si hay una temporada activa, usar su precio en lugar del normal
                    $nPrecioFinal = $temporada ? $temporada->nPrecio : $precio->nPrecio;

                    return [
                        'id' => $precio->dia->id,
                        'cNombreDia' => $precio->dia->cNombreDia,
                        'cAbreviado' => $precio->dia->cAbreviado,
                        'nPrecio' => $nPrecioFinal, // Precio final, considerando temporada
                        'fechaDia' => $fechaDia->toDateString(),
                        'cFechaAbrev' => $fechaDia->format('j/m'),
                    ];
                });

            // Ordenar los precios según el día de la semana
            $preciosOrdenados = $precios->sortBy(function ($item) use ($diaSemana) {
                $dias = [1, 2, 3, 4, 5, 6, 7];
                $indiceDia = array_search($item['id'], $dias);
                $offset = ($indiceDia - ($diaSemana - 1)) % 7;
                return $offset < 0 ? $offset + 7 : $offset;
            });

            // 🔹 Obtener incidencia activa solo para el flag
            $incidenciaActiva = HabitacionIncidencia::where('habitacion_id', $habitacion->id)
                ->whereIn('nEstado', [0, 1, 2])
                ->orderByDesc('created_at')
                ->first();

            return [
                'id' => $habitacion->id,
                'nNroHabitacion' => $habitacion->nNroHabitacion,
                'cNombreTipo' => $this->abreviarNombreTipo($habitacion->tipoHabitacion->cNombreTipo ?? null),
                'nEstado' => $habitacion->nEstado,
                'cImagen' => $habitacion->cImagen,
                'cNombreNivel' => $habitacion->nivel->cNombreNivel ?? null,
                'lstPrecios' => $preciosOrdenados->values(),
                'bTieneIncidenciaActiva' => $incidenciaActiva ? true : false,
                'incidencia_activa' => $incidenciaActiva,
                'lstServicios' => $servicios->map(function ($servicio) use ($habitacion) {
                    // Verificar si existe la relación en HabitacionServicio
                    $bActivo = HabitacionServicio::where('habitacion_id', $habitacion->id)
                        ->where('servicio_id', $servicio->id)
                        ->exists();

                    return [
                        'id' => $servicio->id,
                        'cNombreServicio' => $servicio->cNombreServicio,
                        'bActivo' => $bActivo, // True si existe la relación, de lo contrario false
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $lstHabitaciones,
            'dias' => $diasDisponibles, // Nueva variable con el orden correcto de los días
            'dia' => $diaSemana,
            'servicios' => $servicios,
            'tipoHabitaciones' => $tipoHabitaciones,
        ], 200);
    }








    public function indexTipoHabitacion()
    {
        $tipoHabitaciones = TipoHabitacion::orderBy('id', 'asc')->get();
        $tipoCamas = TipoCama::orderBy('cNombreTipo', 'asc')->get(); // Asumiendo que esta es tu tabla de tipos de camas

        // Mapear los datos al formato requerido
        $tipoHabitaciones = $tipoHabitaciones->map(function ($tipoHabitacion) use ($tipoCamas) {
            $nTotalPersonas = $tipoHabitacion->nAforo;
            $nTotalCamas = 0;

            $lstTipoCamas = $tipoCamas->map(function ($tipoCama) use ($tipoHabitacion, &$nTotalPersonas, &$nTotalCamas) {
                // Verificar si existe la relación en tipo_habitacion_camas
                $tipoHabitacionCama = TipoHabitacionCama::where('tipoHabitacion_id', $tipoHabitacion->id)
                    ->where('tipoCama_id', $tipoCama->id)
                    ->first();

                $bActivo = $tipoHabitacionCama ? true : false;
                $nNroPersonasCama = $tipoHabitacionCama ? $tipoHabitacionCama->nNroPersonasCama : 0;
                $nCantidad = $tipoHabitacionCama ? $tipoHabitacionCama->nCantidad : 0;
                if ($bActivo) {

                    $nTotalCamas += 1;
                }

                return [
                    'id' => $tipoCama->id,
                    'cNombreCama' => $tipoCama->cNombreTipo, // Ajusta al nombre correcto de tu campo
                    'nNroPersonas' => $nNroPersonasCama,
                    'nCantidad' => $nCantidad,
                    'bActivo' => $bActivo, // True si existe la relación, de lo contrario false
                ];
            });

            return [
                'id' => $tipoHabitacion->id,
                'nEstado' => $tipoHabitacion->nEstado,
                'cNombreTipo' => $tipoHabitacion->cNombreTipo,
                'nTotalPersonas' => $nTotalPersonas,
                'nTotalCamas' => $nTotalCamas,
                'lstTipoCamas' => $lstTipoCamas,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $tipoHabitaciones,
        ], 200);
    }


    public function store(Request $request)
    {

        try {

            DB::beginTransaction();
            foreach ($request->lstHabitaciones as $habitacionData) {
                // Si el campo id es mayor a 0, actualizar; de lo contrario, crear una nueva habitación
                if (isset($habitacionData['id']) && $habitacionData['id'] > 0) {
                    // Actualizar la habitación existente
                    $habitacion = Habitacion::findOrFail($habitacionData['id']);

                    // Eliminar todos los registros actuales de HabitacionServicio para esta habitación
                    HabitacionServicio::where('habitacion_id', $habitacion->id)->delete();

                    // Procesar el lstServicios y agregar nuevos servicios si es necesario
                    if (!empty($habitacionData['lstServicios'])) {
                        $nuevosServicios = [];
                        foreach ($habitacionData['lstServicios'] as $servicio) {
                            if ($servicio['bActivo'] === true) {
                                $nuevosServicios[] = [
                                    'habitacion_id' => $habitacion->id,
                                    'servicio_id' => $servicio['id'],
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }

                        // Insertar los nuevos registros de servicios
                        if (!empty($nuevosServicios)) {
                            HabitacionServicio::insert($nuevosServicios);
                        }
                    }
                } else {
                }
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Habitaciones procesadas correctamente',
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                "error" => $e->getMessage(),
                "message" => "Error inesperado al actualizar la información ",
                "success" => false
            ], 500);
        }
    }

    public function storeTipoHabitacion(Request $request)
    {
        $cNombreTipo = $request->input('cNombreTipo');
        $tipohabitacion_id = $request->input('id');
        $nAforo = $request->input('nAforo');
        $lstTipoCamas = $request->input('lstTipoCamas', []);

        if ($tipohabitacion_id > 0) {
            // Actualizar el registro existente
            $tipoHabitacion = TipoHabitacion::find($tipohabitacion_id);
            if ($tipoHabitacion) {
                $tipoHabitacion->cNombreTipo = $cNombreTipo;
                $tipoHabitacion->nAforo = $nAforo;
                $tipoHabitacion->save();

                // Eliminar todos los registros de TipoHabitacionCama para este tipoHabitacion_id
                TipoHabitacionCama::where('tipoHabitacion_id', $tipohabitacion_id)->delete();

                // Insertar los nuevos registros de TipoHabitacionCama
                $nuevasCamas = [];
                foreach ($lstTipoCamas as $tipoCama) {
                    if ($tipoCama['bActivo']) {
                        $nuevasCamas[] = [
                            'tipoHabitacion_id' => $tipohabitacion_id,
                            'tipoCama_id' => $tipoCama['id'],
                            'nCantidad' => $tipoCama['nCantidad'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
                TipoHabitacionCama::insert($nuevasCamas);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de habitación no encontrado',
                ], 404);
            }
        } else {
            // Insertar un nuevo registro
            $tipoHabitacion = TipoHabitacion::create([
                'cNombreTipo' => $cNombreTipo,
            ]);

            // Insertar los nuevos registros de TipoHabitacionCama
            $nuevasCamas = [];
            foreach ($lstTipoCamas as $tipoCama) {
                if ($tipoCama['bActivo']) {
                    $nuevasCamas[] = [
                        'tipoHabitacion_id' => $tipoHabitacion->id,
                        'tipoCama_id' => $tipoCama['id'],
                        'nCantidad' => $tipoCama['nCantidad'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            TipoHabitacionCama::insert($nuevasCamas);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tipo de habitación procesado correctamente',
        ], 200);
    }




    private function abreviarNombreTipo($nombreTipo)
    {
        // Reemplazar "HABITACIÓN" o "HABITACION" por "HAB", pero dejando el resto de la cadena intacta
        if (str_contains($nombreTipo, 'HABITACIÓN') || str_contains($nombreTipo, 'HABITACION')) {
            return str_replace(['HABITACIÓN', 'HABITACION'], 'HAB', $nombreTipo);
        }
        return $nombreTipo;
    }
}
