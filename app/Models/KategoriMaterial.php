<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriMaterial extends Model
{
    protected $table = 'kategori_material';

    protected $primaryKey = 'id_kategori_material';

    protected $fillable = ['code_material', 'qty_material', 'satuan_material', 'harga_material'];

    public function materials()
    {
        return $this->hasMany(Material::class, 'id_kategori_material', 'id_kategori_material');
    }

    public function jadwals()
    {
        return $this->hasMany(JadwalPengiriman::class, 'id_kategori_material', 'id_kategori_material');
    }
}
