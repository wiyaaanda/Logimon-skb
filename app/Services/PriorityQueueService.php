<?php

namespace App\Services;

use App\Models\JadwalPengiriman;
use App\Models\StatusLog;
use Carbon\Carbon;

class PriorityQueueService
{
    // Hitung bobot menurut rumus sederhana yang diberikan
    public function calculateWeight(JadwalPengiriman $jadwal): float
    {
        $kategori = $jadwal->priority ?? 'normal';
        // assign higher bobot to urgent > high > normal
        if ($kategori === 'urgent') {
            $bobots = 3;
        } elseif ($kategori === 'high') {
            $bobots = 2;
        } else {
            $bobots = 1;
        }

        $selisihHari = Carbon::now()->diffInDays(Carbon::parse($jadwal->tanggal), false);
        if ($selisihHari < 0) {
            $selisihHari = 0;
        }

        $faktorKeterlambatan = $this->computeDelayFactor($jadwal);

        return ($bobots / ($selisihHari + 1)) + $faktorKeterlambatan;
    }

    // Contoh: jika ada status_log Pending lebih dari 2 jam, tambahkan 0.5
    protected function computeDelayFactor(JadwalPengiriman $jadwal): float
    {
        $pengiriman = $jadwal->pengirimans()->first();
        if (! $pengiriman) {
            return 0.0;
        }

        $pendingLog = StatusLog::where('id_pengiriman', $pengiriman->id_pengiriman)
            ->where('status_update', 'Pending')
            ->orderBy('waktu_pending', 'desc')
            ->first();

        if (! $pendingLog || ! $pendingLog->waktu_pending) {
            return 0.0;
        }

        $diffHours = Carbon::now()->diffInHours(Carbon::parse($pendingLog->waktu_pending));

        return $diffHours >= 2 ? 0.5 : 0.0;
    }

    // Recalculate nilai_bobot untuk semua jadwal aktif
    public function recalcAll()
    {
        $jadwals = JadwalPengiriman::whereIn('status', ['Terjadwal', 'Proses', 'Pending'])->get();
        foreach ($jadwals as $j) {
            $j->nilai_bobot = $this->calculateWeight($j);
            $j->save();
        }
    }

    // Recalculate progress_tujuan_persen for a specific id_tujuan and tanggal
    public function recalcProgressForGroup(int $id_tujuan, $tanggal)
    {
        $group = JadwalPengiriman::where('id_tujuan', $id_tujuan)->where('tanggal', $tanggal)->get();
        if ($group->count() === 0) {
            return;
        }

        $total = $group->count();
        $selesai = $group->where('status', 'Selesai')->count();
        $percent = (int) round(($selesai / $total) * 100);

        foreach ($group as $g) {
            $g->progress_tujuan_persen = $percent;
            $g->save();
        }
    }
}
