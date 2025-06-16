<?php

namespace App\Events;

use App\Models\Mensajes;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MensajeEnviado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $mensaje;

    public function __construct(Mensajes $mensaje)
    {
        $this->mensaje = $mensaje;
    }

    public function broadcastOn()
    {
        return new Channel('ticket.' . $this->mensaje->ticket_id);
    }

    public function broadcastAs()
    {
        return 'MensajeEnviado';
    }

    public function broadcastWith()
    {
        // Devuelve los datos del mensaje como array para el frontend
        return [
            'id' => $this->mensaje->id,
            'ticket_id' => $this->mensaje->ticket_id,
            'contenido' => $this->mensaje->contenido,
            'user_id' => $this->mensaje->user_id,
            'tipo' => $this->mensaje->tipo,
            'adjunto' => $this->mensaje->adjunto,
            'created_at' => $this->mensaje->created_at ? $this->mensaje->created_at->toISOString() : null,
            'updated_at' => $this->mensaje->updated_at ? $this->mensaje->updated_at->toISOString() : null,
        ];
    }
}