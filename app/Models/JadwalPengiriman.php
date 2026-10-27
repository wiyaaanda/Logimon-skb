<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalPengiriman extends Model
{
    protected $table = 'jadwal_pengiriman';

    protected $primaryKey = 'id_jadwal';

    protected $fillable = ['tanggal', 'hari', 'id_driver', 'id_kendaraan', 'id_transporter', 'id_asal', 'id_tujuan', 'id_material', 'id_kategori_material', 'code_material', 'no_pol', 'status', 'priority', 'nilai_bobot', 'progress_tujuan_persen', 'no_rec', 'jam_berangkat', 'vol_kg', 'timbangan_cv_sr'];

    public function kategoriMaterial()
    {
        return $this->belongsTo(KategoriMaterial::class, 'id_kategori_material', 'id_kategori_material');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'id_driver', 'id_driver');
    }

    public function kendaraan()
    {
        return $this->belongsTo(Kendaraan::class, 'id_kendaraan', 'id_kendaraan');
    }

    public function transporter()
    {
        return $this->belongsTo(Transporter::class, 'id_transporter', 'id_transporter');
    }

    public function asal()
    {
        return $this->belongsTo(Asal::class, 'id_asal', 'id_asal');
    }

    public function tujuan()
    {
        return $this->belongsTo(Tujuan::class, 'id_tujuan', 'id_tujuan');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'id_material', 'id_material');
    }

    public function pengirimans()
    {
        return $this->hasMany(Pengiriman::class, 'id_jadwal', 'id_jadwal');
    }
}
