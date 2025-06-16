<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AgenteSoporte;

class Ticket extends Model
{
    // Atributos asignables en masa
    protected $fillable = [
        'orden_id',
        'asunto',
        'estado',
        'user_id',
        'agente_id', // Nuevo campo para el agente asignado
    ];

    // Valor por defecto para estado
    protected $attributes = [
        'estado' => 'pendiente',
    ];

    // Un ticket pertenece a una orden (obligatorio)
    public function orden()
    {
        return $this->belongsTo(Orden::class, 'orden_id');
    }

    // Un ticket tiene muchos mensajes
    public function mensajes()
    {
        return $this->hasMany(Mensajes::class, 'ticket_id');
    }

    // Un ticket pertenece a un agente de soporte
    public function agente()
    {
        return $this->belongsTo(AgenteSoporte::class, 'agente_id');
    }

    // Método para asignar un agente de soporte activo aleatorio
    public static function asignarAgenteAleatorio()
    {
        return AgenteSoporte::where('estado', 'activo')->inRandomOrder()->first();
    }
}
