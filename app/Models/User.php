<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Uspdev\Replicado\DB;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'codpes'
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
        ];
    }

    public static function pessoa(int $codundclg){
        $query = "SELECT p.nompes, p.codpes, a.nompesttd
        FROM LOCALIZAPESSOA p
        INNER JOIN PESSOA a
        ON a.codpes = p.codpes
        WHERE p.codset = 606
        AND p.sitatl = 'A'"; //pegar so quem esta ativo
    
//atualizar o replicado com a fechaduara (ao clicar o botao)

        $result = DB::fetchAll($query);
        // 
        return $result;
    }

}
