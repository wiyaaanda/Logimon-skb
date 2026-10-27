<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tujuan extends Model
{
    protected $table = 'tujuan';

    protected $primaryKey = 'id_tujuan';

    protected $fillable = ['nama_tujuan', 'kota_tujuan', 'provensi_tujuan', 'no_telp_tujuan'];
}
