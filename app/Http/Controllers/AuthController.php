<?php

namespace App\Http\Controllers;

use App\Models\AkunKelola;
use App\Models\Driver;
use App\Models\JadwalPengiriman;
use App\Models\Material;
use App\Models\Pengiriman;
use App\Models\StatusLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate(['username' => 'required', 'password' => 'required']);

        $user = User::where('username', $request->username)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            // try driver login
            $driver = Driver::where('username', $request->username)->first();
            if (! $driver || ! Hash::check($request->password, $driver->password)) {
                return back()->withErrors(['username' => 'Credentials invalid']);
            }

            // set driver session
            session(['driver_id' => $driver->id_driver]);
            session(['role' => 'driver']);

            return redirect()->route('dashboard');
        }

        // Simple session auth
        session(['user_id' => $user->id_user]);

        // Determine role: if user has akun_kelola with id_driver -> Driver, else Admin
        $akun = AkunKelola::where('id_user', $user->id_user)->first();
        $role = ($akun && ($akun->id_driver || $akun->status_driver === 'driver')) ? 'driver' : 'admin';
        session(['role' => $role]);

        return redirect()->route('dashboard');
    }

    public function logout()
    {
        session()->forget(['user_id', 'role', 'driver_id']);

        return redirect()->route('welcome');
    }

    public function dashboard()
    {
        $role = session('role');
        if ($role === 'driver') {
            return redirect()->route('driver.jadwal');
        }

        // Admin dashboard: filter by month and year
        $maxDate = JadwalPengiriman::max('tanggal') ?: date('Y-m-d');
        $defaultMonth = (int) date('n', strtotime($maxDate));
        $defaultYear = (int) date('Y', strtotime($maxDate));

        $selectedMonth = (int) request()->get('month', request()->filled('date') ? date('n', strtotime(request()->get('date'))) : $defaultMonth);
        $selectedYear = (int) request()->get('year', request()->filled('date') ? date('Y', strtotime(request()->get('date'))) : $defaultYear);

        if ($selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = $defaultMonth;
        }
        if ($selectedYear < 2020 || $selectedYear > 2035) {
            $selectedYear = $defaultYear;
        }

        $date = sprintf('%04d-%02d-01', $selectedYear, $selectedMonth);
        $period = 'month';
        $jadwalFilter = request()->get('jadwal_filter', 'semua'); // hari_ini, besok, kemarin, semua

        $queryJadwal = JadwalPengiriman::whereYear('tanggal', $selectedYear)->whereMonth('tanggal', $selectedMonth);
        $queryPengiriman = Pengiriman::whereHas('jadwal', fn ($query) => $query->whereYear('tanggal', $selectedYear)->whereMonth('tanggal', $selectedMonth));

        // Priority statistics for the selected month & year
        $priorityCounts = (clone $queryJadwal)
            ->select('priority', DB::raw('count(*) as total'))
            ->groupBy('priority')
            ->pluck('total', 'priority')
            ->toArray();

        $totalUrgent = (int) ($priorityCounts['urgent'] ?? 0);
        $totalHigh = (int) ($priorityCounts['high'] ?? 0);
        $totalNormal = (int) ($priorityCounts['normal'] ?? 0);
        $priorityLabels = ['Urgent', 'High', 'Normal'];
        $priorityData = [$totalUrgent, $totalHigh, $totalNormal];

        // Apply jadwal date filter
        $queryJadwalForFilter = clone $queryJadwal;
        if ($jadwalFilter === 'hari_ini') {
            $queryJadwalForFilter->whereDate('tanggal', today());
        } elseif ($jadwalFilter === 'besok') {
            $queryJadwalForFilter->whereDate('tanggal', today()->addDay());
        } elseif ($jadwalFilter === 'kemarin') {
            $queryJadwalForFilter->whereDate('tanggal', today()->subDay());
        }
        // if semua, no additional filter

        $items = $queryJadwalForFilter
            ->with(['driver', 'tujuan', 'material'])
            ->orderByRaw("CASE WHEN priority='urgent' THEN 2 WHEN priority='high' THEN 1 ELSE 0 END DESC")
            ->orderBy('tanggal', 'desc')
            ->limit(15)
            ->get();
        $totalPengiriman = $queryPengiriman->count();
        $totalSuratMasuk = (clone $queryPengiriman)
            ->whereNotNull('no_rec')
            ->where('no_rec', '<>', '')
            ->distinct('no_rec')
            ->count('no_rec');
        $driverAktif = (clone $queryJadwal)->distinct('id_driver')->whereNotNull('id_driver')->count('id_driver');
        $statusCounts = (clone $queryPengiriman)
            ->select('pengiriman.status', DB::raw('count(*) as total'))
            ->groupBy('pengiriman.status')
            ->pluck('total', 'status')
            ->toArray();

        $topMaterials = (clone $queryJadwal)->select('id_material', DB::raw('count(*) as total'))
            ->groupBy('id_material')->orderByDesc('total')->limit(5)->get();

        $materialLabels = [];
        $materialData = [];
        foreach ($topMaterials as $m) {
            $mat = Material::find($m->id_material);
            $materialLabels[] = $mat?->nama_material ?? 'Unknown';
            $materialData[] = (int) $m->total;
        }

        $topTimbangan = (clone $queryPengiriman)
            ->join('jadwal_pengiriman', 'pengiriman.id_jadwal', '=', 'jadwal_pengiriman.id_jadwal')
            ->leftJoin('material', 'jadwal_pengiriman.id_material', '=', 'material.id_material')
            ->select('jadwal_pengiriman.id_material', 'material.nama_material', DB::raw('SUM(COALESCE(pengiriman.timbangan_cv_sr, pengiriman.total_timbangan_cv_sr, 0)) as total_timbangan'))
            ->groupBy('jadwal_pengiriman.id_material', 'material.nama_material')
            ->orderByDesc('total_timbangan')
            ->limit(5)
            ->get();
        $timbanganLabels = $topTimbangan->map(fn ($row) => $row->nama_material ?? 'Unknown')->values()->all();
        $timbanganData = $topTimbangan->map(fn ($row) => round((float) $row->total_timbangan, 2))->values()->all();

        $trendQuery = DB::table('pengiriman')
            ->leftJoin('jadwal_pengiriman', 'pengiriman.id_jadwal', '=', 'jadwal_pengiriman.id_jadwal')
            ->selectRaw('DATE(COALESCE(jadwal_pengiriman.tanggal, pengiriman.created_at)) as label')
            ->selectRaw('SUM(COALESCE(pengiriman.vol_kg, pengiriman.total_volume_kg, 0)) as volume')
            ->selectRaw('SUM(COALESCE(pengiriman.timbangan_cv_sr, pengiriman.total_timbangan_cv_sr, 0)) as timbangan')
            ->whereYear(DB::raw('COALESCE(jadwal_pengiriman.tanggal, pengiriman.created_at)'), $selectedYear)
            ->whereMonth(DB::raw('COALESCE(jadwal_pengiriman.tanggal, pengiriman.created_at)'), $selectedMonth)
            ->groupByRaw('DATE(COALESCE(jadwal_pengiriman.tanggal, pengiriman.created_at))')
            ->orderBy('label');

        $trendRows = $trendQuery->get();
        $trendLabels = $trendRows->pluck('label')->values()->all();
        $volumeTrendData = $trendRows->map(fn ($row) => round((float) $row->volume, 2))->values()->all();
        $timbanganTrendData = $trendRows->map(fn ($row) => round((float) $row->timbangan, 2))->values()->all();

        $statusLabels = array_keys($statusCounts);
        $statusData = array_values($statusCounts);

        $selesaiLogs = StatusLog::where('status_update', 'Selesai')
            ->whereYear('waktu_penyelesaian', $selectedYear)
            ->whereMonth('waktu_penyelesaian', $selectedMonth);

        $totalSelesai = $selesaiLogs->count();

        $onTimeCount = StatusLog::where('status_update', 'Selesai')
            ->join('pengiriman', 'status_log.id_pengiriman', '=', 'pengiriman.id_pengiriman')
            ->join('jadwal_pengiriman', 'pengiriman.id_jadwal', '=', 'jadwal_pengiriman.id_jadwal')
            ->whereRaw('DATE(status_log.waktu_penyelesaian) = jadwal_pengiriman.tanggal')
            ->whereYear('status_log.waktu_penyelesaian', $selectedYear)
            ->whereMonth('status_log.waktu_penyelesaian', $selectedMonth);

        $onTime = $onTimeCount->count();
        $onTimeRate = $totalSelesai > 0 ? round(($onTime / $totalSelesai) * 100, 2) : 0;

        return view('dashboard.admin', compact(
            'items', 'totalPengiriman', 'totalSuratMasuk', 'driverAktif',
            'statusCounts', 'statusLabels', 'statusData',
            'materialLabels', 'materialData', 'timbanganLabels', 'timbanganData',
            'trendLabels', 'volumeTrendData', 'timbanganTrendData', 'onTimeRate',
            'period', 'date', 'jadwalFilter', 'selectedMonth', 'selectedYear',
            'totalUrgent', 'totalHigh', 'totalNormal', 'priorityLabels', 'priorityData'
        ));
    }
}
