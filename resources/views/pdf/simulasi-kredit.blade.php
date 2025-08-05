<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Simulasi Kredit</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            height: 100px;
            width: auto;
            margin-bottom: 15px;
            object-fit: contain;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: right;
        }
        th {
            background-color: #f5f5f5;
        }
        .text-left {
            text-align: left;
        }
        .summary {
            margin-top: 20px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo_koperasi.jpg') }}" alt="Logo Koperasi">
        <h2>Simulasi Kredit</h2>
    </div>

    <div class="summary">
        <p><strong>Nominal Pinjaman:</strong> Rp {{ number_format((float) str_replace([',', '.'], '', $nominalPinjaman), 0, ',', '.') }}</p>
        <p><strong>Bunga:</strong> {{ $bunga }}%</p>
        <p><strong>Jangka Waktu:</strong> {{ $jangkaWaktu }} bulan</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-left">Bulan Ke</th>
                <th>Pokok</th>
                <th>Bunga</th>
                <th>Total Angsuran</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($angsuranList as $angsuran)
                <tr>
                    <td class="text-left">{{ $angsuran['bulan_ke'] }}</td>
                    <td>Rp {{ number_format($angsuran['pokok'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($angsuran['bunga'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($angsuran['angsuran'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="text-left"><strong>Total</strong></td>
                <td><strong>Rp {{ number_format(collect($angsuranList)->sum('pokok'), 0, ',', '.') }}</strong></td>
                <td><strong>Rp {{ number_format($totalBunga, 0, ',', '.') }}</strong></td>
                <td><strong>Rp {{ number_format($totalAngsuran, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="summary">
        <p><strong>Total Pinjaman:</strong> Rp {{ number_format((float) str_replace([',', '.'], '', $nominalPinjaman), 0, ',', '.') }}</p>
        <p><strong>Total Bunga:</strong> Rp {{ number_format($totalBunga, 0, ',', '.') }}</p>
        <p><strong>Total yang Harus Dibayar:</strong> Rp {{ number_format($totalAngsuran, 0, ',', '.') }}</p>
    </div>
</body>
</html>
