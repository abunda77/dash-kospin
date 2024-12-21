<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Keterlambatan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Keterlambatan Angsuran Bulan Ini</h2>
        <p>Tanggal: {{ $today->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="text-align: center;">No</th>
                <th style="text-align: center;">Nama</th>
                <th style="text-align: center;">No Pinjaman</th>
                <th style="text-align: center;">Angsuran Pokok</th>
                <th style="text-align: center;">Denda</th>
                <th style="text-align: center;">Total Bayar</th>
                <th style="text-align: center;">Hari Terlambat</th>
                <th style="text-align: center;">WhatsApp</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $item)
            @php
                $angsuranPokok = $item->jumlah_pinjaman / $item->jangka_waktu;
                $tanggalJatuhTempo = Carbon\Carbon::create(
                    $today->year,
                    $today->month,
                    Carbon\Carbon::parse($item->tanggal_jatuh_tempo)->day
                )->startOfDay();

                $hariTerlambat = $today->gt($tanggalJatuhTempo) ?
                    $today->diffInDays($tanggalJatuhTempo) : 0;

                $denda = ($item->denda->rate_denda/100 * $angsuranPokok / 30) * $hariTerlambat;
                $totalBayar = $angsuranPokok + $denda;
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ trim($item->profile->first_name . ' ' . $item->profile->last_name) }}</td>
                <td>{{ $item->no_pinjaman }}</td>
                <td>Rp.{{ number_format($angsuranPokok, 2, ',', '.') }}</td>
                <td>Rp.{{ number_format(abs($denda), 2, ',', '.') }}</td>
                <td>Rp.{{ number_format($totalBayar, 2, ',', '.') }}</td>
                <td>{{ abs($hariTerlambat) }} hari</td>
                <td>{{ $item->profile->whatsapp }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
