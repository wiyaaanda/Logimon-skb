<?php

namespace App\Imports;

use App\Models\KategoriMaterial;
use DateTimeInterface;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KategoriMaterialImport implements ToModel, WithHeadingRow
{
    use SkipsDuplicateRows;

    public function model(array $row)
    {
        if ($this->normalizeCode($row['code_material'] ?? null) === null) {
            return null;
        }

        if ($this->shouldSkip($row, 'code_material')) {
            return null;
        }

        return new KategoriMaterial([
            'code_material' => $this->normalizeCode($row['code_material'] ?? null),
            'qty_material' => $this->normalizeQuantity($row['qty_material'] ?? null),
            'satuan_material' => $this->normalizeUnit($row['satuan_material'] ?? null),
            'harga_material' => $row['harga_material'] ?? null,
        ]);
    }

    private function normalizeUnit(mixed $value): string
    {
        $unit = mb_strtolower(trim((string) $value));

        return $unit === '' ? 'ton' : $unit;
    }

    public function normalizeCode(mixed $value): ?string
    {
        $code = $value instanceof DateTimeInterface
            ? $value->format('n-j')
            : trim((string) $value);

        return $code === '' ? null : $code;
    }

    private function normalizeQuantity(mixed $value): ?string
    {
        $quantity = trim((string) $value);

        return $quantity === '' ? null : mb_strtoupper($quantity);
    }
}
