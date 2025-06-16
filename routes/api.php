<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrdenesController;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Api\SupportController;



Route::post('/register', [AuthController::class, 'register']); //ruta publica
Route::post('/login', [AuthController::class, 'login']);
Route::post('contact', [SupportController::class, 'contact']);
Route::post('support', [App\Http\Controllers\Api\SupportController::class, 'store']);
Route::post('/altaOrden', [OrdenesController::class, 'createOrden']);



Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    // Si tienes un logout, iría aquí porque requiere un token válido para cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Tus otras rutas de API protegidas
    // Route::apiResource('tasks', TaskController::class);
});


?>