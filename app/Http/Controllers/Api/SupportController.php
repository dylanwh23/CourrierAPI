<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactReceived;
use App\Models\User;
use App\Models\Ticket;
use App\Events\TicketActualizado;

class SupportController extends Controller
{
    public function contact(Request $request)
    {
        // 1. Validar datos
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'message' => 'required|string',
        ]);

        Log::info('[CONTACT] Payload recibido:', ['raw' => $request->getContent()]);
        Log::info('[CONTACT] Datos validados:', $data);

        // 2. Buscar usuario por email
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            $fullMessage = $data['message'] . "\n\n" .
                "Hola {$data['name']}, para poder ayudarte necesitamos que tengas una cuenta en ChinaGO.\n\n" .
                "Creá tu cuenta y volvé a contactarnos cuando gustes 💙";

            Mail::to($data['email'])->send(new ContactReceived($data['name'], $fullMessage));

            Log::info("[CONTACT] Usuario inexistente, se le sugirió registrarse: {$data['email']}");

            return response()->json([
                'status'  => 'success',
                'message' => 'Consulta recibida. Revisa tu correo para continuar.',
            ]);
        }

        try {
            $agenteId = Ticket::asignarAgenteAleatorio();

            // Si llegó hasta acá, hay agente asignado, seguí con la lógica normal
            // Por ejemplo, crear el ticket y asignar el agente...

        } catch (\Exception $e) {
            // La excepción viene porque no hay agentes disponibles
            Log::warning("No hay agentes disponibles para asignar al usuario ID {$user->id}");

            // Envío el mail
            Mail::raw('No hay agentes disponibles ahora. Por favor intente contactarnos entre las 9:00 y las 18:00.', function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Agentes no disponibles');
            });

            // Respondemos sin error para no romper la app
            return response()->json([
                'message' => 'No hay agentes disponibles en este momento. Te enviamos un correo con más detalles.'
            ], 200);
        }

        // 4. Crear ticket
        $ticket = Ticket::create([
            'orden_id'  => 0,
            'asunto'    => $data['message'],
            'estado'    => 'pendiente',
            'user_id'   => $user->id,
            'agente_id' => $agenteId,
        ]);

        // 5. Emitir evento
        event(new TicketActualizado($ticket, 'creado'));

        // 6. Enviar mail con link
        $link = 'http://localhost:4200/soportechat';
        $mailMessage = $data['message'] . "\n\n" .
            "Se creó un ticket de soporte para tu consulta.\n\n" .
            "Podés continuar tu consulta haciendo clic aquí:\n$link";

        Mail::to($user->email)->send(new ContactReceived($user->name, $mailMessage, $link));

        Log::info("[CONTACT] Ticket creado y notificado para user_id={$user->id}, ticket_id={$ticket->id}");

        return response()->json([
            'status'  => 'success',
            'message' => 'Consulta recibida. Se creó un ticket de soporte.',
        ]);
    }
}
