<?php

namespace App\Http\Controllers;

use App\Models\Ruta;
use Illuminate\Http\Request;

class RutaController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'tipoResiduo' => 'nullable|in:Mixtos,Reciclables',
        ]);

        $rutas = Ruta::query()
            ->when(
                $validated['tipoResiduo'] ?? null,
                fn ($query, $tipo) => $query->where('tipoResiduo', $tipo)
            )
            ->orderBy('descripcion')
            ->get(['idRuta', 'descripcion', 'horario', 'tipoResiduo']);

        return response()->json(['success' => true, 'data' => $rutas]);
    }
}
