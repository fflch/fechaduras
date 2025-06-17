<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\Setor;

class Fechadura extends Model
{
    protected function local(): Attribute
    {
        return Attribute::make(
            get: fn($value) => " " . $value,
            set: fn($value) => " " . $value
        );
    }

    public function acessos() {
        return $this->hasMany(Acesso::class);
    }

    public function setores() {
        return $this->belongsToMany(Setor::class);
    }

    public function usuarios(){
        return $this->belongsToMany(User::class);
    }

}
