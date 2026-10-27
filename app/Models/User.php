<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'user';

    protected $primaryKey = 'id_user';

    protected $fillable = ['username', 'name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['email_verified_at' => 'datetime'];

    public function akunKelola()
    {
        return $this->hasOne(AkunKelola::class, 'id_user', 'id_user');
    }

    public function statusLogs()
    {
        return $this->hasMany(StatusLog::class, 'id_user', 'id_user');
    }
}
