<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\HabitacionIncidencia;
use App\Models\HabitacionIncidenciaImagen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LimpiezaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Habitacion::with(['tipoHabitacion', 'nivel'])
            ->orderBy('nNroHabitacion', 'asc');

        // Aplicar filtros si existen
        if ($request->filled('estado')) {
            $query->where('nEstado', $request->estado);
        }

        if ($request->filled('tipo')) {
            $query->where('tipoHabitacion_id', $request->tipo);
        }

        if ($request->filled('nivel')) {
            $query->where('nivelHabitacion_id', $request->nivel);
        }

        // Obtenemos las habitaciones
        $habitaciones = $query->get();

        // Pre-cargar incidencias activas (una sola consulta)
        $incidenciasActivas = HabitacionIncidencia::whereIn('nEstado', [0, 1, 2])
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('habitacion_id');

        // Transformar las habitaciones
        $habitaciones->transform(function ($habitacion) use ($incidenciasActivas) {
            $incidenciaActiva = null;

            if ($habitacion->nEstado == 2 && isset($incidenciasActivas[$habitacion->id])) {
                // Tomamos la más reciente (ya está ordenada desc)
                $incidenciaActiva = $incidenciasActivas[$habitacion->id]->first();
            }

            $habitacion->bTieneIncidenciaActiva = $incidenciaActiva ? true : false;

            if ($incidenciaActiva) {
                $habitacion->incidencia_activa = $incidenciaActiva;
            }

            return $habitacion;
        });

        // Respuesta JSON
        return response()->json([
            'success' => true,
            'data' => $habitaciones
        ], 200);
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $habitacion = Habitacion::with('tipoHabitacion', 'nivel')->findOrFail($id);

        // 🟢 Incidencia activa
        $incidenciaActiva = null;
        if ($habitacion->nEstado == 2) {
            $incidenciaActiva = HabitacionIncidencia::with(['imagenes', 'usuarioCreacion', 'usuarioModificacion'])
                ->where('habitacion_id', $habitacion->id)
                ->whereIn('nEstado', [0, 1, 2])
                ->orderByDesc('created_at')
                ->first();

            if ($incidenciaActiva) {
                $incidenciaActiva->cUsuarioCreacion = $incidenciaActiva->usuarioCreacion
                    ? sprintf(
                        'Asignado por %s (%s) el %s',
                        $incidenciaActiva->usuarioCreacion->name,
                        $incidenciaActiva->usuarioCreacion->dni,
                        $incidenciaActiva->created_at->format('d/m/Y H:i')
                    )
                    : null;

                $incidenciaActiva->cUsuarioModificacion = $incidenciaActiva->usuarioModificacion
                    ? sprintf(
                        'Revisado por %s (%s) el %s',
                        $incidenciaActiva->usuarioModificacion->name,
                        $incidenciaActiva->usuarioModificacion->dni,
                        $incidenciaActiva->updated_at->format('d/m/Y H:i')
                    )
                    : null;
            }
        }

        $habitacion->bTieneIncidenciaActiva = (bool) $incidenciaActiva;
        $habitacion->incidencia_activa = $incidenciaActiva;

        // 🟢 Incidencias históricas
        $listIncidencias = HabitacionIncidencia::with(['imagenes', 'usuarioCreacion', 'usuarioModificacion'])
            ->where('habitacion_id', $habitacion->id)
            ->when($incidenciaActiva, fn($q) => $q->where('id', '!=', $incidenciaActiva->id))
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($inc) {
                $inc->cUsuarioCreacion = $inc->usuarioCreacion
                    ? sprintf(
                        'Asignado por %s (%s) el %s',
                        $inc->usuarioCreacion->name,
                        $inc->usuarioCreacion->dni,
                        $inc->created_at->format('d/m/Y H:i')
                    )
                    : null;

                $inc->cUsuarioModificacion = $inc->usuarioModificacion
                    ? sprintf(
                        'Revisado por %s (%s) el %s',
                        $inc->usuarioModificacion->name,
                        $inc->usuarioModificacion->dni,
                        $inc->updated_at->format('d/m/Y H:i')
                    )
                    : null;

                return $inc;
            });

        $habitacion->listIncidencias = $listIncidencias;

        return response()->json([
            'success' => true,
            'data' => $habitacion
        ], 200);
    }






    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $incidencia = HabitacionIncidencia::findOrFail($request->id);
        $incidencia->nEstado = $request->nEstado;
        $incidencia->cObservaciones = $request->cObservaciones;
        $incidencia->usuarioModificacion_id = $request->usuarioModificacion_id;
        $incidencia->save();

        // 🔹 Eliminar imágenes marcadas
        if ($request->has('imagenes_eliminar')) {
            $imagenesEliminar = json_decode($request->imagenes_eliminar, true);
            if (is_array($imagenesEliminar)) {
                foreach ($imagenesEliminar as $idImg) {
                    $imagen = HabitacionIncidenciaImagen::find($idImg);
                    if ($imagen) {
                        $path = public_path(str_replace('/storage', 'storage', $imagen->ruta_imagen));
                        if (file_exists($path)) unlink($path);
                        $imagen->delete();
                    }
                }
            }
        }

        // 🔹 Subir nuevas imágenes
        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $file) {
                $nombre = 'incidencia_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $ruta = $file->storeAs('public/incidencias/' . now()->format('Y-m'), $nombre);
                $publicPath = Storage::url($ruta);

                HabitacionIncidenciaImagen::create([
                    'habitacion_incidencia_id' => $incidencia->id,
                    'ruta_imagen' => $publicPath,
                    'fechaRegistro' => now(),
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Incidencia actualizada correctamente']);
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
