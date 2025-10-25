<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UsuarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        // Construimos la consulta base con orden descendente
        $query = User::with('rol')->orderBy('id', 'desc');

        // Filtro por fecha de inicio
        if ($request->filled('fechaInicio')) {
            $query->whereDate('created_at', '>=', $request->fechaInicio);
        }

        // Filtro por fecha fin
        if ($request->filled('fechaFin')) {
            $query->whereDate('created_at', '<=', $request->fechaFin);
        }

        // Ejecutamos la consulta y mapeamos resultados
        $usuarios = $query->get()->map(function ($user) {
            return [
                "id" => $user->id,
                "name" => $user->name,
                "dni" => $user->dni,
                "email" => $user->email,
                "estado" => $user->estado,
                "rol_id" => $user->rol_id,
                "cNombreRol" => $user->rol ? $user->rol->cNombreRol : null, // ← aquí traes el nombre del rol
                "created_at" => $user->created_at,
                "updated_at" => $user->updated_at
            ];
        });

        // Retornamos la respuesta JSON
        return response()->json([
            "usuarios" => $usuarios,
        ]);
    }
}
