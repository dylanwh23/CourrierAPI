<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mensajes extends Model
{
    // Atributos asignables en masa
    protected $fillable = [
        'ticket_id',
        'contenido',
        'user_id',
        'tipo',       // Tipo de mensaje (cliente/soporte)
        'adjunto',    // Archivo adjunto (opcional)
    ];

    // Un mensaje pertenece a un ticket
    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    // Un mensaje pertenece a un usuario (puede ser cliente o agente de soporte)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    // Puedes dejar el modelo igual, solo necesitas registrar el observer
}