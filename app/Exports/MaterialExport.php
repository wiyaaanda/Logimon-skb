<?php

namespace App\Exports;

use App\Models\Material;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MaterialExport implements FromCollection, WithHeadings
{
    protected $template = false;

    public function __construct($template = false)
    {
        $this->template = $template;
    }

    public function collection()
    {
        if ($this->template) {
            return collect([['nama_material', 'code_material', 'jenis_material', 'stock_status_material']]);
        }

        return Material::with('kategori')->get()->map(fn ($material) => [
            $material->nama_material,
            $material->kategori?->code_material,
            $material->jenis_material,
            $material->stock_status_material,
        ]);
    }

    public function headings(): array
    {
        return ['nama_material', 'code_material', 'jenis_material', 'stock_status_material'];
    }
}
