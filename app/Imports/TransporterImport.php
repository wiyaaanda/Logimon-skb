<?php

namespace App\Imports;

use App\Models\Transporter;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TransporterImport implements ToModel, WithHeadingRow
{
    use SkipsDuplicateRows;

    public function model(array $row)
    {
        if ($this->shouldSkip($row, 'nama_transporter')) {
            return null;
        }

        return new Transporter([
            'nama_transporter' => $row['nama_transporter'] ?? null,
            'no_telp_transporter' => $row['no_telp_transporter'] ?? null,
            'tempat_transporter' => $row['tempat_transporter'] ?? null,
            'email_transporter' => $row['email_transporter'] ?? null,
            'pic_transporter' => $row['pic_transporter'] ?? null,
        ]);
    }
}
