<?php

namespace App\Http\Controllers;

use App\Exports\DataTimbanganExport;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DataTimbanganController extends Controller
{
    private function groupedRows($query)
    {
        return $query->orderBy('tanggal')->orderBy('nama_material')->orderBy('code_material')->get()
            ->groupBy(fn ($row) => implode('|', [$row->tanggal, $row->nama_material, $row->code_material]))
            ->map(function ($details) {
                $first = $details->first();
                $first->details = $details->values();
                $first->group_total_timbangan = $details->sum('total_timbangan_cv_sr');
                $first->group_total_pengantaran = $details->sum('jumlah_pengantaran');

                return $first;
            })->values();
    }

    private function paginateGroups($groups, Request $request): array
    {
        $perPage = 20;
        $totalDetails = $groups->sum(fn ($group) => $group->details->count());
        $page = LengthAwarePaginator::resolveCurrentPage();
        $start = ($page - 1) * $perPage;
        $detailOffset = 0;
        $pageGroups = collect();

        foreach ($groups as $group) {
            if ($detailOffset >= $start && $detailOffset < $start + $perPage) {
                $pageGroups->push($group);
            }
            $detailOffset += $group->details->count();
        }

        $rows = new LengthAwarePaginator(
            $pageGroups->values(),
            $totalDetails,
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return [$rows, $start + 1, min($start + $pageGroups->sum(fn ($group) => $group->details->count()), $totalDetails), $totalDetails];
    }

    public function index(Request $request)
    {
        $date = $request->get('date');
        $q = $request->get('q');
        $from = $request->get('from');
        $to = $request->get('to');

        $rowsQuery = DB::table('data_timbangan')
            ->when($date, function ($qq) use ($date) {
                return $qq->where('tanggal', $date);
            });
        if ($q) {
            $rowsQuery->where(function ($w) use ($q) {
                $w->where('nama_material', 'like', '%'.$q.'%');
            });
        }
        [$rows, $resultFrom, $resultTo, $resultTotal] = $this->paginateGroups($this->groupedRows($rowsQuery), $request);

        $totalTimbanganQuery = DB::table('data_timbangan');
        $totalPengantaranQuery = DB::table('data_timbangan');

        if ($from && $to) {
            $totalTimbanganQuery->whereBetween('tanggal', [$from, $to]);
            $totalPengantaranQuery->whereBetween('tanggal', [$from, $to]);
        } elseif ($date) {
            $totalTimbanganQuery->where('tanggal', $date);
            $totalPengantaranQuery->where('tanggal', $date);
        }

        if ($q) {
            $totalTimbanganQuery->where('nama_material', 'like', '%'.$q.'%');
            $totalPengantaranQuery->where('nama_material', 'like', '%'.$q.'%');
        }
        $totalTimbangan = $totalTimbanganQuery->sum('total_timbangan_cv_sr');
        $totalPengantaran = $totalPengantaranQuery->sum('jumlah_pengantaran');

        $dates = collect();
        if ($date) {
            $dates->push($date);
            $daily = DB::table('data_timbangan')
                ->selectRaw('tanggal as day, sum(total_timbangan_cv_sr) as wt')
                ->when($date, function ($qq) use ($date) {
                    return $qq->where('tanggal', $date);
                })
                ->when($q, function ($qq) use ($q) {
                    return $qq->where('nama_material', 'like', '%'.$q.'%');
                })
                ->groupBy('tanggal')
                ->pluck('wt', 'day');
        } else {
            for ($i = 13; $i >= 0; $i--) {
                $dates->push(now()->subDays($i)->toDateString());
            }
            $daily = DB::table('data_timbangan')
                ->selectRaw('tanggal as day, sum(total_timbangan_cv_sr) as wt')
                ->where('tanggal', '>=', now()->subDays(13)->startOfDay())
                ->when($q, function ($qq) use ($q) {
                    return $qq->where('nama_material', 'like', '%'.$q.'%');
                })
                ->groupBy('tanggal')
                ->pluck('wt', 'day');
        }

        $labels = $dates->toArray();
        $data = array_map(function ($d) use ($daily) {
            return $daily[$d] ?? 0;
        }, $labels);

        return view('data_timbangan.index', compact('rows', 'totalTimbangan', 'totalPengantaran', 'labels', 'data', 'date', 'q', 'resultFrom', 'resultTo', 'resultTotal'));
    }

    public function export(Request $request)
    {
        $date = $request->get('date');
        $q = trim((string) $request->get('q', ''));

        $fileName = 'data_timbangan_'.date('Ymd_His').'.xlsx';
        $rows = $this->groupedRows(DB::table('data_timbangan')
            ->when($date, function ($query) use ($date) {
                return $query->where('tanggal', $date);
            })
            ->when($q, function ($query) use ($q) {
                $query->where('nama_material', 'like', '%'.$q.'%');
            })
        );

        return Excel::download(new DataTimbanganExport($rows), $fileName);
    }
}
