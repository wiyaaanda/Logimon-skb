<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AkunKelola extends Model
{
    protected $table = 'akun_kelola';

    protected $primaryKey = 'id_akun_kelola';

    protected $fillable = ['id_user', 'id_driver', 'status_driver', 'aktivitas_akun'];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'id_driver', 'id_driver');
    }
}
