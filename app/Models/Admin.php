<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Admin extends Model
{
    protected $fillable = [
        'codpes', 'fechadura_id', 'user_id'
    ];

    public function fechadura()
    {
        return $this->belongsTo(Fechadura::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'codpes', 'codpes');
    }

    public function cadastradoPor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
}