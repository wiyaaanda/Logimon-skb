<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $table = 'material';

    protected $primaryKey = 'id_material';

    protected $fillable = ['nama_material', 'jenis_material', 'stock_status_material', 'harga_material', 'id_kategori_material'];

    public function kategori()
    {
        return $this->belongsTo(KategoriMaterial::class, 'id_kategori_material', 'id_kategori_material');
    }
}
