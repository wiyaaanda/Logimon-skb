<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DataOperasionalExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows->map(function ($row) {
            return [
                $row->tanggal ?? '',
                $row->tujuan ?? '',
                $row->no_rec ?? '',
                $row->jam_berangkat ?? '',
                $row->jumlah_pengantaran ?? 0,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'TANGGAL',
            'TUJUAN',
            'NO REC',
            'JAM BERANGKAT',
            'JUMLAH PENGANTARAN',
        ];
    }
}
