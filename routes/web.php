<?php

use App\Http\Controllers\AkunKelolaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DataOperasionalController;
use App\Http\Controllers\DataTimbanganController;
use App\Http\Controllers\DataVolumeController;
use App\Http\Controllers\JadwalPengirimanController;
use App\Http\Controllers\Master\AsalController;
use App\Http\Controllers\Master\DriverController;
use App\Http\Controllers\Master\ImportController;
use App\Http\Controllers\Master\KategoriMaterialController;
use App\Http\Controllers\Master\KendaraanController;
use App\Http\Controllers\Master\MaterialController;
use App\Http\Controllers\Master\TransporterController;
use App\Http\Controllers\Master\TujuanController;
use App\Http\Controllers\RekapPengirimanController;
use App\Http\Controllers\StatusLogController;
use App\Models\JadwalPengiriman;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Carbon;

Route::get('/', function () {
    $today = Carbon::today();
    $todaySchedules = JadwalPengiriman::with('pengirimans')
        ->whereDate('tanggal', $today)
        ->get();

    $operationalSummary = [
        'total' => $todaySchedules->count(),
        'proses' => $todaySchedules->where('status', 'Proses')->count(),
        'selesai' => $todaySchedules->where('status', 'Selesai')->count(),
        'pending' => $todaySchedules->where('status', 'Pending')->count(),
    ];

    $recentSchedules = $todaySchedules
        ->sortByDesc('id_jadwal')
        ->take(3)
        ->map(function (JadwalPengiriman $schedule): array {
            $delivery = $schedule->pengirimans->sortByDesc('id_pengiriman')->first();

            return [
                'reference' => $schedule->no_rec ?: $delivery?->no_rec ?: 'Jadwal #'.$schedule->id_jadwal,
                'status' => $schedule->status ?: 'Terjadwal',
            ];
        });

    return view('welcome', compact('operationalSummary', 'recentSchedules', 'today'));
})->name('welcome');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware([])->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
});

// Master data routes
Route::prefix('master')->name('master.')->group(function () {
    Route::post('{type}/import/preview', [ImportController::class, 'preview'])->name('import.preview');
    Route::post('{type}/import/execute', [ImportController::class, 'import'])->name('import.execute');
    Route::get('transporter/download-template', [TransporterController::class, 'downloadTemplate'])->name('transporter.downloadTemplate');
    Route::post('transporter/import', [TransporterController::class, 'import'])->name('transporter.import');
    Route::get('transporter/export', [TransporterController::class, 'export'])->name('transporter.export');
    Route::resource('transporter', TransporterController::class)->parameters(['transporter' => 'transporter']);

    Route::get('driver/download-template', [DriverController::class, 'downloadTemplate'])->name('driver.downloadTemplate');
    Route::post('driver/import', [DriverController::class, 'import'])->name('driver.import');
    Route::get('driver/export', [DriverController::class, 'export'])->name('driver.export');
    Route::resource('driver', DriverController::class)->parameters(['driver' => 'driver']);

    Route::get('kendaraan/download-template', [KendaraanController::class, 'downloadTemplate'])->name('kendaraan.downloadTemplate');
    Route::post('kendaraan/import', [KendaraanController::class, 'import'])->name('kendaraan.import');
    Route::get('kendaraan/export', [KendaraanController::class, 'export'])->name('kendaraan.export');
    Route::resource('kendaraan', KendaraanController::class)->parameters(['kendaraan' => 'kendaraan']);

    Route::get('kategori_material/download-template', [KategoriMaterialController::class, 'downloadTemplate'])->name('kategori_material.downloadTemplate');
    Route::post('kategori_material/import', [KategoriMaterialController::class, 'import'])->name('kategori_material.import');
    Route::get('kategori_material/export', [KategoriMaterialController::class, 'export'])->name('kategori_material.export');
    Route::resource('kategori_material', KategoriMaterialController::class)->parameters(['kategori_material' => 'kategori_material']);

    Route::get('material/download-template', [MaterialController::class, 'downloadTemplate'])->name('material.downloadTemplate');
    Route::post('material/import', [MaterialController::class, 'import'])->name('material.import');
    Route::get('material/export', [MaterialController::class, 'export'])->name('material.export');
    Route::resource('material', MaterialController::class)->parameters(['material' => 'material']);

    Route::get('asal/download-template', [AsalController::class, 'downloadTemplate'])->name('asal.downloadTemplate');
    Route::post('asal/import', [AsalController::class, 'import'])->name('asal.import');
    Route::get('asal/export', [AsalController::class, 'export'])->name('asal.export');
    Route::resource('asal', AsalController::class)->parameters(['asal' => 'asal']);

    Route::get('tujuan/download-template', [TujuanController::class, 'downloadTemplate'])->name('tujuan.downloadTemplate');
    Route::post('tujuan/import', [TujuanController::class, 'import'])->name('tujuan.import');
    Route::get('tujuan/export', [TujuanController::class, 'export'])->name('tujuan.export');
    Route::resource('tujuan', TujuanController::class)->parameters(['tujuan' => 'tujuan']);
});

// Kelola Akun
Route::prefix('akun-kelola')->name('akun_kelola.')->group(function () {
    Route::get('/', [AkunKelolaController::class, 'index'])->name('index');
    Route::get('/history', [AkunKelolaController::class, 'history'])->name('history');

    // Admin routes
    Route::get('/admin/create', [AkunKelolaController::class, 'createAdmin'])->name('createAdmin');
    Route::post('/admin', [AkunKelolaController::class, 'storeAdmin'])->name('storeAdmin');
    Route::get('/admin/{user}/edit', [AkunKelolaController::class, 'editAdmin'])->name('editAdmin');
    Route::put('/admin/{user}', [AkunKelolaController::class, 'updateAdmin'])->name('updateAdmin');
    Route::post('/admin/{user}/reset-password', [AkunKelolaController::class, 'resetPasswordAdmin'])->name('resetPasswordAdmin');

    // Driver routes
    Route::get('/driver/create', [AkunKelolaController::class, 'createDriver'])->name('createDriver');
    Route::post('/driver', [AkunKelolaController::class, 'storeDriver'])->name('storeDriver');
    Route::get('/driver/{driver}/edit', [AkunKelolaController::class, 'editDriver'])->name('editDriver');
    Route::put('/driver/{driver}', [AkunKelolaController::class, 'updateDriver'])->name('updateDriver');
    Route::post('/driver/{driver}/reset-password', [AkunKelolaController::class, 'resetPasswordDriver'])->name('resetPasswordDriver');
    Route::post('/driver/{driver}/update-status', [AkunKelolaController::class, 'updateStatusDriver'])->name('updateStatusDriver');

    // Delete route
    Route::delete('/destroy', [AkunKelolaController::class, 'destroy'])->name('destroy');
});

// Jadwal Pengiriman
Route::get('jadwal/import/template', [ImportController::class, 'template'])->name('jadwal.import.template');
Route::post('jadwal/import/preview', [ImportController::class, 'preview'])->name('jadwal.import.preview');
Route::post('jadwal/import/execute', [ImportController::class, 'import'])->name('jadwal.import.execute');
Route::post('jadwal/bulk-delete', [JadwalPengirimanController::class, 'bulkDestroy'])->name('jadwal.bulkDestroy');
Route::resource('jadwal', JadwalPengirimanController::class);

// Statistik moved into admin dashboard; standalone route removed.

// Data Operasional (derived from pengiriman history)
Route::get('/operasional', [DataOperasionalController::class, 'index'])->name('operasional.index');
Route::get('/operasional/export', [DataOperasionalController::class, 'export'])->name('operasional.export');

// Data Volume & Timbangan
Route::get('/data-volume', [DataVolumeController::class, 'index'])->name('data_volume.index');
Route::get('/data-volume/export', [DataVolumeController::class, 'export'])->name('data_volume.export');
Route::get('/data-timbangan', [DataTimbanganController::class, 'index'])->name('data_timbangan.index');
Route::get('/data-timbangan/export', [DataTimbanganController::class, 'export'])->name('data_timbangan.export');
// Status Log
Route::get('/status-log', [StatusLogController::class, 'index'])->name('status_log.index');
Route::get('/status-log/{id}', [StatusLogController::class, 'show'])->name('status_log.show');

// Rekap Pengiriman
Route::get('/rekap', [RekapPengirimanController::class, 'index'])->name('rekap.index');
Route::get('/rekap/export', [RekapPengirimanController::class, 'export'])->name('rekap.export');
Route::get('/rekap/pdf', [RekapPengirimanController::class, 'pdf'])->name('rekap.pdf');

// Driver-facing pages
Route::get('/driver/jadwal', [JadwalPengirimanController::class, 'driverJadwal'])->name('driver.jadwal');
Route::get('/driver/update-status', [JadwalPengirimanController::class, 'driverStatus'])->name('driver.status');
Route::post('/driver/update-status', [JadwalPengirimanController::class, 'driverStatusUpdate'])->name('driver.status.update');
