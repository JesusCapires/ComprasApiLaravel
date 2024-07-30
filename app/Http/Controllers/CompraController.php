<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\Compra;
use App\Models\Producto;
use Exception;
use Illuminate\Database\QueryException;
// use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompraController extends Controller
{
    public function index(){
        # code
    }

    public function store(Request $request)
    {
        try {
            $productos = $request->input('productos');

            // Validar los productos
            if(empty($productos)) {
                return ApiResponse::error('No se proporcionaron productos', 400);
            }

            $validator = Validator::make($request->all(), [
                'productos' =>  'required|array',
                'productos.*.producto_id' => 'required|integer|exists:productos,id',
                'productos.*.cantidad' => 'required|integer|min:1'
            ]);

            if($validator->fails()){
                return ApiResponse::error('Datos invalidos en la lista de productos', 400, $validator->errors());
            }

            $productoItems = array_column($productos, 'producto_id');
            if(count($productoItems) !== count(array_unique($productoItems))) {
                return ApiResponse::error('No se permiten productos duplicados para la compra', 400);
            }

            $totalPagar = 0;
            $subtotal = 0;
            $compraItems = [];

            // Iteración de los productos para calcular el total a pagar
            foreach ($productos as $producto) {
                $productoBusqueda = Producto::find($producto['producto_id']);
                if (!$productoBusqueda) {
                    return ApiResponse::error('Producto no encontrado', 404);
                }

                if ($productoBusqueda->cantidad_disponible < $producto['cantidad']) {
                return ApiResponse::error('El producto no tiene suficiente cantidad disponible', 400);
                }

                //Actualización de la cantidad disponible de cada producto
                $productoBusqueda->cantidad_disponible -= $producto['cantidad'];

                $productoBusqueda->save();

                // Calculo de importes
                $subtotal = $productoBusqueda->precio * $producto['cantidad'];
                $totalPagar += $subtotal;

                // Elementos de la compra
                $compraItems[] = [
                    'producto_id' => $productoBusqueda->id,
                    'precio' => $productoBusqueda->precio,
                    'cantidad' => $producto['cantidad'],
                    'subtotal' => $subtotal
                ];

            }

        // Registro en la tabla compra
        $compra = Compra::create([
            'subtotal' => $totalPagar,
            'total' => $totalPagar
        ]);

        // Asociar los productos a la compra con sus cantidades y subtotales
        $compra->productos()->attach($compraItems);
        return ApiResponse::success('Compra realizada exitosamente', 200, $compra);

        } catch (QueryException $e) { //Error de consulta en la base de datos
            return ApiResponse::error('Error en la consulta de la base de datos', 500);
        } catch(Exception $e) {
            return ApiResponse::error('Error inesperado', 500);
        }
    }

    public function show($id)
    {
        # code
    }
}
