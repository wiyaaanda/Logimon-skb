<?php

namespace App\Exports;

use App\Models\Kendaraan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KendaraanExport implements FromCollection, WithHeadings
{
    protected $template = false;

    public function __construct($template = false)
    {
        $this->template = $template;
    }

    public function collection()
    {
        if ($this->template) {
            return collect([['no_pol', 'jenis_kendaraan', 'model_kendaraan', 'code_kendaraan', 'id_transporter']]);
        }

        return Kendaraan::select('no_pol', 'jenis_kendaraan', 'model_kendaraan', 'code_kendaraan', 'id_transporter')->get();
    }

    public function headings(): array
    {
        return ['no_pol', 'jenis_kendaraan', 'model_kendaraan', 'code_kendaraan', 'id_transporter'];
    }
}
