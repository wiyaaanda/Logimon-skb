<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataTimbangan extends Model
{
    protected $table = 'data_timbangan';

    protected $primaryKey = 'id_timbangan';

    protected $fillable = ['id_pengiriman', 'id_material', 'code_material', 'nama_material', 'satuan', 'tanggal', 'total_timbangan_cv_sr', 'jumlah_pengantaran'];
}
