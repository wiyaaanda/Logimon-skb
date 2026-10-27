<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class RekapExport implements FromCollection, WithEvents, WithHeadings
{
    public function __construct(private readonly Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows->flatMap(function ($group) {
            return $group->details->values()->map(function ($row, $index) use ($group) {
                return [
                    $row->tanggal,
                    $row->nama_asal ?? '',
                    $row->nama_tujuan ?? '',
                    $row->nama_driver ?? '',
                    $row->no_pol ?? '',
                    $row->nama_material ?? '',
                    $row->code_material ?? '',
                    $row->vol_kg ?? '',
                    $index === 0 ? $group->total_volume_kg : '',
                    $row->timbangan_cv_sr ?? '',
                    $index === 0 ? $group->total_timbangan_cv_sr : '',
                    $row->no_rec ?? '',
                    $row->jam_berangkat ?? '',
                    $row->nama_transporter ?? '',
                ];
            });
        })->values();
    }

    public function headings(): array
    {
        return [
            'HARI/TANGGAL',
            'NAMA_ASAL',
            'NAMA_TUJUAN',
            'NAMA_DRIVER',
            'NO_POL',
            'NAMA_MATERIAL',
            'CODE_MATERIAL',
            'VOLUME',
            'TOTAL_VOLUME',
            'TIMBANGAN_CV_SR',
            'TOTAL_TIMBANGAN_CV_SR',
            'NO_REC',
            'JAM_BERANGKAT',
            'NAMA_TRANSPORTER',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $row = 2;

                foreach ($this->rows as $group) {
                    $lastRow = $row + $group->details->count() - 1;
                    if ($lastRow > $row) {
                        $event->sheet->mergeCells("I{$row}:I{$lastRow}");
                        $event->sheet->mergeCells("K{$row}:K{$lastRow}");
                    }
                    $row = $lastRow + 1;
                }
            },
        ];
    }
}
