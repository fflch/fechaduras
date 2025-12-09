<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioBloqueado extends Model
{
    use HasFactory;

    protected $table = 'usuarios_bloqueados';
    
    protected $fillable = ['codpes', 'fechadura_id', 'motivo', 'user_id'];
    
    public function fechadura()
    {
        return $this->belongsTo(Fechadura::class);
    }
    
    public function usuario()
    {
        return $this->belongsTo(User::class, 'codpes', 'codpes');
    }
    
    public function bloqueadoPor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}