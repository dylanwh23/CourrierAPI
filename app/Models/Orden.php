<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orden extends Model
{
    /** @use HasFactory<\Database\Factories\OrdenFactory> */
    use HasFactory;
    protected $table = 'ordens';
    protected $fillable = [
        'user_id',
        'status',
        'ultima_fecha_actualizacion_estado',
        'tracking_id',
        'valor_total',
        'compras' // Relación con el modelo Compra
    ];
    public function compras()
    {
        return $this->hasMany(Compra::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function actualizarValorTotal()
    {
        $this->valor_total = $this->compras->sum('valor_declarado');
        $this->save();
    }
    public function actualizarStatus($nuevoEstado)
    {
        $this->status = $nuevoEstado;
        $this->ultima_fecha_actualizacion_estado = now();
        $this->save();
    }
    // Una orden puede tener cero o un ticket find
    public function ticket()
    {
        return $this->hasOne(Ticket::class, 'orden_id');
    }
}
