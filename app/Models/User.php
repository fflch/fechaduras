<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;
use Uspdev\Wsfoto;
use Uspdev\SenhaunicaSocialite\Traits\HasSenhaunica;
use App\Models\Setor;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory,Notifiable,HasRoles,HasSenhaunica;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'codpes',
        'foto',
        'foto_atualizada_em'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'foto_atualizada_em' => 'datetime'
        ];
    }

    public function setor()
    {
        return $this->belongsTo(Setor::class);
    }

    public function fechadurasComoAdmin()
    {
        return $this->belongsToMany(Fechadura::class, 'admins', 'user_id', 'fechadura_id');
    }

    public function fechadurasComoUsuario()
    {
        return $this->belongsToMany(Fechadura::class, 'fechadura_user', 'user_id', 'fechadura_id');
    }

    public function temFotoLocal()
    {
        return !is_null($this->foto_path) && file_exists(storage_path('app/public/' . $this->foto_path));
    }

    public function getFotoUrlAttribute()
    {
        // 1. Tenta foto local
        if ($this->foto && Storage::disk('public')->exists($this->foto)) {
            return asset('storage/' . $this->foto);
        }
        
        // 2. Tenta buscar do replicado (via Wsfoto)
        $fotoBase64 = Wsfoto::obter($this->codpes);
        if ($fotoBase64) {
            return 'data:image/jpeg;base64,' . $fotoBase64;
        }

    }

    protected $appends = ['foto_url'];
}
