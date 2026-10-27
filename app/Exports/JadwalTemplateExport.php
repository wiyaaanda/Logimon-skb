<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class JadwalTemplateExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        return collect();
    }

    public function headings(): array
    {
        return [
            'tanggal',
            'driver',
            'no_polisi',
            'transporter',
            'asal',
            'tujuan',
            'material',
            'kode_material',
            'no_resi',
            'jam_berangkat',
            'volume',
            'bobot',
            'timbangan_cv_sr',
            'priority',
            'status',
        ];
    }
}
