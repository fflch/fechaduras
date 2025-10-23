<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioExterno extends Model
{
    protected $table = 'usuarios_externos';
    
    protected $fillable = [
        'nome', 'foto', 'fechadura_id', 'user_id', 'vinculo', 'observacao'
    ];

    public function fechadura()
    {
        return $this->belongsTo(Fechadura::class);
    }

    public function cadastradoPor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
