<?php

namespace App\Http\Controllers;

use App\Exports\DataOperasionalExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DataOperasionalController extends Controller
{
    private function rowsQuery(Request $request)
    {
        $date = $request->get('date');
        $q = trim((string) $request->get('q', ''));

        return DB::table('pengiriman')
            ->join('jadwal_pengiriman', 'pengiriman.id_jadwal', '=', 'jadwal_pengiriman.id_jadwal')
            ->leftJoin('material', 'jadwal_pengiriman.id_material', '=', 'material.id_material')
            ->leftJoin('tujuan', 'jadwal_pengiriman.id_tujuan', '=', 'tujuan.id_tujuan')
            ->leftJoin('data_volume as dv', 'pengiriman.id_pengiriman', '=', 'dv.id_pengiriman')
            ->select(
                'pengiriman.id_pengiriman',
                'jadwal_pengiriman.id_material',
                'material.nama_material',
                'jadwal_pengiriman.code_material',
                'jadwal_pengiriman.id_tujuan',
                'tujuan.nama_tujuan as tujuan',
                'pengiriman.no_rec',
                'pengiriman.jam_berangkat',
                DB::raw('COALESCE(DATE(pengiriman.jam_berangkat), jadwal_pengiriman.tanggal) as tanggal'),
                'pengiriman.vol_kg',
                'dv.jumlah_pengantaran as jumlah_pengantaran'
            )
            ->when($date, function ($query) use ($date) {
                return $query->whereRaw('COALESCE(DATE(pengiriman.jam_berangkat), jadwal_pengiriman.tanggal) = ?', [$date]);
            })
            ->when($q, function ($query) use ($q) {
                $query->where(function ($where) use ($q) {
                    $where->where('material.nama_material', 'like', "%{$q}%")
                        ->orWhere('jadwal_pengiriman.code_material', 'like', "%{$q}%")
                        ->orWhere('tujuan.nama_tujuan', 'like', "%{$q}%")
                        ->orWhere('pengiriman.no_rec', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('pengiriman.id_pengiriman');
    }

    public function index(Request $request)
    {
        $date = $request->get('date');
        $q = trim((string) $request->get('q', ''));

        $rows = $this->rowsQuery($request)
            ->paginate(20)
            ->withQueryString();

        // Performance statistics
        $totalPengiriman = DB::table('pengiriman')->count();

        return view('operasional.index', compact('rows', 'totalPengiriman', 'date', 'q'));
    }

    public function export(Request $request)
    {
        return Excel::download(
            new DataOperasionalExport($this->rowsQuery($request)->get()),
            'data_operasional_'.now()->format('Ymd_His').'.xlsx'
        );
    }
}
