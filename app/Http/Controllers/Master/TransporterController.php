<?php

namespace App\Http\Controllers\Master;

use App\Exports\TransporterExport;
use App\Http\Controllers\Controller;
use App\Imports\TransporterImport;
use App\Models\Transporter;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TransporterController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $items = Transporter::when($q, fn ($b) => $b->where('nama_transporter', 'like', '%'.$q.'%'))->paginate(15);

        return view('master.transporter.index', compact('items', 'q'));
    }

    public function create()
    {
        return view('master.transporter.form', ['item' => new Transporter]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_transporter' => 'required',
            'no_telp_transporter' => 'nullable',
            'tempat_transporter' => 'nullable',
            'email_transporter' => 'nullable|email',
            'pic_transporter' => 'nullable',
        ]);
        Transporter::create($data);

        return redirect()->route('master.transporter.index')->with('success', 'Data transporter berhasil ditambahkan');
    }

    public function edit(Transporter $transporter)
    {
        return view('master.transporter.form', ['item' => $transporter]);
    }

    public function update(Request $request, Transporter $transporter)
    {
        $data = $request->validate(['nama_transporter' => 'required', 'no_telp_transporter' => 'nullable', 'tempat_transporter' => 'nullable', 'email_transporter' => 'nullable|email', 'pic_transporter' => 'nullable']);
        $transporter->update($data);

        return redirect()->route('master.transporter.index')->with('success', 'Data transporter berhasil diperbarui');
    }

    public function destroy(Transporter $transporter)
    {
        $transporter->delete();

        return back()->with('success', 'Data transporter berhasil dihapus');
    }

    public function export()
    {
        return Excel::download(new TransporterExport, 'transporter.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx']);
        Excel::import(new TransporterImport, $request->file('file'));

        return back()->with('success', 'Import selesai');
    }

    public function downloadTemplate()
    {
        return Excel::download(new TransporterExport(true), 'transporter_template.xlsx');
    }
}
