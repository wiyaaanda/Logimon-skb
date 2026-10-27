<?php

namespace App\Http\Controllers\Master;

use App\Exports\DriverExport;
use App\Http\Controllers\Controller;
use App\Imports\DriverImport;
use App\Models\Driver;
use App\Models\Transporter;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $items = Driver::with('transporter')->when($q, fn ($b) => $b->where('nama_driver', 'like', '%'.$q.'%'))->paginate(15);

        return view('master.driver.index', compact('items', 'q'));
    }

    public function create()
    {
        $transporters = Transporter::all();

        return view('master.driver.form', ['item' => new Driver, 'transporters' => $transporters]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['nama_driver' => 'required', 'no_telp_driver' => 'nullable', 'tempat_driver' => 'nullable', 'tanggal_lahir_driver' => 'nullable|date', 'sim_driver' => 'nullable', 'status_aktif_driver' => 'nullable', 'id_transporter' => 'nullable|integer|exists:transporter,id_transporter']);
        Driver::create($data);

        return redirect()->route('master.driver.index')->with('success', 'Data driver berhasil ditambahkan');
    }

    public function edit(Driver $driver)
    {
        $transporters = Transporter::all();

        return view('master.driver.form', ['item' => $driver, 'transporters' => $transporters]);
    }

    public function update(Request $request, Driver $driver)
    {
        $data = $request->validate(['nama_driver' => 'required', 'no_telp_driver' => 'nullable', 'tempat_driver' => 'nullable', 'tanggal_lahir_driver' => 'nullable|date', 'sim_driver' => 'nullable', 'status_aktif_driver' => 'nullable', 'id_transporter' => 'nullable|integer|exists:transporter,id_transporter']);
        $driver->update($data);

        return redirect()->route('master.driver.index')->with('success', 'Data driver berhasil diperbarui');
    }

    public function destroy(Driver $driver)
    {
        $driver->delete();

        return back()->with('success', 'Data driver berhasil dihapus');
    }

    public function export()
    {
        return Excel::download(new DriverExport, 'driver.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx']);
        Excel::import(new DriverImport, $request->file('file'));

        return back()->with('success', 'Import selesai');
    }

    public function downloadTemplate()
    {
        return Excel::download(new DriverExport(true), 'driver_template.xlsx');
    }
}
