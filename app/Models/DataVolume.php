<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataVolume extends Model
{
    protected $table = 'data_volume';

    protected $primaryKey = 'id_volume';

    protected $fillable = ['id_pengiriman', 'id_material', 'code_material', 'nama_material', 'satuan', 'tanggal', 'total_volume_kg', 'jumlah_pengantaran'];
}
