<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\MenuItemRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function register(Request $request)
    {


        $rol = new Role();
        $rol->cNombreRol = $request->cNombreRol;
        $rol->nEstado = $request->nEstado;
        $rol->save();

        return response()->json([
            'success' => true,
            'message' => 'Rol registrado correctamente',
            'data' => $rol
        ], 201);
    }

    public function index(Request $request)
    {
        $query = Role::orderBy("id", "desc");

        if ($request->filled('fechaInicio')) {
            $query->whereDate('created_at', '>=', $request->fechaInicio);
        }

        if ($request->filled('fechaFin')) {
            $query->whereDate('created_at', '<=', $request->fechaFin);
        }

        $roles = $query->get()->map(function ($rol) {
            return [
                "id" => $rol->id,
                "cNombreRol" => $rol->cNombreRol,
                "nEstado" => $rol->nEstado,
                "created_at" => $rol->created_at,
                "updated_at" => $rol->updated_at,
            ];
        });

        return response()->json([
            "roles" => $roles,
        ]);
    }

    public function show(Request $request)
    {
        $idRol = $request->input('id'); // o auth()->user()->rol_id;

        $menus = Menu::with(['items.roles' => function ($q) use ($idRol) {
            $q->where('roles.id', $idRol);
        }])->get();

        $lstMenu = $menus->map(function ($menu) use ($idRol) {
            return [
                "menu_id"     => $menu->id,
                "cNombreMenu" => $menu->title,
                "sub_menus"   => $menu->items->map(function ($item) use ($idRol) {
                    // Buscar el rol asociado en la tabla pivote
                    $rolePivot = $item->roles
                        ->where('pivot.role_id', $idRol)
                        ->where('pivot.estado', 1)
                        ->first(); // Devuelve null si no hay coincidencia

                    $nEstado        = $rolePivot ? 1 : 0;
                    $nIdMenuItemRol = $rolePivot ? $rolePivot->pivot->id : 0;
                    $bPermisoEdicion = $rolePivot ? $rolePivot->pivot->bPermisoEdicion : 0;
                    $bPermisoEliminacion = $rolePivot ? $rolePivot->pivot->bPermisoEliminacion : 0;

                    return [
                        "submenu_id"      => $item->id,
                        "menu_id"         => $item->menu_id,
                        "cNombreSubMenu"  => $item->title,
                        "nEstado"         => $nEstado,
                        "nIdMenuItemRol"  => $nIdMenuItemRol,
                        "bPermisoEdicion" => $bPermisoEdicion,
                        "bPermisoEliminacion" => $bPermisoEliminacion,
                        "pivor" => $rolePivot
                    ];
                }),
            ];
        });


        return response()->json([
            "lstMenu" => $lstMenu,
        ]);
    }


    public function update(Request $request)
    {
        try {
            DB::beginTransaction();

            // Buscar el rol por ID
            $rol = Role::findOrFail($request->id);

            // Actualizar datos del rol
            $rol->update([
                'cNombreRol' => $request->cNombreRol,
                'nEstado'    => $request->nEstado,
            ]);

            if ($request->has('lstMenu')) {
                foreach ($request->lstMenu as $menu) {
                    if (isset($menu['sub_menus']) && is_array($menu['sub_menus'])) {
                        foreach ($menu['sub_menus'] as $submenu) {
                            if ($submenu['nIdMenuItemRol'] == 0) {
                                // Registrar nuevo
                                MenuItemRole::create([
                                    'menu_item_id' => $submenu['submenu_id'],
                                    'role_id'      => $rol->id,
                                    'estado'       => $submenu['nEstado'],
                                    'bPermisoEdicion' => $submenu['bPermisoEdicion'],
                                    'bPermisoEliminacion' => $submenu['bPermisoEliminacion'],
                                ]);
                            } else {
                                // Editar existente
                                $pivot = MenuItemRole::find($submenu['nIdMenuItemRol']);
                                if ($pivot) {
                                    $pivot->update([
                                        'estado' => $submenu['nEstado'],
                                        'bPermisoEdicion' => $submenu['bPermisoEdicion'],
                                        'bPermisoEliminacion' => $submenu['bPermisoEliminacion'],
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Rol actualizado correctamente',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                "error"   => $e->getMessage(),
                "message" => "Error inesperado al actualizar la información.",
                "success" => false
            ], 500);
        }
    }

    public function GetMenuItems(Request $request)
    {
        // Toma el rol desde el request o desde el usuario autenticado
        $idRol = $request->input('id', auth()->user()->rol_id ?? null);

        if (!$idRol) {
            return response()->json([
                "error" => "Rol no especificado"
            ], 400);
        }

        $menuItems = Menu::whereHas('items.roles', function ($q) use ($idRol) {
            $q->where('roles.id', $idRol)
                ->where('menu_item_role.estado', 1);
        })
            ->with(['items' => function ($q) use ($idRol) {
                $q->whereHas('roles', function ($q2) use ($idRol) {
                    $q2->where('roles.id', $idRol)
                        ->where('menu_item_role.estado', 1);
                })->orderBy('id', 'asc');
            }])
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            "menuItems" => $menuItems
        ]);
    }

    public function PostPermisosPorUsuarioModulo(Request $request)
    {
        try {
            // 1. Obtener usuario y rol
            $usuario = User::find($request->id);
            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 200);
            }

            $rol_id = $usuario->rol_id;

            // 2. Obtener submenú por link
            $sub_menu = MenuItem::where('link', $request->link)->first();
            if (!$sub_menu) {
                return response()->json([
                    'success' => false,
                    'message' => 'Submenú no encontrado'
                ], 200);
            }

            $sub_menu_id = $sub_menu->id;

            // 3. Buscar permisos en la tabla pivot
            $item = MenuItemRole::where('menu_item_id', $sub_menu_id)
                ->where('role_id', $rol_id)
                ->where('estado', 1)
                ->first();

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron permisos para este usuario en el submenú indicado'
                ], 200);
            }

            // 4. Respuesta
            return response()->json([
                'success' => true,
                'message' => 'Usuario identificado correctamente',
                'bPermisoEdicion' => (bool) $item->bPermisoEdicion,
                'bPermisoEliminacion' => (bool) $item->bPermisoEliminacion,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "error"   => $e->getMessage(),
                "message" => "Error inesperado al obtener la información.",
                "success" => false
            ], 500);
        }
    }
}
