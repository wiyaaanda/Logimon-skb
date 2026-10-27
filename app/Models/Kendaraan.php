<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kendaraan extends Model
{
    protected $table = 'kendaraan';

    protected $primaryKey = 'id_kendaraan';

    protected $fillable = ['no_pol', 'jenis_kendaraan', 'model_kendaraan', 'code_kendaraan', 'id_transporter'];

    public function transporter()
    {
        return $this->belongsTo(Transporter::class, 'id_transporter', 'id_transporter');
    }
}
