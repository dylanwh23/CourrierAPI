<?php
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
Route::post('/register', [AuthController::class, 'register']); //ruta publica
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    // Si tienes un logout, iría aquí porque requiere un token válido para cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout']);

    // Tus otras rutas de API protegidas
    // Route::apiResource('tasks', TaskController::class);
});
Route::get('/', function () {
    return view('welcome');
});
