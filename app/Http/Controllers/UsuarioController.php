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
        $usuarios = User::orderBy("id", "desc")->get();
        return response()->json([
            "usuarios" => $usuarios,
        ]);
    }
}
