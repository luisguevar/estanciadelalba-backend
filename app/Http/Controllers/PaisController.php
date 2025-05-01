<?php

namespace App\Http\Controllers;

use App\Models\Pais;
use Illuminate\Http\Request;

class PaisController extends Controller
{
    public function index()
    {
        $paises = Pais::all();
        return response()->json([
            'success' => true,
            'data' => $paises
        ], 200);
    }
}
