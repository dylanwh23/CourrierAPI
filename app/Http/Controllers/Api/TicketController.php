<?php

namespace App\Http\Controllers\Api;

use App\Models\Ticket;
use App\Models\Mensajes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Events\MensajeEnviado;
use App\Events\TicketActualizado;
use App\Models\Orden;

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
        try {
            $user = Auth::user();

            $ordenId = $request->orden_id;
            $asunto = $request->asunto;
            $clienteId = $user->agente ? $request->user_id : $user->id;
            $orden = Orden::findOrFail($ordenId);

            if ((int) $orden->user_id !== (int) $clienteId) {
                throw new \Exception('El usuario no pertenece a esta orden');
            }

            $data = [
                'orden_id' => $ordenId,
                'asunto' => $asunto,
                'estado' => 'pendiente',
                'user_id' => $clienteId,
            ];

            if ($user->agente) {
                $data['agente_id'] = $user->id;
            } else {
                $agenteId = Ticket::asignarAgenteAleatorio();
                if ($agenteId) {
                    $data['agente_id'] = $agenteId;
                }
            }
            $ticket = Ticket::create($data);
            event(new TicketActualizado($ticket, 'creado'));

            return response()->json([
                'success' => true,
                'message' => 'Ticket creado correctamente',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(), // esto captura el mensaje en Angular como err.error.error
            ], 403);
        }
    }

    // Agregar un mensaje a un ticket
    public function addMensaje(Request $request, $ticketId)
    {


        $ticket = Ticket::findOrFail($ticketId);
        $mensaje = new Mensajes($request->only([
            'contenido',
            'user_id',
            'tipo',
            'adjunto'
        ]));
        $mensaje->ticket_id = $ticket->id;
        $mensaje->save();




        // emitir evento
        event(new MensajeEnviado($mensaje));


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

    //cambia estaDO DE TICKET SOLO SI ES EL AGENTE ASIGNADO
    public function cambiarEstado(Request $request, $ticketId)
    {
        $user = Auth::user();
        $ticket = Ticket::findOrFail($ticketId);

        // Solo el agente asignado puede cambiar el estado
        if ($ticket->agente_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Validar estado recibido (opcional si querés que sea dinámico)
        $nuevoEstado = $request->input('estado');
        if (!in_array($nuevoEstado, ['pendiente', 'completado'])) {
            return response()->json(['message' => 'Estado inválido'], 422);
        }

        $ticket->estado = $nuevoEstado;
        $ticket->save();
        event(new TicketActualizado($ticket, 'actualizado'));
        return response()->json(['message' => 'Estado actualizado', 'estado' => $ticket->estado]);
    }
}
