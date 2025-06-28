<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Orden;
use App\Models\Compra;
use Illuminate\Support\Str;



class OrdenesController extends Controller
{
    public function createOrden(Request $request)
    {
        // cargar factura
        $file = $request->file('imagen_factura');
        $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('facturas', $fileName, 'public');
        try {
            $orden = Orden::create([
                'user_id' => $request->user_id,
                'tracking_id' => 'UES' . uniqid(),
            ]);
            $compra = Compra::create([
                'valor_declarado' => $request->valor_declarado,
                'estado' => 'En viaje a casilla',
                'descripcion' => $request->descripcion,
                'orden_id' => $orden->id,
                'imagen_factura' => $fileName,
                'proveedor' => $request->proveedor,
            ]);

           
            $orden->compras()->save($compra);
            $orden->actualizarValorTotal();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear la orden: ' . $e->getMessage(),
            ], 500);
        }
        return response()->json([
            'message' => 'Orden creada exitosamente',
            'orden_id' => $orden->id,
            'orden_status' => $orden->status,
            'ultima_fecha_actualizacion_estado' => $orden->ultima_fecha_actualizacion_estado,
            'tracking_id' => $orden->tracking_id,
            'valor_total' => $orden->valor_total,
            'user_id' => $orden->user_id,
        ], 201);
    }
    public function getOrdenesByUserId($userId)
    {
        $ordenes = Orden::where('user_id', $userId)->with('compras')->get();
        return response()->json($ordenes, 200);
    }
    public function createCompra(Request $request, $ordenId)
    {
               // cargar factura
        $file = $request->file('imagen_factura');
        $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('facturas', $fileName, 'public');
        try {
            $orden = Orden::findOrFail($ordenId);
              $compra = Compra::create([
                'valor_declarado' => $request->valor_declarado,
                'estado' => 'En viaje a casilla',
                'descripcion' => $request->descripcion,
                'orden_id' => $orden->id,
                'imagen_factura' => $fileName,
                'proveedor' => $request->proveedor,
            ]);
            $orden->compras()->save($compra);
            $orden->actualizarValorTotal();
            return response()->json([
                'message' => 'Compra creada exitosamente',
                'compra_id' => $compra->id,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear la compra: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function confirmarEnvioOrden($ordenId)
    {
        try {
            $orden = Orden::findOrFail($ordenId);
            $orden->actualizarStatus('En viaje');
            $orden->ultima_fecha_actualizacion_estado = now();
            $orden->save();
            return response()->json([
                'message' => 'Orden confirmada y en viaje',
                'orden_id' => $orden->id,
                'status' => $orden->status,
                'ultima_fecha_actualizacion_estado' => $orden->ultima_fecha_actualizacion_estado,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al confirmar el envío de la orden: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function confirmarRecepcionCompra($compraId)
    {
        try {
            $compra = Compra::findOrFail($compraId);
            $compra->estado = 'Arribado';
            $compra->save();
            return response()->json([
                'message' => 'Compra confirmada como arribada',
                'compra_id' => $compra->id,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al confirmar la recepción de la compra: ' . $e->getMessage(),
            ], 500);
        }
    }
}
