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
        <h2>Laporan Keterlambatan Pembayaran</h2>
        <p>Tanggal: {{ $today->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>No Pinjaman</th>
                <th>Angsuran Pokok</th>
                <th>Total Bayar</th>
                <th>Hari Terlambat</th>
                <th>WhatsApp</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->profile->first_name }} {{ $item->profile->last_name }}</td>
                <td>{{ $item->no_pinjaman }}</td>
                <td>Rp.{{ number_format($item->jumlah_pinjaman / $item->jangka_waktu, 2, ',', '.') }}</td>
                <td>@php
                    $angsuranPokok = $item->jumlah_pinjaman / $item->jangka_waktu;
                    $tanggalJatuhTempo = Carbon\Carbon::create(
                        $today->year,
                        $today->month,
                        Carbon\Carbon::parse($item->tanggal_jatuh_tempo)->day
                    )->startOfDay();

                    $hariTerlambat = 0;
                    if ($today->gt($tanggalJatuhTempo)) {
                        $hariTerlambat = abs($tanggalJatuhTempo->diffInDays($today));
                    }

                    $denda = ($item->denda->rate_denda/100 * $angsuranPokok / 30) * $hariTerlambat;
                    $totalBayar = $angsuranPokok + $denda;
                    @endphp
                    Rp.{{ number_format($totalBayar, 2, ',', '.') }}
                </td>
                <td>{{ $hariTerlambat }} hari</td>
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
