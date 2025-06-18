<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    //protected $table = 'setor_pos';
    protected $guarded = ['id'];

    public function fechaduras(){
        return $this->belongsToMany(Fechadura::class);
    }

    public function usuarios(){
        return $this->hasMany(User::class);
    }
}
