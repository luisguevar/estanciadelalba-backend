<?php

namespace App\Http\Controllers;

use App\Models\cambioHabitaciones;
use App\Models\Dia;
use App\Models\EstanciaHabitacion;
use App\Models\EstanciaHabitacionHuesped;
use App\Models\Habitacion;
use App\Models\HabitacionBloqueoReserva;
use App\Models\HabitacionIncidencia;
use App\Models\HabitacionPrecio;
use App\Models\Temporada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HabitacionController extends Controller
{
    public function show(Request $request)
    {
        $id = $request->query('id'); // Obtener el parámetro 'id' desde la consulta

        $habitacion = Habitacion::find($id);

        if (!$habitacion) {
            return response()->json([
                'success' => false,
                'message' => 'Habitacion no encontrada'
            ], 404);
        }

        // Datos básicos de la habitación
        $data = [
            'id' => $habitacion->id,
            'nNroHabitacion' => $habitacion->nNroHabitacion,
            'cDescripcionHabitacion' => $habitacion->cDescripcionHabitacion,
            'tipoHabitacion_id' => $habitacion->tipoHabitacion_id,
            'nivelHabitacion_id' => $habitacion->nivelHabitacion_id,
            'cMotivoBloqueo' => $habitacion->cMotivoBloqueo,
            'nEstado' => $habitacion->nEstado,
        ];

        // Lista de precios por día
        $dias = Dia::orderBy('id', 'asc')->get();
        $lstPrecios  = $dias->map(function ($dia) use ($habitacion) {
            $precio = HabitacionPrecio::where('habitacion_id', $habitacion->id)
                ->where('dia_id', $dia->id)
                ->first();

            return [
                'id' => $dia->id,
                'cNombreDia' => $dia->cNombreDia,
                'cAbreviado' => $dia->cAbreviado,
                'nPrecio' => $precio ? $precio->nPrecio : 0,
            ];
        });

        // Precios por temporada
        $PreciosPorTemporada = Temporada::where('habitacion_id', $habitacion->id)
            ->where('nEstado', 1)
            ->orderBy('id', 'desc')
            ->get();

        $BloqueoReserva = HabitacionBloqueoReserva::where('habitacion_id', $habitacion->id)
            ->where('nEstado', 1)
            ->orderBy('id', 'desc')
            ->get();

        $lstPreciosPorTemporada = $PreciosPorTemporada->map(function ($temporada) {
            return [
                'id' => $temporada->id,
                'cNombreTemporada' => $temporada->cNombreTemporada,
                'dFechaInicio' => $temporada->dFechaInicio,
                'dFechaFin' => $temporada->dFechaFin,
                'cFechaInicio' => date('d/m/Y', strtotime($temporada->dFechaInicio)),
                'cFechaFin' => date('d/m/Y', strtotime($temporada->dFechaFin)),
                'nPrecio' => $temporada->nPrecio,
                'nEstado' => $temporada->nEstado,
            ];
        });

        $lstBloqueoReserva = $BloqueoReserva->map(function ($bloqueo) {
            return [
                'id' => $bloqueo->id,
                'cMotivoBloqueo' => $bloqueo->cMotivoBloqueo,
                'dFechaInicio' => $bloqueo->dFechaInicio,
                'dFechaFin' => $bloqueo->dFechaFin,
                'cFechaInicio' => date('d/m/Y', strtotime($bloqueo->dFechaInicio)),
                'cFechaFin' => date('d/m/Y', strtotime($bloqueo->dFechaFin)),
                'nEstado' => $bloqueo->nEstado,
            ];
        });
        // 🔹 Lista de todas las incidencias de la habitación con sus imágenes
        $listIncidencias = HabitacionIncidencia::with('imagenes')
            ->where('habitacion_id', $habitacion->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
            'lstPrecios' => $lstPrecios,
            'lstPreciosPorTemporada' => $lstPreciosPorTemporada,
            'lstBloqueoReserva' => $lstBloqueoReserva,
            'listIncidencias' => $listIncidencias, // Todas las incidencias con sus imágenes
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Obtener los datos de la solicitud
            $habitacionData = $request->only(['nNroHabitacion', 'tipoHabitacion_id', 'nivelHabitacion_id', 'nEstado']);

            // Crear una nueva habitación
            $habitacion = Habitacion::create($habitacionData);

            $nuevosPrecios = [];
            foreach (range(1, 7) as $dia_id) {
                $nuevosPrecios[] = [
                    'habitacion_id' => $habitacion->id,
                    'dia_id' => $dia_id,
                    'nPrecio' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            HabitacionPrecio::insert($nuevosPrecios);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Habitación procesada correctamente',
                'habitacion' => $habitacion,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                "error" => $e->getMessage(),
                "message" => "Error inesperado al registrar la información.",
                "success" => false
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            DB::beginTransaction();

            // Buscar la habitación por ID
            $habitacion = Habitacion::findOrFail($request->id);

            // Actualizar los datos de la habitación
            $habitacion->update([
                'nNroHabitacion' => $request->nNroHabitacion,
                'tipoHabitacion_id' => $request->tipoHabitacion_id,
                'nivelHabitacion_id' => $request->nivelHabitacion_id,
                'cMotivoBloqueo' => $request->cMotivoBloqueo,
                'nEstado' => $request->nEstado,
            ]);

            // Actualizar precios diarios
            if ($request->has('lstPrecios')) {
                $nuevosPrecios = [];
                foreach ($request->lstPrecios as $precio) {
                    $nuevosPrecios[] = [
                        'habitacion_id' => $habitacion->id,
                        'dia_id' => $precio['id'],
                        'nPrecio' => $precio['nPrecio'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                // Eliminar precios anteriores y agregar los nuevos
                HabitacionPrecio::where('habitacion_id', $habitacion->id)->delete();
                HabitacionPrecio::insert($nuevosPrecios);
            }

            // Actualizar o insertar precios por temporada
            if ($request->has('lstPreciosPorTemporada')) {
                foreach ($request->lstPreciosPorTemporada as $temporada) {
                    if ($temporada['id'] == 0) {
                        // Insertar nueva temporada
                        Temporada::create([
                            'habitacion_id' => $habitacion->id,
                            'cNombreTemporada' => $temporada['cNombreTemporada'],
                            'dFechaInicio' => $temporada['dFechaInicio'],
                            'dFechaFin' => $temporada['dFechaFin'],
                            'nPrecio' => $temporada['nPrecio'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        // Actualizar temporada existente
                        Temporada::where('id', $temporada['id'])->update([
                            'cNombreTemporada' => $temporada['cNombreTemporada'],
                            'nPrecio' => $temporada['nPrecio'],
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            if ($request->has('lstBloqueoReserva')) {
                foreach ($request->lstBloqueoReserva as $bloqueo) {
                    if ($bloqueo['id'] == 0) {
                        // Insertar nueva temporada
                        HabitacionBloqueoReserva::create([
                            'habitacion_id' => $habitacion->id,
                            'cMotivoBloqueo' => $bloqueo['cMotivoBloqueo'],
                            'dFechaInicio' => $bloqueo['dFechaInicio'],
                            'dFechaFin' => $bloqueo['dFechaFin'],
                            'created_at' => now(),
                            'updated_at' => now(),
                            'nEstado' =>  $bloqueo['nEstado'],
                        ]);
                    } else {
                        // Actualizar temporada existente
                        HabitacionBloqueoReserva::where('id', $bloqueo['id'])->update([
                            'cMotivoBloqueo' => $bloqueo['cMotivoBloqueo'],
                            'updated_at' => now(),
                            'nEstado' =>  $bloqueo['nEstado'],
                        ]);
                    }
                }
            }

            if ($request->has('lstPreciosPorTemporadaDelete')) {
                foreach ($request->lstPreciosPorTemporadaDelete as $temporada) {
                    Temporada::where('id', $temporada['id'])->update([

                        'nEstado' => 0,
                        'updated_at' => now(),
                    ]);
                }
            }

            if ($request->has('lstBloqueoReservaDelete')) {
                foreach ($request->lstBloqueoReservaDelete as $bloqueo) {
                    HabitacionBloqueoReserva::where('id', $bloqueo['id'])->update([

                        'nEstado' => 0,
                        'updated_at' => now(),
                    ]);
                }
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Habitación actualizada correctamente',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                "error" => $e->getMessage(),
                "message" => "Error inesperado al actualizar la información.",
                "success" => false
            ], 500);
        }
    }

    public function postBloqueoReserva(Request $request)
    {
        try {
            DB::beginTransaction();

            // Obtener los datos de la solicitud
            $nTipoAsignacion = $request->nTipoAsignacion;
            $lstHabitaciones = $request->lstHabitaciones;
            if ($nTipoAsignacion == 1) {

                foreach ($lstHabitaciones as $habitacion) {
                    $habitacionData = [
                        'habitacion_id' => $habitacion['id'],
                        'cMotivoBloqueo' => $request->cMotivoBloqueo,
                        'dFechaInicio' => $request->dFechaInicio,
                        'dFechaFin' => $request->dFechaFin,
                        'nEstado' => 1,
                    ];
                    HabitacionBloqueoReserva::create($habitacionData);
                }
            } else if ($nTipoAsignacion == 2) {

                $lstTiposHabitaciones = $request->lstTiposHabitaciones;
                foreach ($lstTiposHabitaciones as $tipoHabitacion) {
                    $habitaciones = Habitacion::where('tipoHabitacion_id', $tipoHabitacion['id'])->get();
                    foreach ($habitaciones as $habitacion) {
                        $habitacionData = [
                            'habitacion_id' => $habitacion->id,
                            'cMotivoBloqueo' => $request->cMotivoBloqueo,
                            'dFechaInicio' => $request->dFechaInicio,
                            'dFechaFin' => $request->dFechaFin,
                            'nEstado' => 1,
                        ];
                        HabitacionBloqueoReserva::create($habitacionData);
                    }
                }
            } else if ($nTipoAsignacion == 3) {
                $lstNivelesHabitaciones = $request->lstNiveles;
                foreach ($lstNivelesHabitaciones as $nivelHabitacion) {
                    $habitaciones = Habitacion::where('nivelHabitacion_id', $nivelHabitacion['id'])->get();
                    foreach ($habitaciones as $habitacion) {
                        $habitacionData = [
                            'habitacion_id' => $habitacion->id,
                            'cMotivoBloqueo' => $request->cMotivoBloqueo,
                            'dFechaInicio' => $request->dFechaInicio,
                            'dFechaFin' => $request->dFechaFin,
                            'nEstado' => 1,
                        ];
                        HabitacionBloqueoReserva::create($habitacionData);
                    }
                }
            } else if ($nTipoAsignacion == 4) {
                $habitaciones = Habitacion::all();
                foreach ($habitaciones as $habitacion) {
                    $habitacionData = [
                        'habitacion_id' => $habitacion->id,
                        'cMotivoBloqueo' => $request->cMotivoBloqueo,
                        'dFechaInicio' => $request->dFechaInicio,
                        'dFechaFin' => $request->dFechaFin,
                        'nEstado' => 1,
                    ];
                    HabitacionBloqueoReserva::create($habitacionData);
                }
            }



            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bloqueo de Reserva procesado correctamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                "error" => $e->getMessage(),
                "message" => "Error inesperado al registrar la información.",
                "success" => false
            ], 500);
        }
    }


    public function postTemporada(Request $request)
    {
        try {
            DB::beginTransaction();

            // Obtener los datos de la solicitud
            $nTipoAsignacion = $request->nTipoAsignacion;
            $lstHabitaciones = $request->lstHabitaciones;
            if ($nTipoAsignacion == 1) {

                foreach ($lstHabitaciones as $habitacion) {
                    $habitacionData = [
                        'habitacion_id' => $habitacion['id'],
                        'cNombreTemporada' => $request->cNombreTemporada,
                        'dFechaInicio' => $request->dFechaInicio,
                        'dFechaFin' => $request->dFechaFin,
                        'nPrecio' => $request->nPrecio,
                        'nEstado' => 1,
                    ];
                    Temporada::create($habitacionData);
                }
            } else if ($nTipoAsignacion == 2) {

                $lstTiposHabitaciones = $request->lstTiposHabitaciones;
                foreach ($lstTiposHabitaciones as $tipoHabitacion) {
                    $habitaciones = Habitacion::where('tipoHabitacion_id', $tipoHabitacion['id'])->get();
                    foreach ($habitaciones as $habitacion) {
                        $habitacionData = [
                            'habitacion_id' => $habitacion->id,
                            'cNombreTemporada' => $request->cNombreTemporada,
                            'dFechaInicio' => $request->dFechaInicio,
                            'dFechaFin' => $request->dFechaFin,
                            'nPrecio' => $request->nPrecio,
                            'nEstado' => 1,
                        ];
                        Temporada::create($habitacionData);
                    }
                }
            } else if ($nTipoAsignacion == 3) {
                $lstNivelesHabitaciones = $request->lstNiveles;
                foreach ($lstNivelesHabitaciones as $nivelHabitacion) {
                    $habitaciones = Habitacion::where('nivelHabitacion_id', $nivelHabitacion['id'])->get();
                    foreach ($habitaciones as $habitacion) {
                        $habitacionData = [
                            'habitacion_id' => $habitacion->id,
                            'cNombreTemporada' => $request->cNombreTemporada,
                            'dFechaInicio' => $request->dFechaInicio,
                            'dFechaFin' => $request->dFechaFin,
                            'nPrecio' => $request->nPrecio,
                            'nEstado' => 1,
                        ];
                        Temporada::create($habitacionData);
                    }
                }
            } else if ($nTipoAsignacion == 4) {
                $habitaciones = Habitacion::all();
                foreach ($habitaciones as $habitacion) {
                    $habitacionData = [
                        'habitacion_id' => $habitacion->id,
                        'cNombreTemporada' => $request->cNombreTemporada,
                        'dFechaInicio' => $request->dFechaInicio,
                        'dFechaFin' => $request->dFechaFin,
                        'nPrecio' => $request->nPrecio,
                        'nEstado' => 1,
                    ];
                    Temporada::create($habitacionData);
                }
            }



            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Habitación procesada correctamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                "error" => $e->getMessage(),
                "message" => "Error inesperado al registrar la información.",
                "success" => false
            ], 500);
        }
    }


    public function postHabitacionWeb(Request $request)
    {
        try {
            $dataToUpdate = [];

            // Verifica si se subió una nueva imagen y actualiza el campo cImagen
            if ($request->hasFile("imagen_file")) {
                // Guardar la imagen en storage/app/public/habitaciones
                $path = $request->file("imagen_file")->store("habitaciones", "public");
                $dataToUpdate["cImagen"] = $path;
            }

            // Verifica si se envió el campo nEstado y lo agrega a la actualización
            if ($request->has("nEstado")) {
                $dataToUpdate["nEstado"] = $request->nEstado;
            }

            // Buscar la habitación
            $habitacion = Habitacion::where('id', $request->id)->first();

            if (!$habitacion) {
                return response()->json([
                    "message" => "Habitación no encontrada",
                    "success" => false
                ], 404);
            }

            // Actualizar solo los campos especificados
            $habitacion->update($dataToUpdate);

            return response()->json([
                "habitacion" => $habitacion,
                "success" => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "error" => $e->getMessage(),
                "message" => "Error inesperado al actualizar la habitación",
                "success" => false
            ], 500);
        }
    }
}
