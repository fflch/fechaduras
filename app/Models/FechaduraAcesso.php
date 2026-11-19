<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class FechaduraAcesso extends Model
{
    protected $table = 'fechadura_acessos';
 
    protected $fillable = [
        'fechadura_id', 'codpes',
        'tem_foto', 'tem_senha',
        'foto_pendente', 'senha_pendente',
    ];
 
    protected $hidden = ['senha_pendente'];
 
    protected $casts = [
        'tem_foto'       => 'boolean',
        'tem_senha'      => 'boolean',
        'foto_pendente'  => 'boolean',
        'senha_pendente' => 'encrypted',
    ];
 
    public function fechadura()
    {
        return $this->belongsTo(Fechadura::class);
    }
}
