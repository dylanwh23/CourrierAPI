<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    /** @use HasFactory<\Database\Factories\CompraFactory> */
    use HasFactory;
    protected $fillable = [
        'valor_declarado',
        'estado',
        'descripcion',
        'orden_id',
        'imagen_factura'
    ];
    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }
    public function actualizarEstado($nuevoEstado)
    {
        $this->estado = $nuevoEstado;
        $this->save();
    }
    public function actualizarValorDeclarado($nuevoValor)
    {
        $this->valor_declarado = $nuevoValor;
        $this->save();
    }
}
