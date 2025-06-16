<?php

namespace App\Http\Controllers\Api;
use App\Models\Ticket;
use App\Models\Mensajes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class TicketController extends Controller
{
    // Listar tickets
     public function ticketsClienteAuth()
    {
        $user = Auth::user();
        $tickets = Ticket::where('user_id', $user->id)->with('mensajes')->get();
        return response()->json($tickets);
    }

    // Lista los tickets asignados al agente autenticado
    public function ticketsAgenteAuth()
    {
        $user = Auth::user();
        $tickets = Ticket::where('agente_id', $user->id)->with('mensajes')->get();
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
        $ticket->asignarAgenteAleatorio();
        $mensaje = new Mensajes($request->only([
            'contenido', 'user_id', 'tipo', 'adjunto'
        ]));
        $mensaje->ticket_id = $ticket->id;
        $mensaje->save();
        return response()->json($mensaje, 201);
    }
}
