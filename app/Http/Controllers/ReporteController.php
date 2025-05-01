<?php

namespace App\Http\Controllers;

use App\Models\cambioHabitaciones;
use App\Models\Estancia;
use App\Models\EstanciaHabitacion;
use App\Models\Habitacion;
use App\Models\Huesped;
use App\Models\Pago;
use App\Models\RegistroIngresosSalidas;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReporteController extends Controller
{

    public function allUsuarios()
    {
        $usuarios = User::all();

        return response()->json([
            'success' => true,
            'data' => $usuarios
        ], 200);
    }

    public function allHabitaciones()
    {
        $habitaciones = Habitacion::all();

        return response()->json([
            'success' => true,
            'data' => $habitaciones
        ], 200);
    }

    public function ingresoSalidas(Request $request)
    {
        // Obtener parámetros de la solicitud
        $usuarioResponsable = $request->input('usuarioResponsable', 0);
        $tipoMovimiento = $request->input('tipoMovimiento', 3);
        $cNroDocumento = $request->input('cNroDocumento', null);
        $desde = $request->input('desde', null);
        $hasta = $request->input('hasta', null);

        // Convertir tipoMovimiento a texto
        $tipoMovimientoTexto = null;
        if ($tipoMovimiento == 1) {
            $tipoMovimientoTexto = 'Check-in';
        } elseif ($tipoMovimiento == 2) {
            $tipoMovimientoTexto = 'Check-out';
        }

        // Construir consulta base
        $query = RegistroIngresosSalidas::with([
            'usuarioResponsable:id,name,email,dni',
            'huespedResponsable:id,cNombres,cApellidos,cCorreo,cNroDocumento'
        ]);

        // Aplicar filtro de usuario responsable
        if ($usuarioResponsable != 0) {
            $query->where('usuarioResponsable_id', $usuarioResponsable);
        }

        // Aplicar filtro de tipo de movimiento
        if ($tipoMovimientoTexto) {
            $query->where('cTipoMovimiento', $tipoMovimientoTexto);
        }

        if ($cNroDocumento) {
            $huesped = Huesped::where('cNroDocumento', $cNroDocumento)->first();
            if ($huesped) {
                $query->where('huespedResponsable_id', $huesped->id);
            } else {
                $query->where('huespedResponsable_id', 0);
            }
        }
        // Aplicar filtros de fecha
        if ($desde) {
            $query->whereDate('dFechaMovimiento', '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate('dFechaMovimiento', '<=', $hasta);
        }

        // Obtener los registros filtrados
        $registros = $query->get();

        // Mapear los datos para la respuesta JSON
        $lstRegistros = $registros->map(function ($registro) {
            return [
                'id' => $registro->id,
                'cTipoMovimiento' => $registro->cTipoMovimiento,
                'dFechaMovimiento' => $registro->dFechaMovimiento,
                'cNroHabitacion' => $registro->cNroHabitacion,
                'huespedResponsable' => $registro->huespedResponsable,
                'cHuespedResponsable' => '(' . $registro->huespedResponsable->cNroDocumento . ') ' . $registro->huespedResponsable->cNombres . ' ' . $registro->huespedResponsable->cApellidos,
                'nNroHuespedes' => $registro->nNroHuespedes,
                'nMontoPagar' => $registro->nMontoPagar,
                'nMontoPagado' => $registro->nMontoPagado,
                'usuarioResponsable' => $registro->usuarioResponsable,
                'cUsuarioResponsable' => '(' . $registro->usuarioResponsable->dni . ') ' . $registro->usuarioResponsable->name,
                'nEstado' => $registro->nEstado
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $lstRegistros
        ], 200);
    }

    public function cambiosHabitacion(Request $request)
    {
        // Obtener parámetros de la solicitud
        $usuarioResponsable = $request->input('usuarioResponsable', 0);
        $tipoMovimiento = $request->input('tipoMovimiento', 3);
        $cNroDocumento = $request->input('cNroDocumento', null); //del huesped
        $desde = $request->input('desde', null);
        $hasta = $request->input('hasta', null);

        // Convertir tipoMovimiento a texto
        $nEstadoEstancia = null;
        if ($tipoMovimiento == 1) {
            $nEstadoEstancia = 1;
        } elseif ($tipoMovimiento == 2) {
            $nEstadoEstancia = 2;
        }

        // Construir consulta base
        $query = cambioHabitaciones::with([
            'usuarioResponsable:id,name,email,dni',
            'huespedResponsable:id,cNombres,cApellidos,cCorreo,cNroDocumento'
        ]);

        // Aplicar filtro de usuario responsable
        if ($usuarioResponsable != 0) {
            $query->where('usuarioResponsable_id', $usuarioResponsable);
        }

        // Aplicar filtro de tipo de movimiento
        if ($nEstadoEstancia > 0) {
            $query->where('nEstadoEstancia', $nEstadoEstancia);
        }

        if ($cNroDocumento) {

            $huesped = Huesped::where('cNroDocumento', $cNroDocumento)->first();
            if ($huesped) {
                $query->where('huespedResponsable_id', $huesped->id);
            } else {
                $query->where('huespedResponsable_id', 0);
            }
        }
        // Aplicar filtros de fecha
        if ($desde) {
            $query->whereDate('dFechaCambio', '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate('dFechaCambio', '<=', $hasta);
        }

        // Obtener los registros filtrados
        $registros = $query->get();

        // Mapear los datos para la respuesta JSON
        // Mapear los datos para la respuesta JSON
        $lstRegistros = $registros->map(function ($registro) {

            // Definir un valor por defecto
            $cEstadoEstancia = 'Desconocido';

            if ($registro->nEstadoEstancia == 1) {
                $cEstadoEstancia = 'Reserva';
            } elseif ($registro->nEstadoEstancia == 2) {
                $cEstadoEstancia = 'Estancia';
            }

            return [
                'id' => $registro->id,
                'cObservaciones' => $registro->cObservaciones,
                'nNroHabitacionDestino' => $registro->nNroHabitacionDestino,
                'nNroHabitacionOrigen' => $registro->nNroHabitacionOrigen,
                'dFechaCambio' => $registro->dFechaCambio,
                'cEstadoEstancia' => $cEstadoEstancia,
                'cUsuarioResponsable' => '(' . $registro->usuarioResponsable->dni . ') ' . $registro->usuarioResponsable->name,
                'huespedResponsable' => $registro->huespedResponsable,
                'cHuespedResponsable' => '(' . $registro->huespedResponsable->cNroDocumento . ') ' . $registro->huespedResponsable->cNombres . ' ' . $registro->huespedResponsable->cApellidos,
                'nEstado' => $registro->nEstado
            ];
        });


        return response()->json([
            'success' => true,
            'data' => $lstRegistros
        ], 200);
    }

    public function huespedResponsable(Request $request)
    {
        // Obtener parámetros de la solicitud
        $nEstadoEstancia = $request->input('nEstadoEstancia', 0);
        $cNroDocumento = $request->input('cNroDocumento', null); // Huesped titular
        $desdeIngreso = $request->input('desdeIngreso', null); // Fecha ingreso desde
        $hastaIngreso = $request->input('hastaIngreso', null); // Fecha ingreso hasta
        $nNroHabitacion = $request->input('nNroHabitacion', null);

        // Construir consulta base
        $query = EstanciaHabitacion::with([
            'estancia:id,huespedTitular_id,dFechaCheckIn,dFechaCheckOut,dFechaReservaCheckIn,dFechaReservaCheckOut,nEstado',
            'estancia.huespedTitular:id,cNombres,cNroDocumento,cApellidos,nTipoDocumento,cNroCelular,cCorreo',
            'habitacion:id,nNroHabitacion'
        ])->where('nEstado', 1);

        // Aplicar filtros si existen
        if (!is_null($nEstadoEstancia) && ($nEstadoEstancia != 0)) {
            $query->whereHas('estancia', function ($q) use ($nEstadoEstancia) {
                $q->where('nEstado', $nEstadoEstancia);
            });
        }

        if (!is_null($cNroDocumento)) {
            $query->whereHas('estancia.huespedTitular', function ($q) use ($cNroDocumento) {
                $q->where('cNroDocumento', $cNroDocumento);
            });
        }

        if (!is_null($nNroHabitacion) && $nNroHabitacion != 0) {
            $query->whereHas('habitacion', function ($q) use ($nNroHabitacion) {
                $q->where('nNroHabitacion', $nNroHabitacion);
            });
        }

        if (!is_null($desdeIngreso) && !is_null($hastaIngreso)) {
            $query->whereHas('estancia', function ($q) use ($desdeIngreso, $hastaIngreso) {
                $q->whereBetween('dFechaCheckIn', [$desdeIngreso, $hastaIngreso])
                    ->orWhereBetween('dFechaReservaCheckIn', [$desdeIngreso, $hastaIngreso]);
            });
        }

        $registros = $query->get();

        $lstRegistros = $registros->map(function ($registro) {
            // Manejo de fechas con fallback
            $dFechaIngreso = $registro->estancia->dFechaCheckIn ?? $registro->estancia->dFechaReservaCheckIn ?? '';
            $dFechaSalida = $registro->estancia->dFechaCheckOut ?? $registro->estancia->dFechaReservaCheckOut ?? '';

            // Mapeo de estados de estancia
            $estados = [
                1 => 'En Reserva',
                2 => 'En Estancia',
                3 => 'Culminada',
                4 => 'Interrumpida',
                5 => 'Reserva Cancelada'
            ];

            // Mapeo de tipos Documento
            $documentos = [
                1 => 'DNI',
                2 => 'CARNET E.',

            ];

            $cEstadoEstancia = $estados[$registro->estancia->nEstado] ?? '';
            $cTipoDocumentoResponsable = $documentos[$registro->estancia->huespedTitular->nTipoDocumento] ?? '';

            return [
                'id' => $registro->id,
                'nNroHabitacion' => $registro->habitacion->nNroHabitacion ?? '',
                'cHuespedResponsable' => trim(($registro->estancia->huespedTitular->cNombres ?? '') . ' ' . ($registro->estancia->huespedTitular->cApellidos ?? '')),
                'cNroDocumentoResponsable' => $registro->estancia->huespedTitular->cNroDocumento ?? '',
                'cTipoDocumentoResponsable' => $cTipoDocumentoResponsable ?? '',
                'cNroCelular' => $registro->estancia->huespedTitular->cNroCelular ?? '',
                'cCorreo' => $registro->estancia->huespedTitular->cCorreo ?? '',
                'dFechaIngreso' => $dFechaIngreso,
                'dFechaSalida' => $dFechaSalida,
                'cEstadoEstancia' => $cEstadoEstancia,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $lstRegistros->isNotEmpty() ? $lstRegistros : []
        ], 200);
    }


    public function ingresosTipoHabitacion(Request $request)
    {
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');
        $nivelHabitacionId = $request->input('nivelHabitacion_id'); // Nuevo filtro

        // Construir la consulta base con relaciones necesarias
        $query = Pago::with([
            'estancia.habitaciones.habitacion.tipoHabitacion',
            'estancia.habitaciones.habitacion.nivel' // Relación con nivel de habitación
        ])->where('nEstado', 1);

        // Aplicar filtros de fecha
        if ($desde) {
            $query->whereDate('dFechaPago', '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate('dFechaPago', '<=', $hasta);
        }

        // Obtener registros filtrados
        $registros = $query->get();

        // Inicializar array para acumular ingresos y conteo de estancias por tipo de habitación
        $ingresosPorTipo = [];

        foreach ($registros as $registro) {
            if (!$registro->estancia) {
                continue;
            }

            // Filtrar habitaciones según el nivelHabitacion_id (si es distinto de 0)
            $habitacionesFiltradas = $registro->estancia->habitaciones->filter(function ($habitacion) use ($nivelHabitacionId) {
                return $nivelHabitacionId == 0 || $habitacion->habitacion->nivelHabitacion_id == $nivelHabitacionId;
            });

            // Obtener los tipos de habitación y niveles únicos después del filtro
            $tiposHabitaciones = $habitacionesFiltradas->map(function ($habitacion) {
                return [
                    'tipo' => $habitacion->habitacion->tipoHabitacion->cNombreTipo ?? null,
                    'nivel' => $habitacion->habitacion->nivel->cNombreNivel ?? null
                ];
            });

            foreach ($tiposHabitaciones as $data) {
                if (!$data['tipo']) {
                    continue;
                }

                $tipo = $data['tipo'];
                $nivel = $data['nivel'];

                // Inicializar estructura si no existe
                if (!isset($ingresosPorTipo[$tipo])) {
                    $ingresosPorTipo[$tipo] = [
                        'cTipoHabitacion' => $tipo,
                        'nIngresosTotales' => 0,
                        'nTotalEstancias' => 0,
                        'cNiveles' => []
                    ];
                }

                // Sumar el monto pagado y aumentar la cuenta de estancias únicas
                $ingresosPorTipo[$tipo]['nIngresosTotales'] += $registro->nMontoPagado;
                $ingresosPorTipo[$tipo]['nTotalEstancias']++;

                // Agregar nivel si aún no está en la lista
                if ($nivel && !in_array($nivel, $ingresosPorTipo[$tipo]['cNiveles'])) {
                    $ingresosPorTipo[$tipo]['cNiveles'][] = $nivel;
                }
            }
        }

        // Convertir niveles en una cadena concatenada
        foreach ($ingresosPorTipo as &$dato) {
            $dato['cNiveles'] = implode(', ', $dato['cNiveles']);
        }

        // Formatear la respuesta
        $result = array_values($ingresosPorTipo);

        return response()->json([
            'success' => true,
            'data' => $result
        ], 200);
    }

    public function estadosHabitacion(Request $request)
    {
        // Obtener filtros desde la solicitud
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');
        $nEstadoLimpieza = $request->input('nEstadoLimpieza'); // 0, 1 o 2
        $nivelHabitacionId = $request->input('nivelHabitacion_id'); // Puede ser 0 o más

        // Construir la consulta base con relaciones necesarias
        $query = Habitacion::with([
            'tipoHabitacion:id,cNombreTipo',
            'nivel:id,cNombreNivel'
        ])->select([
            'id',
            'nNroHabitacion',
            'tipoHabitacion_id',
            'nivelHabitacion_id',
            'nEstado', // Estado de limpieza
            'updated_at as dFechaActualizacion'
        ])->where('updated_at', '<>', null);

        // Aplicar filtros de fecha
        if ($desde) {
            $query->whereDate('updated_at', '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate('updated_at', '<=', $hasta);
        }

        // Filtrar por estado de limpieza si se proporciona
        if ($nEstadoLimpieza >= 0 && $nEstadoLimpieza != null) {
            $query->where('nEstado', $nEstadoLimpieza);
        }

        // Filtrar por nivel de habitación si es distinto de 0
        if ($nivelHabitacionId && $nivelHabitacionId != 0) {
            $query->where('nivelHabitacion_id', $nivelHabitacionId);
        }

        // Obtener los resultados y mapear datos adicionales
        $habitaciones = $query->orderBy('nNroHabitacion', 'asc')->get()->map(function ($habitacion) {
            return [
                'nNroHabitacion' => $habitacion->nNroHabitacion,
                'cTipoHabitacion' => $habitacion->tipoHabitacion->cNombreTipo ?? 'Desconocido',
                'cNivelHabitacion' => $habitacion->nivel->cNombreNivel ?? 'Desconocido',
                'cEstadoLimpieza' => match ($habitacion->nEstado) {
                    0 => 'Bloqueado',
                    1 => 'Limpia',
                    2 => 'Sucia',
                    default => 'Desconocido'
                },
                'dFechaActualizacion' => $habitacion->dFechaActualizacion
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $habitaciones
        ], 200);
    }


    public function cancelaciones(Request $request)
    {
        $usuarioResponsable = $request->input('usuarioResponsable', 0);
        $desde = $request->input('desde', null);
        $hasta = $request->input('hasta', null);
        $cNroDocumento = $request->input('cNroDocumento', null); // Huesped titular

        // Construir la consulta
        $query = Estancia::with(['usuarioResponsable', 'huespedTitular', 'habitaciones', 'habitaciones.habitacion'])
            ->whereIn('nEstado', [4, 5]); // Filtrar estados 4 o 5

        // Filtrar por usuario responsable (si se envió un ID válido)
        if ($usuarioResponsable > 0) {
            $query->where('usuarioResponsable_id', $usuarioResponsable);
        }

        // Filtrar por rango de fechas si ambos parámetros están presentes
        if ($desde && $hasta) {
            $query->whereBetween('updated_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59']);
        }

        if (!is_null($cNroDocumento)) {
            $query->whereHas('huespedTitular', function ($q) use ($cNroDocumento) {
                $q->where('cNroDocumento', $cNroDocumento);
            });
        }

        $cancelaciones = $query->get();
        $estados = [
            1 => 'En Reserva',
            2 => 'En Estancia',
            3 => 'Culminada',
            4 => 'Interrumpida',
            5 => 'Reserva Cancelada'
        ];
        $lstRegistros = $cancelaciones->map(function ($registro) {
            return [
                'id' => $registro->id,
                'dFechaCancelacion' => $registro->getRawOriginal('updated_at'),
                'cMotivo' => $registro->cObservaciones,
                'cUsuarioResponsable' => optional($registro->usuarioResponsable)->dni
                    ? '(' . optional($registro->usuarioResponsable)->dni . ') ' . optional($registro->usuarioResponsable)->name
                    : 'No registrado',
                'cHuespedResponsable' => optional($registro->huespedTitular)->cNroDocumento
                    ? '(' . optional($registro->huespedTitular)->cNroDocumento . ') ' . optional($registro->huespedTitular)->cNombres . ' ' . optional($registro->huespedTitular)->cApellidos
                    : 'No registrado',
                'cNrosHabitaciones' => $registro->habitaciones->pluck('habitacion.nNroHabitacion')->filter()->implode(', '),
                'cEstado' =>  match ($registro->nEstado) {
                    4 => 'Interrumpida',
                    5 => 'Reserva Cancelada',
                    default => 'Desconocido'
                },
                'cNroReserva' => $registro->cNroReserva ?? 'Sin Reserva'

            ];
        });

        return response()->json([
            'success' => true,
            'data' => $lstRegistros
        ], 200);
    }


    public function descargarPDF(Request $request)
    {
        $usuarioResponsable_id  = $request->input('usuarioResponsable_id');
        $usuario =  User::findorfail($usuarioResponsable_id);
        $cUsuarioResponsable =  '(' . $usuario->dni . ')' . ' ' .  $usuario->name;
        $tipoReporte  = $request->input('tipoReporte');
        $registros = $request->input('registros');
        $parametros = $request->input('parametros');

        // Verificar si llegan datos
        if (!$registros || !is_array($registros)) {
            return response()->json(['message' => 'No se recibieron registros válidos'], 400);
        }



        if ($tipoReporte == 1) {

            // Parámetros 
            $tipoMovimiento = $parametros['cTipoMovimiento'];
            $documentoHuesped = $parametros['cDocumentoHuesped'];
            $desde = $parametros['cDesde'];
            $hasta = $parametros['cHasta'];
            $usuarioResponsable = $parametros['cUsuarioResponsable'];

            // Generar el PDF, pasando los parámetros
            $pdf = Pdf::loadView('pdf.rpt-entrada-salida', compact('registros', 'usuarioResponsable', 'tipoMovimiento', 'documentoHuesped', 'desde', 'hasta', 'cUsuarioResponsable'));
        }


        if ($tipoReporte == 2) {

            // Parámetros 
            $tipoMovimiento = $parametros['cTipoMovimiento'];
            $documentoHuesped = $parametros['cDocumentoHuesped'];
            $desde = $parametros['cDesde'];
            $hasta = $parametros['cHasta'];
            $usuarioResponsable = $parametros['cUsuarioResponsable'];


            // Generar el PDF, pasando los parámetros
            $pdf = Pdf::loadView('pdf.rpt-cambio-habitacion', compact('registros', 'usuarioResponsable', 'tipoMovimiento', 'documentoHuesped', 'desde', 'hasta', 'cUsuarioResponsable'));
        }


        if ($tipoReporte == 3) {

            // Parámetros 
            $estadoReserva = $parametros['cEstadoReserva'];
            $nroHabitacion = $parametros['cNroHabitacion'];
            $documentoHuesped = $parametros['cDocumentoHuesped'];
            $desde = $parametros['cDesde'];
            $hasta = $parametros['cHasta'];
            $usuarioResponsable = $parametros['cUsuarioResponsable'];


            // Generar el PDF, pasando los parámetros
            $pdf = Pdf::loadView('pdf.rpt-huesped-responsable', compact('registros', 'usuarioResponsable', 'estadoReserva', 'nroHabitacion', 'documentoHuesped', 'desde', 'hasta', 'cUsuarioResponsable'));
        }



        if ($tipoReporte == 4) {

            // Parámetros 

            $nivel = $parametros['cNivel'];
            $desde = $parametros['cDesde'];
            $hasta = $parametros['cHasta'];
            $usuarioResponsable = $parametros['cUsuarioResponsable'];


            // Generar el PDF, pasando los parámetros
            $pdf = Pdf::loadView('pdf.rpt_ingresos_tipohab', compact('registros', 'nivel', 'usuarioResponsable', 'desde', 'hasta', 'cUsuarioResponsable'));
        }



        if ($tipoReporte == 5) {

            // Parámetros 
            $estadoHabitacion = $parametros['cEstadoHabitacion'];
            $nivel = $parametros['cNivel'];
            $desde = $parametros['cDesde'];
            $hasta = $parametros['cHasta'];
            $usuarioResponsable = $parametros['cUsuarioResponsable'];


            // Generar el PDF, pasando los parámetros
            $pdf = Pdf::loadView('pdf.rpt_estado_habitacion', compact('registros', 'nivel', 'estadoHabitacion', 'usuarioResponsable', 'desde', 'hasta', 'cUsuarioResponsable'));
        }

        /*  $usuarioResponsable = "(74757790) Luis A. Guevara"; */





        if ($tipoReporte == 6) {

            // Parámetros 
           
            $documentoHuesped = $parametros['cDocumentoHuesped'];
            $desde = $parametros['cDesde'];
            $hasta = $parametros['cHasta'];
            $usuarioResponsable = $parametros['cUsuarioResponsable'];

            // Generar el PDF, pasando los parámetros
            $pdf = Pdf::loadView('pdf.rpt_cancelaciones', compact('registros', 'usuarioResponsable',  'documentoHuesped', 'desde', 'hasta', 'cUsuarioResponsable'));
        }



        return response()->make($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="reporte.pdf"',
        ]);
    }
}
