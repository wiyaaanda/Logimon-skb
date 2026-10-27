<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class DataTimbanganExport implements FromCollection, WithEvents, WithHeadings
{
    public function __construct(private readonly Collection $groups) {}

    public function collection(): Collection
    {
        return $this->groups->flatMap(function ($group) {
            return $group->details->values()->map(function ($row, $index) use ($group) {
                return [
                    $row->nama_material ?? '',
                    $row->code_material ?? '',
                    'kg',
                    $row->tanggal ?? '',
                    $row->total_timbangan_cv_sr ?? '',
                    $index === 0 ? $group->group_total_timbangan : '',
                    $index === 0 ? $group->group_total_pengantaran : '',
                    $row->created_at ?? '',
                    $row->updated_at ?? '',
                ];
            });
        })->values();
    }

    public function headings(): array
    {
        return ['NAMA MATERIAL', 'CODE MATERIAL', 'QTY', 'TANGGAL', 'TIMBANGAN DETAIL', 'TOTAL TIMBANGAN', 'JUMLAH PENGANTARAN', 'CREATED AT', 'UPDATED AT'];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $row = 2;
                foreach ($this->groups as $group) {
                    $lastRow = $row + $group->details->count() - 1;
                    if ($lastRow > $row) {
                        $event->sheet->mergeCells("F{$row}:F{$lastRow}");
                        $event->sheet->mergeCells("G{$row}:G{$lastRow}");
                    }
                    $row = $lastRow + 1;
                }
            },
        ];
    }
}
