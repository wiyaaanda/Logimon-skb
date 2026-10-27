<?php

namespace App\Exports;

use App\Models\Driver;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DriverExport implements FromCollection, WithHeadings
{
    protected $template = false;

    public function __construct($template = false)
    {
        $this->template = $template;
    }

    public function collection()
    {
        if ($this->template) {
            return collect([['nama_driver', 'no_telp_driver', 'tempat_driver', 'tanggal_lahir_driver', 'sim_driver', 'status_aktif_driver', 'id_transporter']]);
        }

        return Driver::select('nama_driver', 'no_telp_driver', 'tempat_driver', 'tanggal_lahir_driver', 'sim_driver', 'status_aktif_driver', 'id_transporter')->get();
    }

    public function headings(): array
    {
        return ['nama_driver', 'no_telp_driver', 'tempat_driver', 'tanggal_lahir_driver', 'sim_driver', 'status_aktif_driver', 'id_transporter'];
    }
}
