<?php

namespace App\Http\Controllers\Master;

use App\Exports\TujuanExport;
use App\Http\Controllers\Controller;
use App\Imports\TujuanImport;
use App\Models\Tujuan;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TujuanController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $items = Tujuan::when($q, fn ($b) => $b->where('nama_tujuan', 'like', '%'.$q.'%'))->paginate(15);

        return view('master.tujuan.index', compact('items', 'q'));
    }

    public function create()
    {
        return view('master.tujuan.form', ['item' => new Tujuan]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['nama_tujuan' => 'required']);
        Tujuan::create($data);

        return redirect()->route('master.tujuan.index')->with('success', 'Data tujuan berhasil ditambahkan');
    }

    public function edit(Tujuan $tujuan)
    {
        return view('master.tujuan.form', ['item' => $tujuan]);
    }

    public function update(Request $request, Tujuan $tujuan)
    {
        $data = $request->validate(['nama_tujuan' => 'required']);
        $tujuan->update($data);

        return redirect()->route('master.tujuan.index')->with('success', 'Data tujuan berhasil diperbarui');
    }

    public function destroy(Tujuan $tujuan)
    {
        $tujuan->delete();

        return back()->with('success', 'Data tujuan berhasil dihapus');
    }

    public function export()
    {
        return Excel::download(new TujuanExport, 'tujuan.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx']);
        Excel::import(new TujuanImport, $request->file('file'));

        return back()->with('success', 'Import selesai');
    }

    public function downloadTemplate()
    {
        return Excel::download(new TujuanExport(true), 'tujuan_template.xlsx');
    }
}
