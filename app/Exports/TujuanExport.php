<?php

namespace App\Exports;

use App\Models\Tujuan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TujuanExport implements FromCollection, WithHeadings
{
    protected $template = false;

    public function __construct($template = false)
    {
        $this->template = $template;
    }

    public function collection()
    {
        if ($this->template) {
            return collect([['nama_tujuan', 'kota_tujuan', 'provensi_tujuan', 'no_telp_tujuan']]);
        }

        return Tujuan::select('nama_tujuan', 'kota_tujuan', 'provensi_tujuan', 'no_telp_tujuan')->get();
    }

    public function headings(): array
    {
        return ['nama_tujuan', 'kota_tujuan', 'provensi_tujuan', 'no_telp_tujuan'];
    }
}
