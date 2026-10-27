<?php

namespace App\Services;

use App\Models\AkunKelola;

class AktivitasAkunService
{
    /**
     * Catat aktivitas untuk user/admin
     *
     * @param  int  $user_id
     * @param  string  $aktivitas
     * @return AkunKelola
     */
    public static function catatAktivitasUser($user_id, $aktivitas)
    {
        return AkunKelola::create([
            'id_user' => $user_id,
            'aktivitas_akun' => $aktivitas,
        ]);
    }

    /**
     * Catat aktivitas untuk driver
     *
     * @param  int  $driver_id
     * @param  string  $aktivitas
     * @return AkunKelola
     */
    public static function catatAktivitasDriver($driver_id, $aktivitas)
    {
        return AkunKelola::create([
            'id_driver' => $driver_id,
            'aktivitas_akun' => $aktivitas,
        ]);
    }

    /**
     * Catat aktivitas update status pengiriman
     *
     * @param  int  $driver_id
     * @param  string  $from_status
     * @param  string  $to_status
     * @param  string  $no_rec
     * @param  string|null  $keterangan
     * @return AkunKelola
     */
    public static function catatUpdateStatusPengiriman($driver_id, $from_status, $to_status, $no_rec, $keterangan = null)
    {
        $aktivitas = "Update status pengiriman No. {$no_rec}: {$from_status} → {$to_status}";
        if ($keterangan) {
            $aktivitas .= " | Catatan: {$keterangan}";
        }

        return self::catatAktivitasDriver($driver_id, $aktivitas);
    }

    /**
     * Catat aktivitas login
     *
     * @param  int  $user_id
     * @param  string  $role
     * @return AkunKelola
     */
    public static function catatLogin($user_id, $role = 'admin')
    {
        return self::catatAktivitasUser($user_id, "Login sebagai {$role}");
    }

    /**
     * Catat aktivitas logout
     *
     * @param  int  $user_id
     * @param  string  $role
     * @return AkunKelola
     */
    public static function catatLogout($user_id, $role = 'admin')
    {
        return self::catatAktivitasUser($user_id, "Logout dari role {$role}");
    }
}
