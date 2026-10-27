<?php

namespace App\Http\Controllers\Master;

use App\Exports\JadwalTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\AsalImport;
use App\Imports\DriverImport;
use App\Imports\JadwalImport;
use App\Imports\KategoriMaterialImport;
use App\Imports\KendaraanImport;
use App\Imports\MaterialImport;
use App\Imports\TransporterImport;
use App\Imports\TujuanImport;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ImportController extends Controller
{
    public function preview(Request $request, string $type): JsonResponse
    {
        $definition = $this->definition($type);
        if (! $definition) {
            return response()->json(['message' => 'Jenis import tidak dikenal.'], 404);
        }

        $fileError = $this->validateFile($request);
        if ($fileError) {
            return $fileError;
        }

        try {
            $previewImport = new class implements ToCollection, WithHeadingRow
            {
                public array $rows = [];

                public function collection(Collection $collection): void
                {
                    $this->rows = $collection->map(fn (Collection $row): array => $row->toArray())->all();
                }
            };

            Excel::import($previewImport, $request->file('file'));
            $previewImport->rows = $this->removeEmptyRows($previewImport->rows, $type);
            if ($type === 'jadwal') {
                $jadwalImport = new JadwalImport;
                $previewImport->rows = array_map(function (array $row) use ($jadwalImport): array {
                    $row['tanggal'] = $jadwalImport->normalizeDate($row['tanggal'] ?? null);

                    return $row;
                }, $previewImport->rows);
            }
            if ($type === 'driver') {
                $driverImport = new DriverImport;
                $previewImport->rows = array_map(function (array $row) use ($driverImport): array {
                    $row['tanggal_lahir_driver'] = $driverImport->normalizeBirthDate($row['tanggal_lahir_driver'] ?? null);
                    $row['status_aktif_driver'] = $driverImport->normalizeStatus($row['status_aktif_driver'] ?? null);

                    return $row;
                }, $previewImport->rows);
            }
            if ($type === 'kategori_material') {
                $kategoriMaterialImport = new KategoriMaterialImport;
                $previewImport->rows = array_map(function (array $row) use ($kategoriMaterialImport): array {
                    $row['code_material'] = $kategoriMaterialImport->normalizeCode($row['code_material'] ?? null);

                    return $row;
                }, $previewImport->rows);
            }
            if ($type === 'material') {
                $previewImport->rows = array_map(function (array $row): array {
                    $value = $row['code_material'] ?? null;
                    $row['code_material'] = $value instanceof DateTimeInterface
                        ? $value->format('n-j')
                        : ($value === null ? null : trim((string) $value));
                    unset($row['harga_material']);

                    return $row;
                }, $previewImport->rows);
            }

            $rows = array_slice($previewImport->rows, 0, 100);
            $errors = $this->validateRows($previewImport->rows, $definition);
            if ($type === 'jadwal') {
                $rows = array_map(function (array $row): array {
                    if (! empty($row['tanggal'])) {
                        $row['tanggal'] = strtoupper(Carbon::parse($row['tanggal'])->locale('id')->translatedFormat('l, d F Y'));
                    }

                    return $row;
                }, $rows);
            }
            $columns = $rows ? array_keys($rows[0]) : [];

            if (! $rows) {
                $errors[] = 'File tidak memiliki baris data.';
            }

            if ($columns) {
                $missingColumns = array_diff($definition['required'], $columns);
                foreach ($missingColumns as $column) {
                    $errors[] = 'Format kolom tidak sesuai: kolom '.$column.' tidak ditemukan.';
                }
            }

            $duplicateCheck = $this->findDuplicates($previewImport->rows, $definition);

            return response()->json([
                'valid' => $errors === [],
                'columns' => $columns,
                'rows' => $rows,
                'total' => count($previewImport->rows),
                'errors' => array_values(array_unique($errors)),
                'warnings' => $duplicateCheck['warnings'],
            ], $errors === [] ? 200 : 422);
        } catch (Throwable $exception) {
            return response()->json([
                'message' => 'File tidak dapat dibaca. Pastikan format file sesuai template.',
                'errors' => [$exception->getMessage()],
            ], 422);
        }
    }

    public function import(Request $request, string $type): JsonResponse
    {
        $definition = $this->definition($type);
        if (! $definition) {
            return response()->json(['message' => 'Jenis import tidak dikenal.'], 404);
        }

        $fileError = $this->validateFile($request);
        if ($fileError) {
            return $fileError;
        }

        try {
            $previewImport = new class implements ToCollection, WithHeadingRow
            {
                public array $rows = [];

                public function collection(Collection $collection): void
                {
                    $this->rows = $collection->map(fn (Collection $row): array => $row->toArray())->all();
                }
            };

            Excel::import($previewImport, $request->file('file'));
            $previewImport->rows = $this->removeEmptyRows($previewImport->rows, $type);
            if ($type === 'jadwal') {
                $jadwalImport = new JadwalImport;
                $previewImport->rows = array_map(function (array $row) use ($jadwalImport): array {
                    $row['tanggal'] = $jadwalImport->normalizeDate($row['tanggal'] ?? null);

                    return $row;
                }, $previewImport->rows);
            }
            if ($type === 'material') {
                $previewImport->rows = array_map(function (array $row): array {
                    $value = $row['code_material'] ?? null;
                    $row['code_material'] = $value instanceof DateTimeInterface
                        ? $value->format('n-j')
                        : ($value === null ? null : trim((string) $value));
                    unset($row['harga_material']);

                    return $row;
                }, $previewImport->rows);
            }
            $errors = $this->validateRows($previewImport->rows, $definition);
            $columns = $previewImport->rows ? array_keys($previewImport->rows[0]) : [];
            $errors = array_merge($errors, array_map(
                fn (string $column): string => 'Format kolom tidak sesuai: kolom '.$column.' tidak ditemukan.',
                $type === 'jadwal' && collect($previewImport->rows)->every(fn (array $row): bool => $this->existingJadwalByNoRec($row) !== null)
                    ? []
                    : array_diff($definition['required'], $columns)
            ));
            $duplicateCheck = $this->findDuplicates($previewImport->rows, $definition);

            if (! $previewImport->rows) {
                $errors[] = 'File tidak memiliki baris data.';
            }

            if ($errors !== []) {
                return response()->json(['message' => 'Data tidak valid.', 'errors' => array_values(array_unique($errors))], 422);
            }

            DB::transaction(function () use ($definition, $request, $duplicateCheck): void {
                $import = $duplicateCheck['skipValues']
                    ? new $definition['import']($duplicateCheck['skipValues'])
                    : new $definition['import'];

                Excel::import($import, $request->file('file'));
            });

            $skipped = count($duplicateCheck['skipRows']);
            $imported = count($previewImport->rows) - $skipped;

            return response()->json([
                'message' => 'Import selesai. '.$imported.' data berhasil ditambahkan dan '.$skipped.' data duplikat dilewati.',
                'imported' => $imported,
                'skipped' => $skipped,
                'warnings' => $duplicateCheck['warnings'],
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'message' => 'Import gagal diproses.',
                'errors' => [$exception->getMessage()],
            ], 422);
        }
    }

    public function template()
    {
        return Excel::download(new JadwalTemplateExport, 'template_import_jadwal.xlsx');
    }

    private function validateFile(Request $request): ?JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'File import tidak valid.',
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        return null;
    }

    private function validateRows(array $rows, array $definition): array
    {
        $errors = [];
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $isExistingJadwalUpdate = ($definition['type'] ?? null) === 'jadwal'
                && $this->existingJadwalByNoRec($row) !== null;
            foreach ($definition['required'] as $column) {
                if ($isExistingJadwalUpdate) {
                    continue;
                }
                if (! isset($row[$column]) || trim((string) $row[$column]) === '') {
                    $errors[] = 'Baris '.$rowNumber.': kolom '.$column.' wajib diisi.';
                }
            }

            foreach ($definition['rules'] ?? [] as $column => $rule) {
                if (($row[$column] ?? '') !== '' && $rule === 'email' && ! filter_var($row[$column], FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Baris '.$rowNumber.': '.$column.' harus berupa email yang valid.';
                }
                if (($row[$column] ?? '') !== '' && $rule === 'numeric' && ! is_numeric($row[$column])) {
                    $errors[] = 'Baris '.$rowNumber.': '.$column.' harus berupa angka.';
                }
            }

            if (($definition['type'] ?? null) === 'jadwal') {
                if (! empty($row['tanggal']) && ! (new JadwalImport)->normalizeDate($row['tanggal'])) {
                    $errors[] = 'Baris '.$rowNumber.': tanggal tidak valid.';
                }
                foreach (['volume', 'bobot', 'timbangan_cv_sr'] as $column) {
                    if (($row[$column] ?? '') !== '' && ! is_numeric(str_replace(',', '.', (string) $row[$column]))) {
                        $errors[] = 'Baris '.$rowNumber.': '.$column.' harus berupa angka.';
                    }
                }
                if (! empty($row['priority']) && ! in_array($row['priority'], ['urgent', 'high', 'normal'], true)) {
                    $errors[] = 'Baris '.$rowNumber.': priority harus urgent, high, atau normal.';
                }
                if (! empty($row['status']) && ! in_array($row['status'], ['Terjadwal', 'Proses', 'Selesai', 'Pending'], true)) {
                    $errors[] = 'Baris '.$rowNumber.': status tidak valid.';
                }
            }
        }

        return $errors;
    }

    private function removeEmptyRows(array $rows, string $type): array
    {
        if ($type === 'material') {
            return array_values(array_filter($rows, fn (array $row): bool => trim((string) ($row['nama_material'] ?? '')) !== ''));
        }

        if ($type === 'asal') {
            return array_values(array_filter($rows, fn (array $row): bool => trim((string) ($row['nama_asal'] ?? '')) !== ''));
        }

        if ($type !== 'kategori_material') {
            return $rows;
        }

        return array_values(array_filter($rows, function (array $row): bool {
            $value = $row['code_material'] ?? '';

            return $value instanceof DateTimeInterface
                ? $value->format('n-j') !== ''
                : trim((string) $value) !== '';
        }));
    }

    private function findDuplicates(array $rows, array $definition): array
    {
        if (! isset($definition['unique'])) {
            return ['warnings' => [], 'skipRows' => [], 'skipValues' => []];
        }

        $warnings = [];
        $skipRows = [];
        $skipValues = [];
        foreach ($definition['unique'] as $unique) {
            $seen = [];
            foreach ($rows as $index => $row) {
                $rawValue = $row[$unique['column']] ?? '';
                $value = $rawValue instanceof DateTimeInterface
                    ? $rawValue->format('n-j')
                    : trim((string) $rawValue);
                if ($value === '') {
                    continue;
                }

                $normalized = mb_strtolower($value);
                $rowNumber = $index + 2;
                if (isset($seen[$normalized])) {
                    $warnings[] = 'Baris '.$rowNumber.': '.$unique['label'].' sama dengan baris '.$seen[$normalized].' di file dan dilewati.';
                    $skipRows[$rowNumber] = true;

                    continue;
                }

                $seen[$normalized] = $rowNumber;
                if (DB::table($unique['table'])->whereRaw('LOWER('.$unique['column'].') = ?', [$normalized])->exists()) {
                    $warnings[] = 'Baris '.$rowNumber.': '.$unique['label'].' "'.$value.'" sudah ada di database dan dilewati.';
                    $skipRows[$rowNumber] = true;
                    $skipValues[$unique['column']][$normalized] = true;
                }
            }
        }

        return ['warnings' => array_values(array_unique($warnings)), 'skipRows' => $skipRows, 'skipValues' => $skipValues];
    }

    private function definition(string $type): ?array
    {
        return [
            'jadwal' => ['import' => JadwalImport::class, 'required' => ['tanggal', 'driver', 'no_polisi', 'transporter', 'tujuan', 'material'], 'type' => 'jadwal'],
            'asal' => ['import' => AsalImport::class, 'required' => [], 'unique' => [['table' => 'asal', 'column' => 'nama_asal', 'label' => 'Nama asal']]],
            'driver' => ['import' => DriverImport::class, 'required' => ['nama_driver'], 'unique' => [['table' => 'driver', 'column' => 'nama_driver', 'label' => 'Nama driver']]],
            'kendaraan' => ['import' => KendaraanImport::class, 'required' => ['no_pol', 'jenis_kendaraan'], 'unique' => [['table' => 'kendaraan', 'column' => 'no_pol', 'label' => 'Nomor polisi']]],
            'kategori_material' => ['import' => KategoriMaterialImport::class, 'required' => [], 'unique' => [['table' => 'kategori_material', 'column' => 'code_material', 'label' => 'Kode material']]],
            'material' => ['import' => MaterialImport::class, 'required' => ['nama_material', 'jenis_material'], 'unique' => [['table' => 'material', 'column' => 'nama_material', 'label' => 'Nama material']]],
            'transporter' => ['import' => TransporterImport::class, 'required' => ['nama_transporter'], 'rules' => ['email_transporter' => 'email'], 'unique' => [['table' => 'transporter', 'column' => 'nama_transporter', 'label' => 'Nama transporter']]],
            'tujuan' => ['import' => TujuanImport::class, 'required' => ['nama_tujuan'], 'unique' => [['table' => 'tujuan', 'column' => 'nama_tujuan', 'label' => 'Nama tujuan']]],
        ][$type] ?? null;
    }

    private function existingJadwalByNoRec(array $row): ?object
    {
        $noRec = trim((string) ($row['no_resi'] ?? $row['no_rec'] ?? ''));
        if ($noRec === '') {
            return null;
        }

        return DB::table('jadwal_pengiriman as j')
            ->leftJoin('pengiriman as p', 'j.id_jadwal', '=', 'p.id_jadwal')
            ->where(function ($query) use ($noRec): void {
                $query->where('j.no_rec', $noRec)
                    ->orWhere('p.no_rec', $noRec);
            })
            ->select('j.id_jadwal')
            ->first();
    }
}
