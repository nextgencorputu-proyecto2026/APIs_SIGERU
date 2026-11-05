<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contenedor;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ContenedorController extends Controller
{
    public function index()
    {
        $contenedores = Contenedor::all();

        return response()->json([
            'success' => true,
            'data' => $contenedores,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ubicacionX' => 'required|numeric|between:-99.9999,99.9999|decimal:0,4',
            'ubicacionY' => 'required|numeric|between:-99.9999,99.9999|decimal:0,4',
            'estado' => 'required|in:Inhabilitado,En mantenimiento,Disponible',
            'nivelLlenado' => 'required|integer|in:0',
            'tipo' => 'required|in:Reciclables,Mixtos',
            'idRuta' => [
                'nullable',
                'integer',
                Rule::exists('ruta', 'idRuta')
                    ->where('tipoResiduo', $request->input('tipo')),
            ],
        ]);

        $contenedor = Contenedor::create($validated);

        return response()->json([
            'success' => true,
            'mensaje' => 'Contenedor creado correctamente',
            'data' => $contenedor,
        ], 201);
    }

    public function show(string $id)
    {
        $contenedor = Contenedor::find($id);

        if (!$contenedor) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Contenedor no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $contenedor,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $contenedor = Contenedor::find($id);

        if (!$contenedor) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Contenedor no encontrado',
            ], 404);
        }

        $validated = $request->validate([
            'ubicacionX' => 'sometimes|required|numeric',
            'ubicacionY' => 'sometimes|required|numeric',
            'estado' => 'sometimes|required|in:Inhabilitado,En mantenimiento,Disponible',
            'nivelLlenado' => 'sometimes|required|numeric|min:0|max:100',
            'tipo' => 'sometimes|required|in:Reciclables,Mixtos',
            'idRuta' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('ruta', 'idRuta')->where(
                    'tipoResiduo',
                    $request->input('tipo', $contenedor->tipo)
                ),
            ],
        ]);

        $tipoFinal = $validated['tipo'] ?? $contenedor->tipo;

        $rutaFinal = array_key_exists('idRuta', $validated)
            ? $validated['idRuta']
            : $contenedor->idRuta;

        if ($rutaFinal !== null) {
            $rutaCompatible = DB::table('ruta')
                ->where('idRuta', $rutaFinal)
                ->where('tipoResiduo', $tipoFinal)
                ->exists();

            if (!$rutaCompatible) {
                return response()->json([
                    'message' => 'La ruta asignada no es compatible con el tipo de residuo',
                    'errors' => [
                        'tipo' => [
                            'El tipo de residuo no coincide con la ruta asignada.',
                        ],
                    ],
                ], 422);
            }
        }

        $contenedor->update($validated);

        return response()->json([
            'success' => true,
            'mensaje' => 'Contenedor actualizado correctamente',
            'data' => $contenedor,
        ]);
    }

    public function destroy(string $id)
    {
        $contenedor = Contenedor::find($id);

        if (!$contenedor) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Contenedor no encontrado',
            ], 404);
        }

        $contenedor->delete();

        return response()->json([
            'success' => true,
            'mensaje' => 'Contenedor eliminado correctamente',
        ]);
    }
}
