<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\Setor;

class Fechadura extends Model
{
    protected $fillable = [
        'local', 'ip', 'porta', 'usuario', 'senha', 'observacao'
    ];
    
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
    
    public function areas(){
        return $this->belongsToMany(Area::class, 'fechadura_areas');
    }

    public function usuarios(){
        return $this->belongsToMany(User::class);
    }

    public function usuariosExternos()
    {
        return $this->hasMany(UsuarioExterno::class);
    }

    public function admins()
    {
        return $this->hasMany(Admin::class);
    }

    public function usuariosBloqueados()
    {
        return $this->hasMany(UsuarioBloqueado::class);
    }
}
