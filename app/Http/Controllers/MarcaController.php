<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\Marca;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MarcaController extends Controller
{
    public function index()
    {
        try {
            $marcas =  Marca::all();
            return ApiResponse::success('Lista de marcas', 200, $marcas);
        } catch (Exception $e) {
            return ApiResponse::error('Eror al obtener la lista de marcas: ' .$e->getMessage(), 500, );
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|unique:marcas'
            ]);
            $marca = Marca::create($request->all());
            return ApiResponse::success('Marca creada exitosamente', 201, $marca);
        } catch (ValidationException $e) {
            return ApiResponse::error('Error de validación:' .$e->getMessage(), 422);
        }
    }

    public function show($id)
    {
        try {
            $marca = Marca::findOrFail($id);
            return ApiResponse::success('Marca obtenida exitosamente', 200, $marca);

        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Marca no encontrada', 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $marca = Marca::findOrFail($id);

            $request->validate([
                'nombre' => ['required', Rule::unique('marcas')->ignore($marca)]
            ]);

            $marca->update($request->all());
            return ApiResponse::success('Marca actualizada exitosamente', 200, $marca);

        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Marca no encontrada', 404);
        } catch (Exception $e) {
            return ApiResponse::error('Error:' .$e->getMessage(), 422);
        }
    }

    public function destroy($id)
    {
        try {
            $marca = Marca::findOrFail($id);

            $marca->delete();
            return ApiResponse::success('Marca eliminada exitosamente', 200);

        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Marca no encontrada', 404);
        }
    }

    public function productosPorMarca($id)
    {
        try {
            $marca = Marca::with('productos')->findOrFail($id);
            return ApiResponse::success('Marca y lista de productos', 200, $marca);

        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Marca no encontrada', 404);
        }
    }
}
