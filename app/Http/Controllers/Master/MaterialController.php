<?php

namespace App\Http\Controllers\Master;

use App\Exports\MaterialExport;
use App\Http\Controllers\Controller;
use App\Imports\MaterialImport;
use App\Models\KategoriMaterial;
use App\Models\Material;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $items = Material::with('kategori')
            ->when($q, function ($query) use ($q) {
                $query->where('nama_material', 'like', '%'.$q.'%')
                    ->orWhereHas('kategori', fn ($kategori) => $kategori->where('code_material', 'like', '%'.$q.'%'));
            })
            ->paginate(15);

        return view('master.material.index', compact('items', 'q'));
    }

    public function create()
    {
        $kategoris = KategoriMaterial::all();

        return view('master.material.form', ['item' => new Material, 'kategoris' => $kategoris]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_material' => 'required',
            'jenis_material' => 'nullable',
            'stock_status_material' => 'nullable',
            'id_kategori_material' => 'nullable|integer|exists:kategori_material,id_kategori_material',
        ]);
        Material::create($data);

        return redirect()->route('master.material.index')->with('success', 'Data material berhasil ditambahkan');
    }

    public function edit(Material $material)
    {
        $kategoris = KategoriMaterial::all();

        return view('master.material.form', ['item' => $material, 'kategoris' => $kategoris]);
    }

    public function update(Request $request, Material $material)
    {
        $data = $request->validate([
            'nama_material' => 'required',
            'jenis_material' => 'nullable',
            'stock_status_material' => 'nullable',
            'id_kategori_material' => 'nullable|integer|exists:kategori_material,id_kategori_material',
        ]);
        $material->update($data);

        return redirect()->route('master.material.index')->with('success', 'Data material berhasil diperbarui');
    }

    public function destroy(Material $material)
    {
        $material->delete();

        return back()->with('success', 'Data material berhasil dihapus');
    }

    public function export()
    {
        return Excel::download(new MaterialExport, 'material.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx']);
        Excel::import(new MaterialImport, $request->file('file'));

        return back()->with('success', 'Import selesai');
    }

    public function downloadTemplate()
    {
        return Excel::download(new MaterialExport(true), 'material_template.xlsx');
    }
}
