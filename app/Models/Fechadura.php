<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

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
}
