<?php

namespace App\Http\Controllers\Master;

use App\Exports\AsalExport;
use App\Http\Controllers\Controller;
use App\Imports\AsalImport;
use App\Models\Asal;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AsalController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $items = Asal::when($q, fn ($b) => $b->where('nama_asal', 'like', '%'.$q.'%'))->paginate(15);

        return view('master.asal.index', compact('items', 'q'));
    }

    public function create()
    {
        return view('master.asal.form', ['item' => new Asal]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['nama_asal' => 'required']);
        Asal::create($data);

        return redirect()->route('master.asal.index')->with('success', 'Data asal berhasil ditambahkan');
    }

    public function edit(Asal $asal)
    {
        return view('master.asal.form', ['item' => $asal]);
    }

    public function update(Request $request, Asal $asal)
    {
        $data = $request->validate(['nama_asal' => 'required']);
        $asal->update($data);

        return redirect()->route('master.asal.index')->with('success', 'Data asal berhasil diperbarui');
    }

    public function destroy(Asal $asal)
    {
        $asal->delete();

        return back()->with('success', 'Data asal berhasil dihapus');
    }

    public function export()
    {
        return Excel::download(new AsalExport, 'asal.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx']);
        Excel::import(new AsalImport, $request->file('file'));

        return back()->with('success', 'Import selesai');
    }

    public function downloadTemplate()
    {
        return Excel::download(new AsalExport(true), 'asal_template.xlsx');
    }
}
