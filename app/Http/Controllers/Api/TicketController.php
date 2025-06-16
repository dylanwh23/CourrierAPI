<?php

namespace App\Http\Controllers\Api;
use App\Models\Ticket;
use App\Models\Mensajes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Events\MensajeEnviado;

class TicketController extends Controller
{
    // Unifica ticketsClienteAuth y ticketsAgenteAuth: lista tickets según el tipo de usuario autenticado
    public function misTickets()
    {
        $user = Auth::user();

        // Si el usuario es agente, lista los tickets asignados a él
        if ($user->agente) {
            $tickets = Ticket::where('agente_id', $user->id)->with('mensajes')->get();
        } else {
            // Si es cliente, lista sus propios tickets
            $tickets = Ticket::where('user_id', $user->id)->with('mensajes')->get();
        }
        return response()->json($tickets);
    }

    // Crear un ticket
    public function store(Request $request)
    {
        $ticket = Ticket::create($request->only([
            'orden_id', 'asunto', 'estado', 'user_id'
        ]));
        $ticket->asignarAgenteAleatorio();
        return response()->json($ticket, 201);
    }

    // Agregar un mensaje a un ticket
    public function addMensaje(Request $request, $ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        $mensaje = new Mensajes($request->only([
            'contenido', 'user_id', 'tipo', 'adjunto'
        ]));
        $mensaje->ticket_id = $ticket->id;
        $mensaje->save();

        // Forzar el uso del driver pusher y loguear el resultado
        $result = broadcast(new MensajeEnviado($mensaje))->toOthers();
        Log::info('Broadcast MensajeEnviado', [
            'mensaje_id' => $mensaje->id,
            'broadcast_result' => $result,
          
        ]);

        return response()->json($mensaje, 201);
    }

    // Listar mensajes de un ticket específico SOLO si el usuario autenticado es el agente asignado o el cliente
    public function mensajesPorTicket($ticketId)
    {
        $user = Auth::user();
        $ticket = \App\Models\Ticket::find($ticketId);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket no encontrado'], 404);
        }

        // Solo el cliente o el agente asignado pueden ver los mensajes
        if ($ticket->user_id !== $user->id && $ticket->agente_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $mensajes = \App\Models\Mensajes::where('ticket_id', $ticketId)->orderBy('created_at')->get();
        return response()->json($mensajes);
    }
}

