<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataOperasional extends Model
{
    protected $table = 'data_operasional';

    protected $primaryKey = 'id_operasional';

    protected $fillable = [
        'id_pengiriman',
        'id_material',
        'nama_material',
        'code_material',
        'id_tujuan',
        'tujuan',
        'no_rec',
        'jam_berangkat',
    ];
}
