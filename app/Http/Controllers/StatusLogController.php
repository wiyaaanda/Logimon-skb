<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class StatusLogController extends Controller
{
    public function index()
    {
        $period = request()->get('period', 'month');
        $date = request()->get('date');
        $q = trim((string) request()->get('q', ''));

        $rowsQuery = DB::table('status_log')
            ->leftJoin('pengiriman', 'status_log.id_pengiriman', '=', 'pengiriman.id_pengiriman')
            ->leftJoin('jadwal_pengiriman', 'pengiriman.id_jadwal', '=', 'jadwal_pengiriman.id_jadwal')
            ->leftJoin('user', 'status_log.id_user', '=', 'user.id_user')
            ->select(
                'status_log.id_log',
                'status_log.driver',
                'status_log.username',
                'status_log.dari',
                'status_log.tujuan as tujuan_field',
                'status_log.material',
                'jadwal_pengiriman.code_material',
                'pengiriman.no_rec as no_rec',
                'status_log.waktu_pending',
                'status_log.waktu_penyelesaian',
                'status_log.status_update',
                'status_log.keterangan_log',
                'status_log.created_at',
                'status_log.id_log as sort_id'
            );

        if ($q) {
            $rowsQuery->where(function ($query) use ($q) {
                $query->where('status_log.driver', 'like', "%{$q}%")
                    ->orWhere('status_log.username', 'like', "%{$q}%")
                    ->orWhere('status_log.dari', 'like', "%{$q}%")
                    ->orWhere('status_log.tujuan', 'like', "%{$q}%")
                    ->orWhere('status_log.material', 'like', "%{$q}%")
                    ->orWhere('jadwal_pengiriman.code_material', 'like', "%{$q}%")
                    ->orWhere('pengiriman.no_rec', 'like', "%{$q}%");
            });
        }

        // A status update can be recorded after the delivery date, so match both
        // the log timestamp and the delivery schedule date.
        if ($date && $period === 'day') {
            $rowsQuery->where(function ($query) use ($date) {
                $query->whereDate('status_log.created_at', $date)
                    ->orWhereDate('jadwal_pengiriman.tanggal', $date);
            });
        } elseif ($date && $period === 'year') {
            $year = date('Y', strtotime($date));
            $rowsQuery->where(function ($query) use ($year) {
                $query->whereYear('status_log.created_at', $year)
                    ->orWhereYear('jadwal_pengiriman.tanggal', $year);
            });
        } elseif ($date) {
            $year = date('Y', strtotime($date));
            $month = date('m', strtotime($date));
            $rowsQuery->where(function ($query) use ($year, $month) {
                $query->where(function ($query) use ($year, $month) {
                    $query->whereYear('status_log.created_at', $year)
                        ->whereMonth('status_log.created_at', $month);
                })->orWhere(function ($query) use ($year, $month) {
                    $query->whereYear('jadwal_pengiriman.tanggal', $year)
                        ->whereMonth('jadwal_pengiriman.tanggal', $month);
                });
            });
        }

        $completedFallbackQuery = DB::table('jadwal_pengiriman')
            ->leftJoin('pengiriman', 'jadwal_pengiriman.id_jadwal', '=', 'pengiriman.id_jadwal')
            ->leftJoin('driver', 'jadwal_pengiriman.id_driver', '=', 'driver.id_driver')
            ->leftJoin('asal', 'jadwal_pengiriman.id_asal', '=', 'asal.id_asal')
            ->leftJoin('tujuan', 'jadwal_pengiriman.id_tujuan', '=', 'tujuan.id_tujuan')
            ->leftJoin('material', 'jadwal_pengiriman.id_material', '=', 'material.id_material')
            ->select(
                DB::raw('NULL as id_log'),
                'driver.nama_driver as driver',
                DB::raw('NULL as username'),
                'asal.nama_asal as dari',
                'tujuan.nama_tujuan as tujuan_field',
                'material.nama_material as material',
                'jadwal_pengiriman.code_material',
                'pengiriman.no_rec as no_rec',
                DB::raw('NULL as waktu_pending'),
                DB::raw('NULL as waktu_penyelesaian'),
                'jadwal_pengiriman.status as status_update',
                DB::raw('NULL as keterangan_log'),
                'jadwal_pengiriman.updated_at as created_at',
                'jadwal_pengiriman.id_jadwal as sort_id'
            )
            ->where(function ($query) {
                $query->whereIn('jadwal_pengiriman.status', ['Terjadwal', 'Proses', 'Selesai', 'Pending'])
                    ->orWhereNull('jadwal_pengiriman.status');
            })
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('status_log')
                    ->whereColumn('status_log.id_pengiriman', 'pengiriman.id_pengiriman');
            });

        if ($q) {
            $completedFallbackQuery->where(function ($query) use ($q) {
                $query->where('driver.nama_driver', 'like', "%{$q}%")
                    ->orWhere('asal.nama_asal', 'like', "%{$q}%")
                    ->orWhere('tujuan.nama_tujuan', 'like', "%{$q}%")
                    ->orWhere('material.nama_material', 'like', "%{$q}%")
                    ->orWhere('jadwal_pengiriman.code_material', 'like', "%{$q}%")
                    ->orWhere('pengiriman.no_rec', 'like', "%{$q}%");
            });
        }

        if ($date && $period === 'day') {
            $completedFallbackQuery->whereDate('jadwal_pengiriman.tanggal', $date);
        } elseif ($date && $period === 'year') {
            $completedFallbackQuery->whereYear('jadwal_pengiriman.tanggal', date('Y', strtotime($date)));
        } elseif ($date) {
            $completedFallbackQuery->whereYear('jadwal_pengiriman.tanggal', date('Y', strtotime($date)))
                ->whereMonth('jadwal_pengiriman.tanggal', date('m', strtotime($date)));
        }

        $rows = $rowsQuery
            ->unionAll($completedFallbackQuery)
            ->orderByDesc('sort_id')
            ->paginate(15)
            ->withQueryString();

        // counts with same period filtering
        $statusLogQuery = fn () => DB::table('status_log')
            ->leftJoin('pengiriman', 'status_log.id_pengiriman', '=', 'pengiriman.id_pengiriman')
            ->leftJoin('jadwal_pengiriman', 'pengiriman.id_jadwal', '=', 'jadwal_pengiriman.id_jadwal');
        $totalQuery = $statusLogQuery();
        $pendingQuery = $statusLogQuery()->where('status_log.status_update', 'Pending');
        $prosesQuery = $statusLogQuery()->where('status_log.status_update', 'Proses');
        $selesaiQuery = $statusLogQuery()->where('status_log.status_update', 'Selesai');

        if ($date && $period === 'day') {
            foreach ([$totalQuery, $pendingQuery, $prosesQuery, $selesaiQuery] as $query) {
                $query->where(function ($query) use ($date) {
                    $query->whereDate('status_log.created_at', $date)
                        ->orWhereDate('jadwal_pengiriman.tanggal', $date);
                });
            }
        } elseif ($date && $period === 'year') {
            $year = date('Y', strtotime($date));
            foreach ([$totalQuery, $pendingQuery, $prosesQuery, $selesaiQuery] as $query) {
                $query->where(function ($query) use ($year) {
                    $query->whereYear('status_log.created_at', $year)
                        ->orWhereYear('jadwal_pengiriman.tanggal', $year);
                });
            }
        } elseif ($date) {
            $year = date('Y', strtotime($date));
            $month = date('m', strtotime($date));
            foreach ([$totalQuery, $pendingQuery, $prosesQuery, $selesaiQuery] as $query) {
                $query->where(function ($query) use ($year, $month) {
                    $query->where(function ($query) use ($year, $month) {
                        $query->whereYear('status_log.created_at', $year)
                            ->whereMonth('status_log.created_at', $month);
                    })->orWhere(function ($query) use ($year, $month) {
                        $query->whereYear('jadwal_pengiriman.tanggal', $year)
                            ->whereMonth('jadwal_pengiriman.tanggal', $month);
                    });
                });
            }
        }

        $total = $totalQuery->count();
        $pending = $pendingQuery->count();
        $proses = $prosesQuery->count();
        $selesai = $selesaiQuery->count();

        $completedFallbackCountQuery = DB::table('jadwal_pengiriman')
            ->leftJoin('pengiriman', 'jadwal_pengiriman.id_jadwal', '=', 'pengiriman.id_jadwal')
            ->where('jadwal_pengiriman.status', 'Selesai')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('status_log')
                    ->whereColumn('status_log.id_pengiriman', 'pengiriman.id_pengiriman');
            });

        if ($date && $period === 'day') {
            $completedFallbackCountQuery->whereDate('jadwal_pengiriman.tanggal', $date);
        } elseif ($date && $period === 'year') {
            $completedFallbackCountQuery->whereYear('jadwal_pengiriman.tanggal', date('Y', strtotime($date)));
        } elseif ($date) {
            $completedFallbackCountQuery->whereYear('jadwal_pengiriman.tanggal', date('Y', strtotime($date)))
                ->whereMonth('jadwal_pengiriman.tanggal', date('m', strtotime($date)));
        }

        $completedFallbackCount = $completedFallbackCountQuery
            ->distinct('jadwal_pengiriman.id_jadwal')
            ->count('jadwal_pengiriman.id_jadwal');
        $total += $completedFallbackCount;
        $selesai += $completedFallbackCount;

        // Priority counts from jadwal_pengiriman with same period/date
        $jadwalQuery = DB::table('jadwal_pengiriman');
        if ($date && $period === 'day') {
            $jadwalQuery->whereDate('tanggal', $date);
        } elseif ($date && $period === 'year') {
            $year = date('Y', strtotime($date));
            $jadwalQuery->whereYear('tanggal', $year);
        } elseif ($date) {
            $year = date('Y', strtotime($date));
            $month = date('m', strtotime($date));
            $jadwalQuery->whereYear('tanggal', $year)->whereMonth('tanggal', $month);
        }

        $urgent = (clone $jadwalQuery)->where('priority', 'urgent')->count();
        $high = (clone $jadwalQuery)->where('priority', 'high')->count();
        $normal = (clone $jadwalQuery)->where(function ($q) {
            $q->whereNull('priority')->orWhere('priority', 'normal');
        })->count();

        return view('status_log.index', compact('rows', 'total', 'pending', 'proses', 'selesai', 'urgent', 'high', 'normal', 'period', 'date', 'q'));
    }

    public function show($id)
    {
        $r = DB::table('status_log')
            ->leftJoin('pengiriman', 'status_log.id_pengiriman', '=', 'pengiriman.id_pengiriman')
            ->leftJoin('jadwal_pengiriman', 'pengiriman.id_jadwal', '=', 'jadwal_pengiriman.id_jadwal')
            ->leftJoin('user', 'status_log.id_user', '=', 'user.id_user')
            ->select(
                'status_log.id_log',
                'status_log.driver',
                'status_log.username',
                'status_log.dari',
                'status_log.tujuan as tujuan_field',
                'status_log.material',
                'jadwal_pengiriman.code_material',
                'pengiriman.no_rec as no_rec',
                'status_log.waktu_pending',
                'status_log.waktu_penyelesaian',
                'status_log.status_update',
                'status_log.keterangan_log',
                'user.name as user_name',
                'user.email as user_email'
            )
            ->where('status_log.id_log', $id)
            ->first();

        if (! $r) {
            abort(404);
        }

        return view('status_log.show', compact('r'));
    }
}
