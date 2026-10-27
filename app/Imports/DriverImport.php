<?php

namespace App\Imports;

use App\Models\Driver;
use App\Models\Transporter;
use DateTimeInterface;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class DriverImport implements ToModel, WithHeadingRow
{
    use SkipsDuplicateRows;

    public function model(array $row)
    {
        if ($this->shouldSkip($row, 'nama_driver')) {
            return null;
        }

        return new Driver([
            'nama_driver' => $row['nama_driver'] ?? null,
            'no_telp_driver' => $row['no_telp_driver'] ?? null,
            'tempat_driver' => $row['tempat_driver'] ?? null,
            'tanggal_lahir_driver' => $this->normalizeBirthDate($row['tanggal_lahir_driver'] ?? null),
            'sim_driver' => $row['sim_driver'] ?? null,
            'status_aktif_driver' => $this->normalizeStatus($row['status_aktif_driver'] ?? null),
            'id_transporter' => $this->resolveTransporterId($row['id_transporter'] ?? null),
        ]);
    }

    public function normalizeStatus(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return match (mb_strtolower(trim((string) $value))) {
            'aktif' => 'aktif',
            'tidak aktif', 'nonaktif', 'non-aktif' => 'nonaktif',
            default => mb_strtolower(trim((string) $value)),
        };
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

    public function normalizeBirthDate(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        try {
            if ($value instanceof DateTimeInterface) {
                return $value->format('Y-m-d');
            }

            if (is_numeric($value)) {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            }

            $timestamp = strtotime((string) $value);

            return $timestamp === false ? null : date('Y-m-d', $timestamp);
        } catch (Throwable) {
            return null;
        }
    }
}
