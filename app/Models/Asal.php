<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asal extends Model
{
    protected $table = 'asal';

    protected $primaryKey = 'id_asal';

    protected $fillable = ['nama_asal', 'kota_asal', 'provensi_asal', 'no_telp_asal'];
}
