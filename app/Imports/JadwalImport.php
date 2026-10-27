<?php

namespace App\Imports;

use App\Models\Asal;
use App\Models\DataTimbangan;
use App\Models\DataVolume;
use App\Models\Driver;
use App\Models\JadwalPengiriman;
use App\Models\Kendaraan;
use App\Models\Material;
use App\Models\Pengiriman;
use App\Models\Transporter;
use App\Models\Tujuan;
use App\Services\ShipmentPriorityService;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class JadwalImport implements ToCollection, WithHeadingRow
{
    public function __construct(private readonly ShipmentPriorityService $priorityService = new ShipmentPriorityService) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $this->createSchedule($row->toArray());
        }
    }

    private function createSchedule(array $row): JadwalPengiriman
    {
        $noRec = $this->textValue($this->value($row, 'no_resi', 'no_rec'));
        $jamBerangkat = $this->timeValue($this->value($row, 'jam_berangkat', 'jam'));
        $volume = $this->numberValue($this->value($row, 'volume', 'vol_kg'));
        $timbangan = $this->numberValue($row['timbangan_cv_sr'] ?? null);

        $existingSchedule = $this->findExistingSchedule($noRec);
        if ($existingSchedule) {
            $existingPengiriman = $existingSchedule->pengirimans()->latest('id_pengiriman')->first();
            $existingDate = $row['tanggal'] ?? null;
            if ($existingDate !== null && trim((string) $existingDate) !== '') {
                $existingDate = $this->dateValue($existingDate);
                if (Carbon::parse($existingDate)->lt(Carbon::create(2026, 1, 1))) {
                    $existingSchedule->status = 'Selesai';
                    $existingSchedule->progress_tujuan_persen = 100;
                    $existingPengiriman?->update([
                        'status' => 'Selesai',
                    ]);
                }
            }

            if ($volume !== null) {
                $existingSchedule->vol_kg = $volume;
            }

            if ($timbangan !== null) {
                $existingSchedule->timbangan_cv_sr = $timbangan;
            }

            if ($jamBerangkat !== null) {
                $existingSchedule->jam_berangkat = $jamBerangkat;
            }

            if (! $existingPengiriman && ($volume !== null || $timbangan !== null || $jamBerangkat !== null || $noRec !== null)) {
                $existingPengiriman = Pengiriman::create([
                    'id_jadwal' => $existingSchedule->id_jadwal,
                    'no_rec' => $noRec,
                    'jam_berangkat' => $jamBerangkat,
                    'vol_kg' => $volume,
                    'total_volume_kg' => $volume,
                    'timbangan_cv_sr' => $timbangan,
                    'total_timbangan_cv_sr' => $timbangan,
                    'status' => $existingSchedule->status,
                ]);
            }

            if ($existingPengiriman && $volume !== null) {
                $existingPengiriman->vol_kg = $volume;
                $existingPengiriman->total_volume_kg = $volume;
            }

            if ($existingPengiriman && $timbangan !== null) {
                $existingPengiriman->timbangan_cv_sr = $timbangan;
                $existingPengiriman->total_timbangan_cv_sr = $timbangan;
            }

            if ($existingPengiriman && $jamBerangkat !== null) {
                $existingPengiriman->jam_berangkat = $jamBerangkat;
            }

            if ($volume !== null && $existingPengiriman) {
                $dataVolume = DataVolume::query()
                    ->where('id_pengiriman', $existingPengiriman->id_pengiriman)
                    ->latest('id_volume')
                    ->first();

                if ($dataVolume) {
                    $dataVolume->total_volume_kg = $volume;
                } else {
                    $dataVolume = new DataVolume([
                        'id_pengiriman' => $existingPengiriman->id_pengiriman,
                        'id_material' => $existingSchedule->id_material,
                        'code_material' => $existingSchedule->code_material,
                        'nama_material' => $existingSchedule->material?->nama_material,
                        'satuan' => 'kg',
                        'tanggal' => $existingSchedule->tanggal,
                        'total_volume_kg' => $volume,
                        'jumlah_pengantaran' => 1,
                    ]);
                }

                $dataVolume->save();
            }

            $effectiveVolume = $volume
                ?? $existingPengiriman?->total_volume_kg
                ?? $existingPengiriman?->vol_kg
                ?? $existingSchedule->vol_kg;
            $effectiveDepartureTime = $jamBerangkat
                ?? $existingSchedule->jam_berangkat
                ?? $existingPengiriman?->jam_berangkat;
            $existingSchedule->priority = $this->priorityService->determinePriority(
                $effectiveVolume !== null ? (float) $effectiveVolume : null,
                $effectiveDepartureTime
            );
            $existingSchedule->nilai_bobot = $this->priorityService->weightForPriority($existingSchedule->priority);
            $existingSchedule->save();
            $existingPengiriman?->save();

            return $existingSchedule;
        }

        $driverName = trim((string) ($row['driver'] ?? ''));
        $vehicleNumber = trim((string) $this->value($row, 'no_polisi', 'no_pol'));
        $transporterName = trim((string) ($row['transporter'] ?? ''));
        $tujuanName = trim((string) ($row['tujuan'] ?? ''));
        $materialName = trim((string) ($row['material'] ?? ''));

        $driver = $this->findNamedMaster(Driver::class, 'nama_driver', $driverName, 'Driver');
        $kendaraan = $this->resolveVehicle($vehicleNumber);
        $transporter = $this->findNamedMaster(Transporter::class, 'nama_transporter', $transporterName, 'Transporter');
        $asalName = trim((string) ($row['asal'] ?? ''));
        $asal = $asalName !== ''
            ? $this->findNamedMaster(Asal::class, 'nama_asal', $asalName, 'Asal')
            : null;
        $tujuan = $this->findNamedMaster(Tujuan::class, 'nama_tujuan', $tujuanName, 'Tujuan');
        $material = $this->findNamedMaster(Material::class, 'nama_material', $materialName, 'Material');
        $tanggal = $this->dateValue($row['tanggal']);
        $isHistoricalSchedule = Carbon::parse($tanggal)->lt(Carbon::create(2026, 1, 1));
        $status = $isHistoricalSchedule
            ? 'Selesai'
            : (trim((string) ($row['status'] ?? '')) ?: 'Terjadwal');
        $priority = $this->priorityService->determinePriority($volume, $jamBerangkat);
        $nilaiBobot = $this->priorityService->weightForPriority($priority);
        $schedule = JadwalPengiriman::create([
            'tanggal' => $tanggal,
            'hari' => Carbon::parse($tanggal)->locale('id')->dayName,
            'id_driver' => $driver->id_driver,
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'id_transporter' => $transporter->id_transporter,
            'id_asal' => $asal?->id_asal,
            'id_tujuan' => $tujuan->id_tujuan,
            'id_material' => $material->id_material,
            'code_material' => trim((string) $this->value($row, 'kode_material', 'code_material')) ?: null,
            'no_rec' => $noRec,
            'jam_berangkat' => $jamBerangkat,
            'vol_kg' => $volume,
            'timbangan_cv_sr' => $timbangan,
            'no_pol' => $kendaraan->no_pol,
            'status' => $status,
            'priority' => $priority,
            'nilai_bobot' => $nilaiBobot,
            'progress_tujuan_persen' => $isHistoricalSchedule ? 100 : $this->progressForStatus($status),
        ]);

        if ($volume !== null || $timbangan !== null || $noRec !== null || $jamBerangkat !== null) {
            $pengiriman = Pengiriman::create([
                'id_jadwal' => $schedule->id_jadwal,
                'no_rec' => $noRec,
                'jam_berangkat' => $jamBerangkat,
                'vol_kg' => $volume,
                'total_volume_kg' => $volume,
                'timbangan_cv_sr' => $timbangan,
                'total_timbangan_cv_sr' => $timbangan,
                'status' => $schedule->status,
            ]);

            DataVolume::create([
                'id_pengiriman' => $pengiriman->id_pengiriman,
                'id_material' => $material->id_material,
                'code_material' => $schedule->code_material,
                'nama_material' => $material->nama_material,
                'satuan' => 'kg',
                'tanggal' => $schedule->tanggal,
                'total_volume_kg' => $volume ?? 0,
                'jumlah_pengantaran' => 1,
            ]);

            DataTimbangan::create([
                'id_pengiriman' => $pengiriman->id_pengiriman,
                'id_material' => $material->id_material,
                'code_material' => $schedule->code_material,
                'nama_material' => $material->nama_material,
                'satuan' => 'kg',
                'tanggal' => $schedule->tanggal,
                'total_timbangan_cv_sr' => $timbangan ?? 0,
                'jumlah_pengantaran' => 1,
            ]);
        }

        return $schedule;
    }

    private function findExistingSchedule(?string $noRec): ?JadwalPengiriman
    {
        if ($noRec === null) {
            return null;
        }

        return JadwalPengiriman::query()
            ->where('no_rec', $noRec)
            ->orWhereHas('pengirimans', function ($query) use ($noRec): void {
                $query->where('no_rec', $noRec);
            })
            ->first();
    }

    private function findMaster(mixed $model, string $label, string $value): mixed
    {
        if ($model === null) {
            throw new \InvalidArgumentException($label.' "'.$value.'" tidak ditemukan di data master.');
        }

        return $model;
    }

    private function findNamedMaster(string $modelClass, string $column, string $value, string $label): mixed
    {
        $normalizedValue = $this->normalizeLookupValue($value);
        $model = $modelClass::query()->get()->first(function (mixed $item) use ($column, $normalizedValue): bool {
            return $this->normalizeLookupValue((string) $item->{$column}) === $normalizedValue;
        });

        return $this->findMaster($model, $label, $value);
    }

    private function normalizeLookupValue(string $value): string
    {
        $value = str_replace("\xc2\xa0", ' ', $value);

        return mb_strtolower(trim((string) preg_replace('/\s+/u', ' ', $value)));
    }

    private function resolveVehicle(string $vehicleNumber): Kendaraan
    {
        $vehicle = Kendaraan::where('no_pol', $vehicleNumber)->first();
        if ($vehicle) {
            return $vehicle;
        }

        $matches = Kendaraan::where('no_pol', 'like', $vehicleNumber.'%')->get();
        if ($matches->count() === 1) {
            return $matches->first();
        }

        if ($matches->isEmpty()) {
            throw new \InvalidArgumentException('No polisi "'.$vehicleNumber.'" tidak ditemukan di data master.');
        }

        throw new \InvalidArgumentException('No polisi "'.$vehicleNumber.'" cocok dengan lebih dari satu kendaraan di data master.');
    }

    public function normalizeDate(mixed $value): ?string
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

            $text = trim((string) $value);
            foreach (['d/m/Y', 'd-m-Y', 'Y-m-d'] as $format) {
                try {
                    $date = Carbon::createFromFormat($format, $text);
                    if ($date !== false) {
                        return $date->format('Y-m-d');
                    }
                } catch (Throwable) {
                    continue;
                }
            }

            $timestamp = strtotime($text);

            return $timestamp === false ? null : date('Y-m-d', $timestamp);
        } catch (Throwable) {
            return null;
        }
    }

    private function dateValue(mixed $value): string
    {
        return $this->normalizeDate($value) ?? throw new \InvalidArgumentException('Tanggal tidak valid.');
    }

    private function numberValue(mixed $value): ?float
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return (float) str_replace(',', '.', (string) $value);
    }

    private function textValue(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function timeValue(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        try {
            if ($value instanceof DateTimeInterface) {
                return $value->format('H:i:s');
            }

            if (is_numeric($value) && (float) $value < 1) {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('H:i:s');
            }

            $text = trim((string) $value);
            if (preg_match('/^(\d{1,2})\s*[.:h]\s*(\d{1,2})$/i', $text, $matches)) {
                return sprintf('%02d:%02d:00', (int) $matches[1], (int) $matches[2]);
            }

            if (preg_match('/^(\d{3,4})$/', $text, $matches)) {
                $digits = str_pad($matches[1], 4, '0', STR_PAD_LEFT);

                return sprintf('%02d:%02d:00', (int) substr($digits, 0, 2), (int) substr($digits, 2, 2));
            }

            foreach (['H:i:s', 'H:i'] as $format) {
                try {
                    $time = Carbon::createFromFormat($format, $text);
                    if ($time !== false) {
                        return $time->format('H:i:s');
                    }
                } catch (Throwable) {
                    continue;
                }
            }

            return Carbon::parse($text)->format('H:i:s');
        } catch (Throwable) {
            return null;
        }
    }

    private function value(array $row, string $primary, string $fallback): mixed
    {
        return $row[$primary] ?? $row[$fallback] ?? null;
    }

    private function progressForStatus(string $status): int
    {
        return match ($status) {
            'Selesai' => 100,
            'Proses' => 50,
            default => 0,
        };
    }
}
