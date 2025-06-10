<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Paquetes;
use Illuminate\Http\Request;

class PaqueteController extends Controller
{
public function crearPaquete(Request $request)
{
    $usuario = Auth::user();

    $request->validate([
        'peso' => 'required|numeric',
        'direccion_actual' => 'required|string',
        'direccion_origen' => 'required|string',
        'direccion_destino' => 'required|string',
        'estado' => 'required|string',
    ]);

    $paquete = Paquetes::create([
        'user_id' => $usuario->id,
        'peso' => $request->peso,
        'direccion_actual' => $request->direccion_actual,
        'direccion_origen' => $request->direccion_origen,
        'direccion_destino' => $request->direccion_destino,
        'estado' => $request->estado,
    ]);

    return response()->json($paquete, 201);
}   
public function listarPedidosUsuario()
{
    $usuario = Auth::user(); 
    $paquetes = Paquetes::where('user_id', $usuario->id)->get();
    return response()->json($paquetes);
}
}