<?php

namespace App\Exports;

use App\Models\Asal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AsalExport implements FromCollection, WithHeadings
{
    protected $template = false;

    public function __construct($template = false)
    {
        $this->template = $template;
    }

    public function collection()
    {
        if ($this->template) {
            return collect([['nama_asal', 'kota_asal', 'provensi_asal', 'no_telp_asal']]);
        }

        return Asal::select('nama_asal', 'kota_asal', 'provensi_asal', 'no_telp_asal')->get();
    }

    public function headings(): array
    {
        return ['nama_asal', 'kota_asal', 'provensi_asal', 'no_telp_asal'];
    }
}
