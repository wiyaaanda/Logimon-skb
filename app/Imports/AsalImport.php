<?php

namespace App\Imports;

use App\Models\Asal;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AsalImport implements ToModel, WithHeadingRow
{
    use SkipsDuplicateRows;

    public function model(array $row)
    {
        if (trim((string) ($row['nama_asal'] ?? '')) === '') {
            return null;
        }

        if ($this->shouldSkip($row, 'nama_asal')) {
            return null;
        }

        return new Asal([
            'nama_asal' => $row['nama_asal'] ?? null,
            'kota_asal' => $row['kota_asal'] ?? null,
            'provensi_asal' => $row['provensi_asal'] ?? null,
            'no_telp_asal' => $row['no_telp_asal'] ?? null,
        ]);
    }
}
