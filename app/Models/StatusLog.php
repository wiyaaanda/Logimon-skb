<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusLog extends Model
{
    protected $table = 'status_log';

    protected $primaryKey = 'id_log';

    protected $fillable = ['id_pengiriman', 'id_user', 'driver', 'username', 'dari', 'tujuan', 'material', 'waktu_pending', 'waktu_penyelesaian', 'status_update', 'keterangan_log'];

    public function pengiriman()
    {
        return $this->belongsTo(Pengiriman::class, 'id_pengiriman', 'id_pengiriman');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
