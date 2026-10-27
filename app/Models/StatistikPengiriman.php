<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatistikPengiriman extends Model
{
    protected $table = 'statistik_pengiriman';

    protected $primaryKey = 'id_statistik';

    protected $fillable = ['id_pengiriman', 'id_driver', 'id_material', 'id_transporter', 'hari', 'bulan', 'tahun', 'total_pengiriman', 'driver_aktif', 'top_material', 'tren_volume_bulanan', 'jml_selesai', 'jml_pending', 'jml_proses', 'jml_mendesak', 'jml_prioritas', 'on_time_delivery_rate'];
}
