<?php

namespace App\Exports;

use App\Models\KategoriMaterial;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KategoriMaterialExport implements FromCollection, WithHeadings
{
    protected $template = false;

    public function __construct($template = false)
    {
        $this->template = $template;
    }

    public function collection()
    {
        if ($this->template) {
            return collect([['code_material', 'qty_material', 'satuan_material', 'harga_material']]);
        }

        return KategoriMaterial::select('code_material', 'qty_material', 'satuan_material', 'harga_material')->get();
    }

    public function headings(): array
    {
        return ['code_material', 'qty_material', 'satuan_material', 'harga_material'];
    }
}
