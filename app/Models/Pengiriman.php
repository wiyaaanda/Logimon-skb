<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengiriman extends Model
{
    protected $table = 'pengiriman';

    protected $primaryKey = 'id_pengiriman';

    protected $fillable = ['id_jadwal', 'no_rec', 'jam_berangkat', 'vol_kg', 'total_volume_kg', 'timbangan_cv_sr', 'total_timbangan_cv_sr', 'status'];

    public function jadwal()
    {
        return $this->belongsTo(JadwalPengiriman::class, 'id_jadwal', 'id_jadwal');
    }

    public function statusLogs()
    {
        return $this->hasMany(StatusLog::class, 'id_pengiriman', 'id_pengiriman');
    }
}
