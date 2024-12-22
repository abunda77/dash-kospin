<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Keterlambatan Lebih Dari 30 Hari</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 14px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
        }
        th {
            background-color: #f0f0f0;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
        }
        .total {
            font-weight: bold;
            text-align: right;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">LAPORAN KETERLAMBATAN PEMBAYARAN</div>
        <div class="subtitle">LEBIH DARI 30 HARI</div>
        <div>Per Tanggal: {{ $today->format('d/m/Y') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>No Pinjaman</th>
                <th>Angsuran Pokok</th>
                <th>Bunga</th>
                <th>Denda</th>
                <th>Total Bayar</th>
                <th>Hari Terlambat</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($data as $index => $item)
                @php
                    $angsuranPokok = $item->jumlah_pinjaman / $item->jangka_waktu;

                    // Perhitungan hari terlambat
                    $lastTransaction = $item->transaksiPinjaman()
                        ->orderBy('angsuran_ke', 'desc')
                        ->first();

                    if ($lastTransaction) {
                        $tanggalJatuhTempo = Carbon\Carbon::parse($lastTransaction->tanggal_pembayaran)
                            ->addMonth()
                            ->startOfDay();
                    } else {
                        $tanggalJatuhTempo = Carbon\Carbon::parse($item->tanggal_pinjaman)
                            ->addMonth()
                            ->startOfDay();
                    }

                    $hariTerlambat = $today->gt($tanggalJatuhTempo) ?
                        $today->diffInDays($tanggalJatuhTempo) : 0;

                    // Pastikan denda selalu positif dengan abs()
                    $denda = abs(($item->denda->rate_denda/100 * $angsuranPokok / 30) * $hariTerlambat);
                    $bunga = ($item->jumlah_pinjaman * ($item->biayaBungaPinjaman->persentase_bunga/100)) / $item->jangka_waktu;
                    $totalBayar = $angsuranPokok + $bunga + $denda;
                    $total += $totalBayar;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->profile->first_name }} {{ $item->profile->last_name }}</td>
                    <td>{{ $item->no_pinjaman }}</td>
                    <td style="text-align: right">Rp. {{ number_format($angsuranPokok, 2, ',', '.') }}</td>
                    <td style="text-align: right">Rp. {{ number_format($bunga, 2, ',', '.') }}</td>
                    <td style="text-align: right">Rp. {{ number_format($denda, 2, ',', '.') }}</td>
                    <td style="text-align: right">Rp. {{ number_format($totalBayar, 2, ',', '.') }}</td>
                    <td style="text-align: center">{{ abs($hariTerlambat) }} hari</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="total">Total Keseluruhan:</td>
                <td colspan="2" class="total">Rp. {{ number_format($total, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
