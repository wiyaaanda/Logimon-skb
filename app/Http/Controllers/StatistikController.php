<?php

namespace App\Http\Controllers;

use App\Models\JadwalPengiriman;
use App\Models\Material;
use App\Models\Pengiriman;
use App\Models\StatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatistikController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'month'); // day|month|year
        $date = $request->get('date', date('Y-m-d'));

        $queryJadwal = JadwalPengiriman::query();
        $queryPengiriman = Pengiriman::query();

        // apply period filter
        if ($period === 'day') {
            $queryJadwal->whereDate('tanggal', $date);
            $queryPengiriman->whereDate('created_at', $date);
        } elseif ($period === 'year') {
            $year = date('Y', strtotime($date));
            $queryJadwal->whereYear('tanggal', $year);
            $queryPengiriman->whereYear('created_at', $year);
        } else { // month
            $year = date('Y', strtotime($date));
            $month = date('m', strtotime($date));
            $queryJadwal->whereYear('tanggal', $year)->whereMonth('tanggal', $month);
            $queryPengiriman->whereYear('created_at', $year)->whereMonth('created_at', $month);
        }

        $totalPengiriman = $queryPengiriman->count();

        $driverAktif = $queryJadwal->distinct('id_driver')->whereNotNull('id_driver')->count('id_driver');

        $statusCounts = $queryPengiriman->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status')->toArray();

        // Top Material (by count in jadwal)
        $topMaterials = $queryJadwal->select('id_material', DB::raw('count(*) as total'))
            ->groupBy('id_material')->orderByDesc('total')->limit(5)->get();

        $materialLabels = [];
        $materialData = [];
        foreach ($topMaterials as $m) {
            $mat = Material::find($m->id_material);
            $materialLabels[] = $mat?->nama_material ?? 'Unknown';
            $materialData[] = (int) $m->total;
        }

        // On-Time Delivery Rate: compare jadwal.tanggal vs status_log.waktu_penyelesaian where status_update=Selesai
        $selesaiLogs = StatusLog::where('status_update', 'Selesai');
        if ($period === 'day') {
            $selesaiLogs->whereDate('waktu_penyelesaian', $date);
        } elseif ($period === 'year') {
            $year = date('Y', strtotime($date));
            $selesaiLogs->whereYear('waktu_penyelesaian', $year);
        } else {
            $year = date('Y', strtotime($date));
            $month = date('m', strtotime($date));
            $selesaiLogs->whereYear('waktu_penyelesaian', $year)->whereMonth('waktu_penyelesaian', $month);
        }

        $totalSelesai = $selesaiLogs->count();

        // join to jadwal to compare dates
        $onTimeCount = StatusLog::where('status_update', 'Selesai')
            ->join('pengiriman', 'status_log.id_pengiriman', '=', 'pengiriman.id_pengiriman')
            ->join('jadwal_pengiriman', 'pengiriman.id_jadwal', '=', 'jadwal_pengiriman.id_jadwal')
            ->whereRaw('DATE(status_log.waktu_penyelesaian) = jadwal_pengiriman.tanggal');

        if ($period === 'day') {
            $onTimeCount->whereDate('status_log.waktu_penyelesaian', $date);
        } elseif ($period === 'year') {
            $year = date('Y', strtotime($date));
            $onTimeCount->whereYear('status_log.waktu_penyelesaian', $year);
        } else {
            $year = date('Y', strtotime($date));
            $month = date('m', strtotime($date));
            $onTimeCount->whereYear('status_log.waktu_penyelesaian', $year)->whereMonth('status_log.waktu_penyelesaian', $month);
        }

        $onTime = $onTimeCount->count();
        $onTimeRate = $totalSelesai > 0 ? round(($onTime / $totalSelesai) * 100, 2) : 0;

        return view('statistik.index', compact('totalPengiriman', 'driverAktif', 'statusCounts', 'materialLabels', 'materialData', 'onTimeRate', 'period', 'date'));
    }
}
