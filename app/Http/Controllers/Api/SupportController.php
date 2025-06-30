<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactReceived;
use App\Models\User;
use App\Models\Ticket;

class SupportController extends Controller
{
    public function contact(Request $request)
    {
        // 1) Validar datos básicos del formulario
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'message' => 'required|string',
        ]);

        Log::info('[CONTACT] Payload recibido:', ['raw' => $request->getContent()]);
        Log::info('[CONTACT] Datos validados:', $data);

        // 2) Buscar el usuario por email
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            // Construir mensaje que incluye el texto original + aviso de crear cuenta
            $fullMessage = $data['message'] . "\n\n" .
                "Hola {$data['name']}, para poder ayudarte necesitamos que tengas una cuenta en ChinaGO.\n\nCreá tu cuenta y volvé a contactarnos cuando gustes 💙";

            // Enviar mail con ese mensaje completo (sin link, porque no tiene ticket)
            Mail::to($data['email'])->send(new ContactReceived(
                $data['name'],
                $fullMessage
            ));

            Log::info("[CONTACT] Usuario inexistente, se le sugirió registrarse: {$data['email']}");

            return response()->json([
                'status'  => 'success',
                'message' => 'Consulta recibida. Revisa tu correo para continuar.',
            ]);
        }

        // 3) Crear ticket con el contenido del mensaje como asunto
        $ticket = Ticket::create([
            'orden_id' => 0,
            'asunto' => $data['message'],
            'estado' => 'pendiente',
            'user_id' => $user->id,
            'agente_id' => null,
        ]);

        // 4) Preparar mensaje para mail, agregando info de ticket y link
        $mailMessage = $data['message'] . "\n\n" .
            "Se creó un ticket de soporte para tu consulta.";

        $link = 'http://localhost:4200/soportechat';

        // Enviar mail con mensaje y link
        Mail::to($user->email)->send(new ContactReceived($user->name, $mailMessage, $link));

        Log::info('[CONTACT] Ticket creado para user_id=' . $user->id . ', ticket_id=' . $ticket->id);

        return response()->json([
            'status'  => 'success',
            'message' => 'Consulta recibida. Se creó un ticket de soporte.',
        ]);
    }
}
