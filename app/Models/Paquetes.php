<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paquetes extends Model
{
    use HasFactory;

    protected $table = 'paquetes';

    protected $fillable = [
        'id',
        'peso',
        'direccion_actual',
        'direccion_origen',
        'direccion_destino',
        'estado',
    ];

    // Relación: un paquete pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}