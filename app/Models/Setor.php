<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setor extends Model
{

    protected $table = 'setores';
    protected $guarded = ['id'];

    public function fechaduras()
    {
        return $this->belongsToMany(Fechadura::class);
    }

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }
}
