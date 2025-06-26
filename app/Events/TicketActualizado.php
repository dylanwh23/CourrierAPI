<?php
namespace App\Events;

use Illuminate\Support\Facades\Log;
use App\Models\Ticket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class TicketActualizado implements ShouldBroadcast
{
    use SerializesModels;

    public $ticket;

    public function __construct(Ticket $ticket, string $tipoAccion)
    {
        $this->ticket = $ticket;

        Log::debug("[TICKET ACTUALIZADO] Ticket ID: {$ticket->id}, Tipo de acción: {$tipoAccion}");
        Log::debug("[TICKET ACTUALIZADO] El estado del ticket ha sido actualizado a: {$ticket->estado}");
        Log::debug("[TICKET ACTUALIZADO] El agente asignado al ticket es: {$ticket->agente_id}");
        Log::debug("[TICKET ACTUALIZADO] El cliente asignado al ticket es: {$ticket->user_id}");
    }

    public function broadcastOn()
    {
        return [
            new Channel('usuario.' . $this->ticket->user_id),
            new Channel('usuario.' . $this->ticket->agente_id)
        ];
    }

    public function broadcastAs()
    {
        return 'TicketActualizado';
    }
}