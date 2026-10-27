<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $table = 'driver';

    protected $primaryKey = 'id_driver';

    protected $fillable = ['nama_driver', 'no_telp_driver', 'tempat_driver', 'tanggal_lahir_driver', 'sim_driver', 'status_aktif_driver', 'id_transporter', 'username', 'password'];

    protected $hidden = ['password'];

    public function transporter()
    {
        return $this->belongsTo(Transporter::class, 'id_transporter', 'id_transporter');
    }

    public function jadwals()
    {
        return $this->hasMany(JadwalPengiriman::class, 'id_driver', 'id_driver');
    }
}
