<?php

namespace App\Imports;

use App\Models\KategoriMaterial;
use App\Models\Material;
use DateTimeInterface;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MaterialImport implements ToModel, WithHeadingRow
{
    use SkipsDuplicateRows;

    public function model(array $row)
    {
        if (trim((string) ($row['nama_material'] ?? '')) === '') {
            return null;
        }

        if ($this->shouldSkip($row, 'nama_material')) {
            return null;
        }

        $kategoriId = $row['id_kategori_material'] ?? null;
        $codeMaterial = $this->normalizeCode($row['code_material'] ?? null);
        if (! $kategoriId && $codeMaterial !== null) {
            $kategoriId = KategoriMaterial::where('code_material', $codeMaterial)->value('id_kategori_material');
        }

        return new Material([
            'nama_material' => $row['nama_material'] ?? null,
            'jenis_material' => $row['jenis_material'] ?? null,
            'stock_status_material' => $row['stock_status_material'] ?? null,
            'id_kategori_material' => $kategoriId,
        ]);
    }

    private function normalizeCode(mixed $value): ?string
    {
        $code = $value instanceof DateTimeInterface
            ? $value->format('n-j')
            : trim((string) $value);

        return $code === '' ? null : $code;
    }
}
