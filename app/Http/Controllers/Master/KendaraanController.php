<?php

namespace App\Http\Controllers\Master;

use App\Exports\KendaraanExport;
use App\Http\Controllers\Controller;
use App\Imports\KendaraanImport;
use App\Models\Kendaraan;
use App\Models\Transporter;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class KendaraanController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $items = Kendaraan::with('transporter')->when($q, fn ($b) => $b->where('no_pol', 'like', '%'.$q.'%'))->paginate(15);

        return view('master.kendaraan.index', compact('items', 'q'));
    }

    public function create()
    {
        $transporters = Transporter::all();

        return view('master.kendaraan.form', ['item' => new Kendaraan, 'transporters' => $transporters]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['no_pol' => 'required', 'jenis_kendaraan' => 'nullable', 'model_kendaraan' => 'nullable', 'code_kendaraan' => 'nullable', 'id_transporter' => 'nullable|integer|exists:transporter,id_transporter']);
        Kendaraan::create($data);

        return redirect()->route('master.kendaraan.index')->with('success', 'Data kendaraan berhasil ditambahkan');
    }

    public function edit(Kendaraan $kendaraan)
    {
        $transporters = Transporter::all();

        return view('master.kendaraan.form', ['item' => $kendaraan, 'transporters' => $transporters]);
    }

    public function update(Request $request, Kendaraan $kendaraan)
    {
        $data = $request->validate(['no_pol' => 'required', 'jenis_kendaraan' => 'nullable', 'model_kendaraan' => 'nullable', 'code_kendaraan' => 'nullable', 'id_transporter' => 'nullable|integer|exists:transporter,id_transporter']);
        $kendaraan->update($data);

        return redirect()->route('master.kendaraan.index')->with('success', 'Data kendaraan berhasil diperbarui');
    }

    public function destroy(Kendaraan $kendaraan)
    {
        $kendaraan->delete();

        return back()->with('success', 'Data kendaraan berhasil dihapus');
    }

    public function export()
    {
        return Excel::download(new KendaraanExport, 'kendaraan.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx']);
        Excel::import(new KendaraanImport, $request->file('file'));

        return back()->with('success', 'Import selesai');
    }

    public function downloadTemplate()
    {
        return Excel::download(new KendaraanExport(true), 'kendaraan_template.xlsx');
    }
}
