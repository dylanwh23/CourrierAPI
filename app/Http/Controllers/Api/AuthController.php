<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Models\AgenteSoporte;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'surname' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'cedula' => 'required|string|max:20|unique:users',
                'date_of_birth' => 'required|date',
            ]);
            $user = User::create([
                'name' => $request->name,
                'surname' => $request->surname,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'cedula' => $request->cedula,
                'fecha_nacimiento' => $request->date_of_birth,
            ]);
            $user->sendEmailVerificationNotification();
            return response()->json([
                'message' => 'Usuario registrado exitosamente',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ], Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error inesperado al registrar el usuario.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $request->session()->regenerate();

        // Devuelve todos los datos del usuario autenticado
        $user = Auth::user();

        return response()->json([
            'message' => 'Logged in successfully',
            'user' => $user
        ]);
    }
    public function logout(Request $request)
    {
        Auth::guard('web')->logout(); // Usa el guard 'web' para SPAs

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    // Verifica si el usuario autenticado es agente y devuelve el estado si lo es
    public function esAgente(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['es_agente' => false], 401);
        }

        $agente = $user->agente;
        if ($agente) {
            return response()->json([
                'es_agente' => true,
                'estado' => $agente->estado
            ]);
        } else {
            return response()->json(['es_agente' => false]);
        }
    }


    // Devuelve los datos de un usuario por su ID solo si el autenticado es agente
    public function consultarUsuarioPorIdSoloAgentes(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || !$user->agente) {
            // return response()->json(['message' => 'No autorizado, solo agentes'], 403);
            //por ahora comentado wacho para que aparezca el agente en  el listado de tickets de cliente;
        }
        $usuario = \App\Models\User::find($id);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
        return response()->json($usuario);
    }
    // cambiar estado agente
    public function actualizarEstadoAgente(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->agente) {
            return response()->json(['message' => 'Solo los agentes pueden cambiar su estado'], 403);
        }

        $request->validate([
            'estado' => 'required|in:activo,desconectado',
        ]);

        $agente = $user->agente;
        $agente->estado = $request->estado;
        $agente->save();

        return response()->json([
            'message' => 'Estado actualizado correctamente',
            'estado' => $agente->estado,
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8',
            'confirm_new_password' => 'required|string|min:8',
        ]);
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }
        // Ensure $user is an Eloquent model instance
        $eloquentUser = \App\Models\User::find($user->id);
        if (!Hash::check($request->current_password, $eloquentUser->password)) {
            return response()->json(['message' => 'Contraseña actual incorrecta'], 403);
        }
        if ($request->new_password !== $request->confirm_new_password) {
            return response()->json(['message' => 'Las nuevas contraseñas no coinciden'], 400);
        }
        $eloquentUser->password = Hash::make($request->new_password);
        $eloquentUser->save();
        
        return response()->json(['message' => 'Contraseña actualizada exitosamente'], 200);
    }
}
