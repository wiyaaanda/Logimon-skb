<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transporter extends Model
{
    protected $table = 'transporter';

    protected $primaryKey = 'id_transporter';

    protected $fillable = ['nama_transporter', 'no_telp_transporter', 'tempat_transporter', 'email_transporter', 'pic_transporter'];

    public function drivers()
    {
        return $this->hasMany(Driver::class, 'id_transporter', 'id_transporter');
    }

    public function kendaraans()
    {
        return $this->hasMany(Kendaraan::class, 'id_transporter', 'id_transporter');
    }
}
