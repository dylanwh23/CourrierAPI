<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrdenesController;
use App\Http\Controllers\Api\ChatbotController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\Api\SupportController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Log;

Route::post('/register', [AuthController::class, 'register']); //ruta publica
Route::post('/login', [AuthController::class, 'login']);

// Ruta principal de verificación del email
// Esta es la ruta que se usará para el enlace del correo electrónico.
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // Marca el email como verificado en la base de datos
    return redirect(env('FRONTEND_URL') . '/?email-verified=success');
})->middleware(['signed'])->name('verification.verify');

// Ruta pública para el chatbot

// Ruta para reenviar el correo de verificación (si el usuario lo solicita)
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!'); // O un JSON response para API
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/chatbot', [ChatbotController::class, 'procesarMensaje']);
    // Si tienes un logout, iría aquí porque requiere un token válido para cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/altaOrden', [OrdenesController::class, 'createOrden']);
    Route::get('/ordenes/{userId}', [OrdenesController::class, 'getOrdenesByUserId']);
    Route::post('/createCompra/{ordenId}', [OrdenesController::class, 'createCompra']);
    Route::post('/confirmarEnvioOrden/{ordenId}', [OrdenesController::class, 'confirmarEnvioOrden']);
    Route::post('/confirmarRecepcionCompra/{compraId}', [OrdenesController::class, 'confirmarRecepcionCompra']);
    Route::get('/esAgente', [AuthController::class, 'esAgente']);
    Route::get('/misTickets', [TicketController::class, 'misTickets']);
    // Puedes eliminar o comentar las rutas antiguas si ya no las usas:
    // Route::get('/misTicketsCliente', [TicketController::class, 'ticketsClienteAuth']);
    // Route::get('/misTicketsAgente', [TicketController::class, 'ticketsAgenteAuth']);
    Route::get('/consultarUsuarioPorId/{id}', [AuthController::class, 'consultarUsuarioPorIdSoloAgentes']);
    Route::get('/mensajesPorTicket/{ticketId}', [TicketController::class, 'mensajesPorTicket']);
    Route::post('/addMensaje/{ticketId}', [TicketController::class, 'addMensaje']);
     Route::post('/addMensaje/{ticketId}', [TicketController::class, 'addMensaje']);
     Route::post('/tickets/{id}/estado', [TicketController::class, 'cambiarEstado']);
     Route::post('/crearTickets', [TicketController::class, 'store']);
     Route::post('/actualizarEstadoAgente', [AuthController::class, 'actualizarEstadoAgente']);
     Route::post('/updatePassword', [AuthController::class, 'updatePassword']);
    // Tus otras rutas de API protegidas
    // Route::apiResource('tasks', TaskController::class);
    // observer evento mensajes nuevos
    Broadcast::routes(['middleware' => ['web']]);

});
Route::get('/', function () {
    return view('welcome');
});
