<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgenteSoporte extends Model
{
    protected $primaryKey = 'user_id';

    public $incrementing = false; // si no es auto-incremental (probablemente no lo es)

    protected $keyType = 'int'; // o 'string' si user_id no es entero

    protected $fillable = [
        'user_id',
        'estado',
    ];

    // Relación con User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}