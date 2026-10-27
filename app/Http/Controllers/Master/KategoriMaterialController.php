<?php

namespace App\Http\Controllers\Master;

use App\Exports\KategoriMaterialExport;
use App\Http\Controllers\Controller;
use App\Imports\KategoriMaterialImport;
use App\Models\KategoriMaterial;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class KategoriMaterialController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $items = KategoriMaterial::when($q, fn ($b) => $b->where('code_material', 'like', '%'.$q.'%'))->paginate(15);

        return view('master.kategori_material.index', compact('items', 'q'));
    }

    public function create()
    {
        return view('master.kategori_material.form', ['item' => new KategoriMaterial]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['code_material' => 'nullable|string', 'qty_material' => 'nullable|string', 'satuan_material' => 'nullable|in:ton', 'harga_material' => 'nullable|numeric']);
        KategoriMaterial::create($data);

        return redirect()->route('master.kategori_material.index')->with('success', 'Kategori material berhasil ditambahkan');
    }

    public function edit(KategoriMaterial $kategori_material)
    {
        return view('master.kategori_material.form', ['item' => $kategori_material]);
    }

    public function update(Request $request, KategoriMaterial $kategori_material)
    {
        $data = $request->validate(['code_material' => 'nullable|string', 'qty_material' => 'nullable|string', 'satuan_material' => 'nullable|in:ton', 'harga_material' => 'nullable|numeric']);
        $kategori_material->update($data);

        return redirect()->route('master.kategori_material.index')->with('success', 'Kategori material berhasil diperbarui');
    }

    public function destroy(KategoriMaterial $kategori_material)
    {
        $kategori_material->delete();

        return back()->with('success', 'Kategori material berhasil dihapus');
    }

    public function export()
    {
        return Excel::download(new KategoriMaterialExport, 'kategori_material.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx']);
        Excel::import(new KategoriMaterialImport, $request->file('file'));

        return back()->with('success', 'Import selesai');
    }

    public function downloadTemplate()
    {
        return Excel::download(new KategoriMaterialExport(true), 'kategori_material_template.xlsx');
    }
}
