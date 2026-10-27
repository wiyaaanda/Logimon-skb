<?php

namespace App\Imports;

use App\Models\Tujuan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TujuanImport implements ToModel, WithHeadingRow
{
    use SkipsDuplicateRows;

    public function model(array $row)
    {
        if ($this->shouldSkip($row, 'nama_tujuan')) {
            return null;
        }

        return new Tujuan([
            'nama_tujuan' => $row['nama_tujuan'] ?? null,
            'kota_tujuan' => $row['kota_tujuan'] ?? null,
            'provensi_tujuan' => $row['provensi_tujuan'] ?? null,
            'no_telp_tujuan' => $row['no_telp_tujuan'] ?? null,
        ]);
    }
}
