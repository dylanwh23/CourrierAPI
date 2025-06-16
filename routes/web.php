<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\PaqueteController;
use Illuminate\Support\Facades\Log;

Route::post('/register', [AuthController::class, 'register']); //ruta publica
Route::post('/login', [AuthController::class, 'login']);
//Route::post('/contact', [SupportController::class, 'contact']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    // Si tienes un logout, iría aquí porque requiere un token válido para cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/listarPedidosUsuario', [PaqueteController::class, 'listarPedidosUsuario']);
    Route::post('/crearPaquete', [PaqueteController::class, 'crearPaquete']);
    Route::get('/esAgente', [AuthController::class, 'esAgente']);
    Route::get('/misTickets', [TicketController::class, 'misTickets']);
    // Puedes eliminar o comentar las rutas antiguas si ya no las usas:
    // Route::get('/misTicketsCliente', [TicketController::class, 'ticketsClienteAuth']);
    // Route::get('/misTicketsAgente', [TicketController::class, 'ticketsAgenteAuth']);
    Route::get('/consultarUsuarioPorId/{id}', [AuthController::class, 'consultarUsuarioPorIdSoloAgentes']);
    Route::get('/mensajesPorTicket/{ticketId}', [TicketController::class, 'mensajesPorTicket']);
    Route::post('/addMensaje/{ticketId}', [TicketController::class, 'addMensaje']);
    // Tus otras rutas de API protegidas
    // Route::apiResource('tasks', TaskController::class);
    // observer evento mensajes nuevos 
    Broadcast::routes(['middleware' => ['web']]);

});
Route::get('/', function () {
    return view('welcome');
});
