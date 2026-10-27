<?php

namespace App\Http\Controllers;

use App\Exports\RekapExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class RekapPengirimanController extends Controller
{
    protected function baseQuery()
    {
        return DB::table('jadwal_pengiriman as j')
            ->leftJoin('pengiriman as p', 'j.id_jadwal', '=', 'p.id_jadwal')
            ->leftJoin('material as m', 'j.id_material', '=', 'm.id_material')
            ->leftJoin('tujuan as t', 'j.id_tujuan', '=', 't.id_tujuan')
            ->leftJoin('asal as a', 'j.id_asal', '=', 'a.id_asal')
            ->leftJoin('driver as d', 'j.id_driver', '=', 'd.id_driver')
            ->leftJoin('kendaraan as k', 'j.id_kendaraan', '=', 'k.id_kendaraan')
            ->leftJoin('transporter as tr', 'j.id_transporter', '=', 'tr.id_transporter')
            ->select(
                'j.tanggal',
                'p.jam_berangkat as jam_berangkat',
                'a.nama_asal',
                't.nama_tujuan',
                'd.nama_driver',
                'k.no_pol',
                'm.nama_material',
                'j.code_material',
                'p.vol_kg',
                'p.total_volume_kg',
                'p.timbangan_cv_sr',
                'p.total_timbangan_cv_sr',
                'p.no_rec',
                'tr.nama_transporter as nama_transporter'
            );
    }

    protected function applyFilters($query, Request $request)
    {
        $q = $request->input('q');
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');
        $month = $request->input('month');
        $year = $request->input('year');

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('m.nama_material', 'like', '%'.$q.'%')
                    ->orWhere('j.code_material', 'like', '%'.$q.'%')
                    ->orWhere('d.nama_driver', 'like', '%'.$q.'%')
                    ->orWhere('t.nama_tujuan', 'like', '%'.$q.'%')
                    ->orWhere('p.no_rec', 'like', '%'.$q.'%');
            });
        }

        if ($date_from) {
            $query->where('j.tanggal', '>=', $date_from);
        }
        if ($date_to) {
            $query->where('j.tanggal', '<=', $date_to);
        }

        if ($month) {
            $query->whereRaw('MONTH(j.tanggal) = ?', [$month]);
        }
        if ($year) {
            $query->whereRaw('YEAR(j.tanggal) = ?', [$year]);
        }

        return $query;
    }

    protected function aggregateBaseQuery(Request $request)
    {
        return $this->applyFilters(
            DB::table('jadwal_pengiriman as j')
                ->leftJoin('pengiriman as p', 'j.id_jadwal', '=', 'p.id_jadwal')
                ->leftJoin('material as m', 'j.id_material', '=', 'm.id_material')
                ->leftJoin('tujuan as t', 'j.id_tujuan', '=', 't.id_tujuan')
                ->leftJoin('asal as a', 'j.id_asal', '=', 'a.id_asal')
                ->leftJoin('driver as d', 'j.id_driver', '=', 'd.id_driver')
                ->leftJoin('kendaraan as k', 'j.id_kendaraan', '=', 'k.id_kendaraan')
                ->leftJoin('transporter as tr', 'j.id_transporter', '=', 'tr.id_transporter'),
            $request
        );
    }

    protected function detailRowsQuery(Request $request)
    {
        return $this->applyFilters(
            DB::table('jadwal_pengiriman as j')
                ->leftJoin('pengiriman as p', 'j.id_jadwal', '=', 'p.id_jadwal')
                ->leftJoin('material as m', 'j.id_material', '=', 'm.id_material')
                ->leftJoin('tujuan as t', 'j.id_tujuan', '=', 't.id_tujuan')
                ->leftJoin('asal as a', 'j.id_asal', '=', 'a.id_asal')
                ->leftJoin('driver as d', 'j.id_driver', '=', 'd.id_driver')
                ->leftJoin('kendaraan as k', 'j.id_kendaraan', '=', 'k.id_kendaraan')
                ->leftJoin('transporter as tr', 'j.id_transporter', '=', 'tr.id_transporter')
                ->select(
                    'j.tanggal',
                    'a.nama_asal',
                    't.nama_tujuan',
                    'd.nama_driver',
                    'k.no_pol',
                    'm.nama_material',
                    'j.code_material',
                    'p.vol_kg',
                    'p.total_volume_kg',
                    'p.timbangan_cv_sr',
                    'p.total_timbangan_cv_sr',
                    'p.no_rec',
                    'p.jam_berangkat',
                    'tr.nama_transporter'
                ),
            $request
        );
    }

    protected function groupedRows(Request $request): Collection
    {
        return $this->detailRowsQuery($request)
            ->orderBy('j.tanggal', 'desc')
            ->orderBy('m.nama_material')
            ->orderBy('j.code_material')
            ->get()
            ->groupBy(fn ($row) => implode('|', [$row->tanggal, $row->nama_material, $row->code_material]))
            ->map(function (Collection $details) {
                $first = $details->first();
                $first->details = $details->values();
                $first->total_volume_kg = $details->sum(fn ($row) => $row->total_volume_kg ?? $row->vol_kg ?? 0);
                $first->total_timbangan_cv_sr = $details->sum(fn ($row) => $row->total_timbangan_cv_sr ?? $row->timbangan_cv_sr ?? 0);

                return $first;
            })
            ->values();
    }

    public function index(Request $request)
    {
        $query = $this->applyFilters($this->baseQuery(), $request);

        $totals = (clone $this->aggregateBaseQuery($request))
            ->selectRaw('SUM(COALESCE(p.vol_kg,0)) as sum_vol, SUM(COALESCE(p.total_volume_kg,0)) as sum_total_volume, SUM(COALESCE(p.timbangan_cv_sr,0)) as sum_timbangan, SUM(COALESCE(p.total_timbangan_cv_sr,0)) as sum_total_timbangan')
            ->first();

        $byMaterial = (clone $this->aggregateBaseQuery($request))
            ->selectRaw('m.nama_material, j.code_material, COUNT(*) as cnt, SUM(COALESCE(p.vol_kg,0)) as sum_vol, SUM(COALESCE(p.total_volume_kg,0)) as sum_total_volume, SUM(COALESCE(p.total_timbangan_cv_sr,0)) as sum_total_timbangan')
            ->groupBy('m.nama_material', 'j.code_material')
            ->orderByDesc('sum_total_volume')
            ->limit(10)
            ->get();

        $groupedRows = $this->groupedRows($request);
        $page = LengthAwarePaginator::resolveCurrentPage();
        $rows = new LengthAwarePaginator(
            $groupedRows->forPage($page, 25)->values(),
            $groupedRows->count(),
            25,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );
        $detailNumberStart = $groupedRows
            ->take(($page - 1) * 25)
            ->sum(fn ($group) => $group->details->count()) + 1;

        $q = $request->input('q');
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');
        $month = $request->input('month');
        $year = $request->input('year');

        return view('rekap.index', compact('rows', 'q', 'date_from', 'date_to', 'month', 'year', 'totals', 'byMaterial', 'detailNumberStart'));
    }

    public function export(Request $request)
    {
        $rows = $this->groupedRows($request);

        return Excel::download(
            new RekapExport($rows),
            'rekap_pengiriman_'.now()->format('Ymd_His').'.xlsx'
        );
    }

    public function pdf(Request $request)
    {
        $rows = $this->groupedRows($request);

        $selectedDate = $request->input('date_from') ?: $request->input('date_to') ?: now()->toDateString();
        $carbonDate = Carbon::parse($selectedDate);

        $pdf = Pdf::loadView('rekap.pdf', [
            'rows' => $rows,
            'displayDate' => $carbonDate->translatedFormat('d F Y'),
            'displayDay' => $carbonDate->translatedFormat('l'),
            'displayTime' => now()->translatedFormat('H:i:s').' WIB',
        ])
            ->setOption([
                'defaultFont' => 'Arial',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'dpi' => 72,
            ])
            ->setPaper('A4', 'landscape');

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="rekap_pengiriman_'.now()->format('Ymd_His').'.pdf"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
