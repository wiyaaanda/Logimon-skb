<?php

namespace App\Http\Controllers;

use App\Models\Asal;
use App\Models\DataTimbangan;
use App\Models\DataVolume;
use App\Models\Driver;
use App\Models\JadwalPengiriman;
use App\Models\KategoriMaterial;
use App\Models\Kendaraan;
use App\Models\Material;
use App\Models\Pengiriman;
use App\Models\StatusLog;
use App\Models\Transporter;
use App\Models\Tujuan;
use App\Models\User;
use App\Services\PriorityQueueService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class JadwalPengirimanController extends Controller
{
    protected $pq;

    public function __construct(PriorityQueueService $pq)
    {
        $this->pq = $pq;
    }

    private function materialCodeFromCategory(?int $categoryId): ?string
    {
        if (! $categoryId) {
            return null;
        }

        return KategoriMaterial::whereKey($categoryId)->value('code_material');
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $perPage = (int) $request->get('per_page', 25);
        if ($perPage < 10 || $perPage > 100) {
            $perPage = 25;
        }

        // Order by urgency priority: Mendesak (highest), Prioritas, then others; within same priority order by tanggal desc
        $items = JadwalPengiriman::with(['driver', 'kendaraan', 'transporter', 'asal', 'tujuan', 'material', 'pengirimans'])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($row) use ($q) {
                    $row->where('no_rec', 'like', "%{$q}%")
                        ->orWhere('code_material', 'like', "%{$q}%")
                        ->orWhere('status', 'like', "%{$q}%")
                        ->orWhere('priority', 'like', "%{$q}%")
                        ->orWhere('tanggal', 'like', "%{$q}%")
                        ->orWhereRaw("DATE_FORMAT(tanggal, '%d-%m-%Y') LIKE ?", ["%{$q}%"])
                        ->orWhereRaw("DATE_FORMAT(tanggal, '%d/%m/%Y') LIKE ?", ["%{$q}%"])
                        ->orWhereRaw("DATE_FORMAT(tanggal, '%d %M %Y') LIKE ?", ["%{$q}%"])
                        ->orWhereHas('driver', function ($driverQuery) use ($q) {
                            $driverQuery->where('nama_driver', 'like', "%{$q}%");
                        })
                        ->orWhereHas('tujuan', function ($tujuanQuery) use ($q) {
                            $tujuanQuery->where('nama_tujuan', 'like', "%{$q}%");
                        })
                        ->orWhereHas('asal', function ($asalQuery) use ($q) {
                            $asalQuery->where('nama_asal', 'like', "%{$q}%");
                        })
                        ->orWhereHas('transporter', function ($tQuery) use ($q) {
                            $tQuery->where('nama_transporter', 'like', "%{$q}%");
                        })
                        ->orWhereHas('kendaraan', function ($kQuery) use ($q) {
                            $kQuery->where('no_pol', 'like', "%{$q}%");
                        })
                        ->orWhereHas('material', function ($materialQuery) use ($q) {
                            $materialQuery->where('nama_material', 'like', "%{$q}%");
                        });

                    // Search by Indonesian Day Name (e.g. Senin, Selasa, Rabu, Kamis, Jumat, Sabtu, Minggu)
                    $dayMap = [
                        'senin' => 0, 'selasa' => 1, 'rabu' => 2, 'kamis' => 3,
                        'jumat' => 4, "jum'at" => 4, 'sabtu' => 5, 'minggu' => 6, 'ahad' => 6,
                    ];
                    $qLower = strtolower($q);
                    foreach ($dayMap as $dayName => $weekdayVal) {
                        if (str_contains($qLower, $dayName)) {
                            $row->orWhereRaw('WEEKDAY(tanggal) = ?', [$weekdayVal]);
                        }
                    }

                    // Search by Indonesian Month Name
                    $monthMap = [
                        'januari' => 1, 'jan' => 1,
                        'februari' => 2, 'feb' => 2,
                        'maret' => 3, 'mar' => 3,
                        'april' => 4, 'apr' => 4,
                        'mei' => 5,
                        'juni' => 6, 'jun' => 6,
                        'juli' => 7, 'jul' => 7,
                        'agustus' => 8, 'agt' => 8, 'agu' => 8,
                        'september' => 9, 'sep' => 9,
                        'oktober' => 10, 'okt' => 10,
                        'november' => 11, 'nov' => 11,
                        'desember' => 12, 'des' => 12,
                    ];
                    foreach ($monthMap as $monthName => $monthVal) {
                        if (str_contains($qLower, $monthName)) {
                            $row->orWhereRaw('MONTH(tanggal) = ?', [$monthVal]);
                        }
                    }

                    // If user enters DD/MM/YYYY or DD-MM-YYYY format
                    if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $q, $matches)) {
                        $parsedDate = sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
                        $row->orWhereDate('tanggal', $parsedDate);
                    }
                });
            })
            ->orderByRaw("CASE WHEN priority='urgent' THEN 2 WHEN priority='high' THEN 1 ELSE 0 END DESC")
            ->orderBy('tanggal', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('jadwal.index', compact('items', 'q', 'perPage'));
    }

    // Driver-facing jadwal list with quick filters (today/kemarin/besok/semua)
    public function driverJadwal(Request $request)
    {
        $filter = $request->get('filter', 'all');

        $query = JadwalPengiriman::with(['pengirimans', 'driver', 'tujuan', 'material'])
            ->whereNotNull('id_jadwal');

        if ($filter === 'today') {
            $query->whereDate('tanggal', today());
        } elseif ($filter === 'yesterday') {
            $query->whereDate('tanggal', today()->subDay());
        } elseif ($filter === 'tomorrow') {
            $query->whereDate('tanggal', today()->addDay());
        }

        $items = $query->orderBy('tanggal', 'desc')->paginate(20)->appends(['filter' => $filter]);

        return view('driver.jadwal', compact('items', 'filter'));
    }

    // Driver update-status page
    public function driverStatus(Request $request)
    {
        $filter = $request->get('filter', 'all');

        $items = JadwalPengiriman::with(['pengirimans', 'driver', 'tujuan', 'material'])
            ->whereNotNull('id_jadwal')
            ->when($filter === 'today', fn ($query) => $query->whereDate('tanggal', today()))
            ->when($filter === 'yesterday', fn ($query) => $query->whereDate('tanggal', today()->subDay()))
            ->when($filter === 'tomorrow', fn ($query) => $query->whereDate('tanggal', today()->addDay()))
            ->orderBy('tanggal', 'desc')
            ->paginate(20)
            ->appends(['filter' => $filter]);

        return view('driver.jadwal', compact('items', 'filter'));
    }

    public function driverStatusUpdate(Request $request)
    {
        $data = $request->validate([
            'id_jadwal' => 'required|integer',
            'status' => 'required|in:Proses,Selesai,Pending',
            'catatan' => 'nullable|string',
        ]);

        $jadwal = JadwalPengiriman::findOrFail($data['id_jadwal']);
        $old = $jadwal->status;
        $jadwal->status = $data['status'];
        $jadwal->save();

        // create or update pengiriman and status log
        $pengiriman = $jadwal->pengirimans()->first();
        if (! $pengiriman) {
            $pengiriman = Pengiriman::create(['id_jadwal' => $jadwal->id_jadwal, 'status' => $data['status']]);
        } else {
            $pengiriman->status = $data['status'];
            $pengiriman->save();
        }

        $user = null;
        if (session('user_id')) {
            $user = User::find(session('user_id'));
        }
        $actingDriver = null;
        if (session('driver_id')) {
            $actingDriver = Driver::find(session('driver_id'));
        }
        $log = new StatusLog;
        $log->id_pengiriman = $pengiriman->id_pengiriman;
        $log->id_user = $user?->id_user;
        $log->driver = $jadwal->driver?->nama_driver;
        // username should reflect the actor: admin user or driver
        $log->username = $user?->username ?? $actingDriver?->username;
        $log->dari = $jadwal->asal?->nama_asal;
        $log->tujuan = $jadwal->tujuan?->nama_tujuan;
        $log->material = $jadwal->material?->nama_material;
        if ($data['status'] === 'Pending') {
            $log->waktu_pending = Carbon::now();
            $log->keterangan_log = $data['catatan'] ?? null;
        }
        if ($data['status'] === 'Selesai') {
            $log->waktu_penyelesaian = Carbon::now();
        }
        $log->status_update = $data['status'];
        $log->save();

        // recalc progress/weight
        $this->pq->recalcAll();
        if ($jadwal->id_tujuan && $jadwal->tanggal) {
            $this->pq->recalcProgressForGroup($jadwal->id_tujuan, $jadwal->tanggal);
        }

        return back()->with('success', 'Status updated');
    }

    public function create()
    {
        $drivers = Driver::query()->orderBy('nama_driver')->get();
        $kendaraans = Kendaraan::all();
        $transporters = Transporter::all();
        $asals = Asal::all();
        $tujuans = Tujuan::all();
        $materials = Material::all();
        $kategoriMaterials = KategoriMaterial::all();

        $measurement = null;

        return view('jadwal.form', compact('drivers', 'kendaraans', 'transporters', 'asals', 'tujuans', 'materials', 'kategoriMaterials', 'measurement'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'vol_kg' => $this->normalizeMeasurement($request->input('vol_kg')),
            'timbangan_cv_sr' => $this->normalizeMeasurement($request->input('timbangan_cv_sr')),
        ]);

        $data = $request->validate([
            'tanggal' => 'required|date',
            'id_driver' => 'nullable|integer|exists:driver,id_driver',
            'id_kendaraan' => 'nullable|integer',
            'id_transporter' => 'nullable|integer',
            'id_asal' => 'nullable|integer',
            'id_tujuan' => 'nullable|integer',
            'id_material' => 'nullable|integer',
            'id_kategori_material' => 'nullable|integer|exists:kategori_material,id_kategori_material',
            'code_material' => 'nullable|string',
            'no_rec' => 'nullable|string',
            'jam_berangkat' => 'nullable|date_format:H:i',
            'vol_kg' => 'nullable|numeric',
            'timbangan_cv_sr' => 'nullable|numeric',
            'no_pol' => 'nullable|string',
            'status' => 'nullable|in:Terjadwal,Proses,Selesai,Pending',
            'priority' => 'nullable|in:urgent,high,normal',
        ]);

        // Map selected no_pol to id_kendaraan if present
        if (! empty($data['no_pol']) && empty($data['id_kendaraan'])) {
            $kend = Kendaraan::where('no_pol', $data['no_pol'])->first();
            if ($kend) {
                $data['id_kendaraan'] = $kend->id_kendaraan;
            }
        }

        $data['code_material'] = $this->materialCodeFromCategory($data['id_kategori_material'] ?? null);

        // Ensure delivery numeric fields are floats
        if (isset($data['vol_kg'])) {
            $data['vol_kg'] = (float) $data['vol_kg'];
        }
        if (isset($data['timbangan_cv_sr'])) {
            $data['timbangan_cv_sr'] = (float) $data['timbangan_cv_sr'];
        }

        // Ensure delivery numeric fields are floats
        if (isset($data['vol_kg'])) {
            $data['vol_kg'] = (float) $data['vol_kg'];
        }
        if (isset($data['timbangan_cv_sr'])) {
            $data['timbangan_cv_sr'] = (float) $data['timbangan_cv_sr'];
        }

        if (! isset($data['priority']) || $data['priority'] === '') {
            $data['priority'] = 'normal';
        }
        if (! isset($data['status']) || $data['status'] === '') {
            $data['status'] = 'Terjadwal';
        }
        $data['progress_tujuan_persen'] = $this->progressForStatus($data['status']);
        $jadwal = JadwalPengiriman::create($data + ['nilai_bobot' => 0, 'progress_tujuan_persen' => 0]);
        // calculate initial weight
        $jadwal->nilai_bobot = $this->pq->calculateWeight($jadwal);
        $jadwal->save();

        // If delivery measurements provided at creation, create pengiriman and history entries
        if (! empty($data['vol_kg']) || ! empty($data['timbangan_cv_sr'])) {
            $pengiriman = Pengiriman::create([
                'id_jadwal' => $jadwal->id_jadwal,
                'no_rec' => $data['no_rec'] ?? null,
                'jam_berangkat' => $data['jam_berangkat'] ?? null,
                'vol_kg' => $data['vol_kg'] ?? null,
                'total_volume_kg' => $data['vol_kg'] ?? null,
                'timbangan_cv_sr' => $data['timbangan_cv_sr'] ?? null,
                'total_timbangan_cv_sr' => $data['timbangan_cv_sr'] ?? null,
                'status' => $data['status'] ?? 'Terjadwal',
            ]);

            // create data_volume entry
            DataVolume::create([
                'id_pengiriman' => $pengiriman->id_pengiriman,
                'id_material' => $jadwal->id_material,
                'code_material' => $jadwal->code_material,
                'nama_material' => $jadwal->material?->nama_material,
                'satuan' => 'kg',
                'tanggal' => $jadwal->tanggal,
                'total_volume_kg' => $pengiriman->total_volume_kg ?? 0,
                'jumlah_pengantaran' => 1,
            ]);

            // create data_timbangan entry
            DataTimbangan::create([
                'id_pengiriman' => $pengiriman->id_pengiriman,
                'id_material' => $jadwal->id_material,
                'code_material' => $jadwal->code_material,
                'nama_material' => $jadwal->material?->nama_material,
                'satuan' => 'kg',
                'tanggal' => $jadwal->tanggal,
                'total_timbangan_cv_sr' => $pengiriman->total_timbangan_cv_sr ?? 0,
                'jumlah_pengantaran' => 1,
            ]);
        }

        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil ditambahkan');
    }

    public function edit(JadwalPengiriman $jadwal)
    {
        $drivers = Driver::query()->orderBy('nama_driver')->get();
        $kendaraans = Kendaraan::all();
        $transporters = Transporter::all();
        $asals = Asal::all();
        $tujuans = Tujuan::all();
        $materials = Material::all();
        $kategoriMaterials = KategoriMaterial::all();
        $measurement = $jadwal->pengirimans()->latest('id_pengiriman')->first();

        return view('jadwal.form', compact('jadwal', 'measurement', 'drivers', 'kendaraans', 'transporters', 'asals', 'tujuans', 'materials', 'kategoriMaterials'));
    }

    public function update(Request $request, JadwalPengiriman $jadwal)
    {
        $request->merge([
            'vol_kg' => $this->normalizeMeasurement($request->input('vol_kg')),
            'timbangan_cv_sr' => $this->normalizeMeasurement($request->input('timbangan_cv_sr')),
        ]);

        $data = $request->validate([
            'tanggal' => 'required|date',
            'id_driver' => 'nullable|integer|exists:driver,id_driver',
            'id_kendaraan' => 'nullable|integer',
            'id_transporter' => 'nullable|integer',
            'id_asal' => 'nullable|integer',
            'id_tujuan' => 'nullable|integer',
            'id_material' => 'nullable|integer',
            'id_kategori_material' => 'nullable|integer|exists:kategori_material,id_kategori_material',
            'code_material' => 'nullable|string',
            'no_rec' => 'nullable|string',
            'jam_berangkat' => 'nullable|date_format:H:i',
            'vol_kg' => 'nullable|numeric',
            'timbangan_cv_sr' => 'nullable|numeric',
            'no_pol' => 'nullable|string',
            'status' => 'nullable|in:Terjadwal,Proses,Selesai,Pending',
            'priority' => 'nullable|in:urgent,high,normal',
            'catatan' => 'nullable|string',
        ]);

        // Map selected no_pol to id_kendaraan if present
        if (! empty($data['no_pol']) && empty($data['id_kendaraan'])) {
            $kend = Kendaraan::where('no_pol', $data['no_pol'])->first();
            if ($kend) {
                $data['id_kendaraan'] = $kend->id_kendaraan;
            }
        }

        if (array_key_exists('id_kategori_material', $data)) {
            $data['code_material'] = $this->materialCodeFromCategory($data['id_kategori_material'] ?? null);
        } else {
            $data['code_material'] = $jadwal->code_material;
        }

        $oldStatus = $jadwal->status;
        if (! isset($data['priority']) || $data['priority'] === '') {
            $data['priority'] = 'normal';
        }
        if (! isset($data['status']) || $data['status'] === '') {
            $data['status'] = $jadwal->status ?: 'Terjadwal';
        }
        $data['progress_tujuan_persen'] = $this->progressForStatus($data['status']);
        $jadwal->update($data);

        // If vol_kg or timbangan present on update, sync pengiriman and history
        if (array_key_exists('vol_kg', $data) || array_key_exists('timbangan_cv_sr', $data)) {
            $pengiriman = $jadwal->pengirimans()->first();
            if (! $pengiriman) {
                $pengiriman = Pengiriman::create([
                    'id_jadwal' => $jadwal->id_jadwal,
                    'no_rec' => $data['no_rec'] ?? $jadwal->no_rec,
                    'jam_berangkat' => $data['jam_berangkat'] ?? $jadwal->jam_berangkat,
                    'vol_kg' => $data['vol_kg'] ?? $jadwal->vol_kg,
                    'total_volume_kg' => $data['vol_kg'] ?? $jadwal->vol_kg,
                    'timbangan_cv_sr' => $data['timbangan_cv_sr'] ?? $jadwal->timbangan_cv_sr,
                    'total_timbangan_cv_sr' => $data['timbangan_cv_sr'] ?? $jadwal->timbangan_cv_sr,
                    'status' => $data['status'] ?? $jadwal->status,
                ]);
            } else {
                $pengiriman->no_rec = $data['no_rec'] ?? $pengiriman->no_rec;
                $pengiriman->jam_berangkat = $data['jam_berangkat'] ?? $pengiriman->jam_berangkat;
                if (array_key_exists('vol_kg', $data)) {
                    $pengiriman->vol_kg = $data['vol_kg'];
                    $pengiriman->total_volume_kg = $data['vol_kg'];
                }
                if (array_key_exists('timbangan_cv_sr', $data)) {
                    $pengiriman->timbangan_cv_sr = $data['timbangan_cv_sr'];
                    $pengiriman->total_timbangan_cv_sr = $data['timbangan_cv_sr'];
                }
                $pengiriman->save();
            }

            // update or create data_volume
            $dv = DataVolume::where('id_pengiriman', $pengiriman->id_pengiriman)->first();
            if (! $dv) {
                DataVolume::create([
                    'id_pengiriman' => $pengiriman->id_pengiriman,
                    'id_material' => $jadwal->id_material,
                    'code_material' => $jadwal->code_material,
                    'nama_material' => $jadwal->material?->nama_material,
                    'satuan' => 'kg',
                    'tanggal' => $jadwal->tanggal,
                    'total_volume_kg' => $pengiriman->total_volume_kg ?? 0,
                    'jumlah_pengantaran' => 1,
                ]);
            } else {
                $dv->total_volume_kg = $pengiriman->total_volume_kg ?? $dv->total_volume_kg;
                $dv->code_material = $jadwal->code_material;
                $dv->tanggal = $jadwal->tanggal;
                $dv->save();
            }

            // update or create data_timbangan
            $dt = DataTimbangan::where('id_pengiriman', $pengiriman->id_pengiriman)->first();
            if (! $dt) {
                DataTimbangan::create([
                    'id_pengiriman' => $pengiriman->id_pengiriman,
                    'id_material' => $jadwal->id_material,
                    'code_material' => $jadwal->code_material,
                    'nama_material' => $jadwal->material?->nama_material,
                    'satuan' => 'kg',
                    'tanggal' => $jadwal->tanggal,
                    'total_timbangan_cv_sr' => $pengiriman->total_timbangan_cv_sr ?? 0,
                    'jumlah_pengantaran' => 1,
                ]);
            } else {
                $dt->total_timbangan_cv_sr = $pengiriman->total_timbangan_cv_sr ?? $dt->total_timbangan_cv_sr;
                $dt->code_material = $jadwal->code_material;
                $dt->tanggal = $jadwal->tanggal;
                $dt->save();
            }
        }

        // If status changed, create/update pengiriman and write status_log
        if (isset($data['status']) && $data['status'] !== $oldStatus) {
            $pengiriman = $jadwal->pengirimans()->first();
            if (! $pengiriman) {
                $pengiriman = Pengiriman::create(['id_jadwal' => $jadwal->id_jadwal, 'status' => $data['status']]);
            } else {
                $pengiriman->status = $data['status'];
                $pengiriman->save();
            }

            $user = null;
            if (session('user_id')) {
                $user = User::find(session('user_id'));
            }
            $actingDriver = null;
            if (session('driver_id')) {
                $actingDriver = Driver::find(session('driver_id'));
            }

            $log = new StatusLog;
            $log->id_pengiriman = $pengiriman->id_pengiriman;
            $log->id_user = $user?->id_user;
            $log->driver = $jadwal->driver?->nama_driver;
            $log->username = $user?->username ?? $actingDriver?->username;
            $log->dari = $jadwal->asal?->nama_asal;
            $log->tujuan = $jadwal->tujuan?->nama_tujuan;
            $log->material = $jadwal->material?->nama_material;
            if ($data['status'] === 'Pending') {
                $log->waktu_pending = Carbon::now();
            }
            if ($data['status'] === 'Selesai') {
                $log->waktu_penyelesaian = Carbon::now();
            }
            $log->status_update = $data['status'];
            $log->keterangan_log = $data['catatan'] ?? null;
            $log->save();

            // Recalculate weights and group progress
            $this->pq->recalcAll();
            if ($jadwal->id_tujuan && $jadwal->tanggal) {
                $this->pq->recalcProgressForGroup($jadwal->id_tujuan, $jadwal->tanggal);
            }
        } else {
            // if urgency changed, recalc weight for this row
            $jadwal->nilai_bobot = $this->pq->calculateWeight($jadwal);
            $jadwal->save();
        }

        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil diperbarui');
    }

    private function normalizeMeasurement(mixed $value): mixed
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $value = trim((string) $value);

        return str_replace('.', '', $value);
    }

    private function progressForStatus(string $status): int
    {
        return match ($status) {
            'Selesai' => 100,
            'Proses' => 50,
            default => 0,
        };
    }

    public function destroy(JadwalPengiriman $jadwal)
    {
        $jadwal->delete();

        return back()->with('success', 'Jadwal berhasil dihapus');
    }

    public function bulkDestroy(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:jadwal_pengiriman,id_jadwal'],
        ]);

        $deleted = JadwalPengiriman::whereIn('id_jadwal', $data['ids'])->delete();

        return response()->json(['deleted' => $deleted]);
    }
}
