<?php

namespace App\Imports;

use App\Models\Kendaraan;
use App\Models\Transporter;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KendaraanImport implements ToModel, WithHeadingRow
{
    use SkipsDuplicateRows;

    public function model(array $row)
    {
        if ($this->shouldSkip($row, 'no_pol')) {
            return null;
        }

        return new Kendaraan([
            'no_pol' => $row['no_pol'] ?? null,
            'jenis_kendaraan' => $row['jenis_kendaraan'] ?? null,
            'model_kendaraan' => $row['model_kendaraan'] ?? null,
            'code_kendaraan' => $row['code_kendaraan'] ?? null,
            'id_transporter' => $this->resolveTransporterId($row['id_transporter'] ?? null),
        ]);
    }

    private function resolveTransporterId(mixed $value): ?int
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $name = mb_strtolower(trim((string) $value));

        return Transporter::query()
            ->whereRaw('LOWER(nama_transporter) = ?', [$name])
            ->orWhereRaw('LOWER(nama_transporter) LIKE ?', [$name.'%'])
            ->value('id_transporter');
    }
}
