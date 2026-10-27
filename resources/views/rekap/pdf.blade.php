<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekap Pengiriman</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 14mm 12mm;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            background: #fff;
        }

        .report-page {
            width: 100%;
            box-sizing: border-box;
        }

        .report-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 1px solid #2f2f2f;
        }

        .report-logo {
            display: flex;
            align-items: center;
        }

        .report-logo img {
            width: 170px;
            height: auto;
            display: block;
        }

        .report-meta {
            text-align: right;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.4;
        }

        .report-meta .title {
            display: block;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-bottom: 4px;
        }

        .report-table-wrap {
            width: 100%;
            overflow: hidden;
            border: 1px solid #1f1f1f;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 7.5px;
        }

        th, td {
            padding: 4px 5px;
            border: 1px solid #1f1f1f;
            text-align: left;
            vertical-align: middle;
            word-break: break-word;
            line-height: 1.2;
        }

        thead th {
            background: #f3f4f6;
            font-weight: 700;
            text-transform: uppercase;
        }

        tbody td {
            background: #fff;
        }
    </style>
</head>
<body>
    <div class="report-page">
        <div class="report-header">
            <div class="report-logo">
                <img src="{{ public_path('images/logo.png') }}" alt="SKB Trans" />
            </div>

            <div class="report-meta">
                <span class="title">Keluar Masuk Material</span>
                <span>{{ $displayDate }}</span>
                <span>{{ $displayDay }}</span>
                <span>{{ $displayTime }}</span>
            </div>
        </div>

        <div class="report-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>HARI/TANGGAL</th>
                        <th>NAMA_ASAL</th>
                        <th>NAMA_TUJUAN</th>
                        <th>NAMA_DRIVER</th>
                        <th>NO_POL</th>
                        <th>NAMA_MATERIAL</th>
                        <th>CODE_MATERIAL</th>
                        <th>VOLUME</th>
                        <th>TOTAL_VOLUME</th>
                        <th>TIMBANGAN_CV_SR</th>
                        <th>TOTAL_TIMBANGAN_CV_SR</th>
                        <th>NO_REC</th>
                        <th>JAM_BERANGKAT</th>
                        <th>NAMA_TRANSPORTER</th>
                    </tr>
                </thead>
                <tbody>
                    @php($detailNumber = 1)
                    @foreach($rows as $group)
                        @foreach($group->details as $detailIndex => $r)
                            <tr>
                                <td>{{ $detailNumber++ }}</td>
                                <td>{{ $r->tanggal }}</td>
                                <td>{{ $r->nama_asal }}</td>
                                <td>{{ $r->nama_tujuan }}</td>
                                <td>{{ $r->nama_driver }}</td>
                                <td>{{ $r->no_pol }}</td>
                                <td>{{ $r->nama_material }}</td>
                                <td>{{ $r->code_material }}</td>
                                <td>{{ $r->vol_kg ?: '-' }}</td>
                                <td>{{ $detailIndex === 0 ? number_format($group->total_volume_kg, 0, ',', '.') : '-' }}</td>
                                <td>{{ $r->timbangan_cv_sr ?: '-' }}</td>
                                <td>{{ $detailIndex === 0 ? number_format($group->total_timbangan_cv_sr, 0, ',', '.') : '-' }}</td>
                                <td>{{ $r->no_rec }}</td>
                                <td>{{ $r->jam_berangkat }}</td>
                                <td>{{ $r->nama_transporter }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
