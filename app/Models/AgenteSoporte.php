<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgenteSoporte extends Model
{
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
