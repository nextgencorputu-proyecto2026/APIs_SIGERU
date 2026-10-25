<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contenedor;
use App\Models\Ruta;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ContenedorController extends Controller
{
    /**
     * Listar todos los contenedores.
     */
    public function index()
    {
        $contenedores = Contenedor::all();

        return response()->json([
            'success' => true,
            'data' => $contenedores,
        ]);
    }

    /**
     * Crear un nuevo contenedor.
     */

    /**
     * Crear un nuevo contenedor.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ubicacionX' => 'required|numeric|between:-99.9999,99.9999|decimal:0,4',
            'ubicacionY' => 'required|numeric|between:-99.9999,99.9999|decimal:0,4',
            'estado' => 'required|in:Inhabilitado,En mantenimiento,Disponible',
            'nivelLlenado' => 'required|integer|in:0',
            'tipo' => 'required|in:Reciclables,Mixtos',
            'idRuta' => [
                'required',
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


    /**
     * Consultar un contenedor por su ID.
     */
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

    /**
     * Actualizar un contenedor existente.
     */

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
            'ubicacionX' => 'sometimes|required|numeric|between:-99.9999,99.9999|decimal:0,4',
            'ubicacionY' => 'sometimes|required|numeric|between:-99.9999,99.9999|decimal:0,4',
            'estado' => 'sometimes|required|in:Inhabilitado,En mantenimiento,Disponible',
            'nivelLlenado' => 'sometimes|required|integer|min:0|max:100',
            'tipo' => 'sometimes|required|in:Reciclables,Mixtos',
            'idRuta' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('ruta', 'idRuta')->where(
                    'tipoResiduo',
                    $request->input('tipo', $contenedor->tipo)
                ),
            ],
        ]);

        if (empty($validated)) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Debe proporcionar al menos un campo para actualizar.',
            ], 422);
        }

        // Obtener los valores finales del tipo y de la ruta.
        $tipoFinal = $validated['tipo'] ?? $contenedor->tipo;
        $rutaFinal = $validated['idRuta'] ?? $contenedor->idRuta;

        // Comprobar que la ruta corresponda al tipo de residuo.
        $rutaCompatible = Ruta::where('idRuta', $rutaFinal)
            ->where('tipoResiduo', $tipoFinal)
            ->exists();

        if (!$rutaCompatible) {
            return response()->json([
                'success' => false,
                'mensaje' => 'La ruta seleccionada no corresponde al tipo de residuo del contenedor.',
            ], 422);
        }

        $contenedor->update($validated);

        return response()->json([
            'success' => true,
            'mensaje' => 'Contenedor actualizado correctamente',
            'data' => $contenedor->fresh(),
        ]);
    }


    /**
     * Eliminar un contenedor.
     */
    public function destroy(string $id)
    {
        $contenedor = Contenedor::find($id);

        if (!$contenedor) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Contenedor no encontrado',
            ], 404);
        }

        // Comprobar si tiene incidencias asociadas.
        $tieneIncidencias = DB::table('contenedor_presenta')
            ->where('idContenedor', $contenedor->idContenedor)
            ->exists();

        if ($tieneIncidencias) {
            return response()->json([
                'success' => false,
                'mensaje' => 'No se puede eliminar el contenedor porque tiene incidencias asociadas.',
            ], 409);
        }

        $contenedor->delete();

        return response()->json([
            'success' => true,
            'mensaje' => 'Contenedor eliminado correctamente',
        ]);
    }
}
