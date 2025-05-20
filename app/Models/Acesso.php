<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Acesso extends Model
{
    protected $fillable = ['fechadura_id', 'datahora', 'codpes', 'event', 'log_id_externo'];
    
    protected $casts = [
        'datahora' => 'datetime',
        'acesso' => 'boolean'
    ];
    
    public function fechadura() {
        return $this->belongsTo(Fechadura::class);
    }

    public function usuario() {
        return $this->belongsTo(User::class, 'codpes', 'codpes');
    }
}