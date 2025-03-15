<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Simulasi Pembiayaan Emas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.4;
            color: #333;
            font-size: 10px;
            margin: 0;
            padding: 8px;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 5px;
        }
        .header img {
            height: 50px;
            width: auto;
            margin-bottom: 3px;
        }
        .header h1 {
            font-size: 14px;
            font-weight: bold;
            margin: 2px 0;
            color: #000;
        }
        .date {
            text-align: right;
            margin-bottom: 10px;
            font-style: italic;
            color: #666;
        }
        .info-section {
            margin-bottom: 10px;
            border: 1px solid #ddd;
            padding: 8px;
            background-color: #f9f9f9;
        }
        .info-title {
            font-weight: bold;
            color: #000;
            margin-bottom: 5px;
            font-size: 11px;
        }
        .info-item {
            margin-bottom: 2px;
            display: flex;
            align-items: center;
        }
        .info-item:before {
            content: "•";
            margin-right: 6px;
            color: #666;
        }
        .simulasi-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 9px;
            box-shadow: 0 0 3px rgba(0,0,0,0.1);
        }
        .simulasi-table th {
            background-color: #659ad8;
            color: white;
            padding: 6px 4px;
            text-align: center;
            font-weight: bold;
            border: 0.5px solid #000;
        }
        .simulasi-table tr:nth-child(odd) {
            background-color: #ffffff;
        }
        .simulasi-table tr:nth-child(even) {
            background-color: #dfdcdc;
        }
        .simulasi-table tr:hover {
            background-color: #f0f7ff;
        }
        .simulasi-table td {
            padding: 4px;
            border: 0.5px solid #000;
            text-align: right;
        }
        .simulasi-table td:first-child {
            text-align: center;
        }
        .footer {
            margin-top: 10px;
            font-size: 8px;
            color: #000;
            text-align: left;
            padding-top: 3px;
        }
        .footer-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .footer-list li {
            margin-bottom: 2px;
        }
        .note {
            font-style: italic;
            font-size: 8px;
            margin-top: 3px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo_koperasi.jpg') }}" alt="Logo Koperasi">
        <h1>SIMULASI PEMBIAYAAN EMAS</h1>
        <h1>KOPERASI SINARA ARTHA</h1>
    </div>

    <div class="date">
        Tanggal: {{ now()->translatedFormat('d F Y') }}
    </div>

    <div class="info-section">
        <div class="info-title">Informasi Pembiayaan:</div>
        <div class="info-item">Setoran Awal: {{ $keterangan['setoran_awal'] }} dari harga emas</div>
        <div class="info-item">Biaya Administrasi: {{ $keterangan['administrasi'] }}</div>
        <div class="info-item">Biaya Materai: Rp 10.000</div>
        <div class="info-item">Bunga: {{ $keterangan['bunga_tahunan'] }} per tahun</div>
        <div class="info-item">Tenor: {{ implode(', ', $keterangan['tenor']) }} bulan</div>
    </div>

    <table class="simulasi-table">
        <thead>
            <tr>
                <th>Berat (gram)</th>
                <th>Harga Emas</th>
                <th>Administrasi + Materai</th>
                <th>DP 5%</th>
                <th>TOTAL UANG MUKA (Adm+DP)</th>
                <th>Pembiayaan 95%</th>
                <th colspan="5">Angsuran Per Bulan</th>
            </tr>
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                @foreach($keterangan['tenor'] as $tenor)
                    <th>{{ $tenor }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($hargaEmas as $emas)
            @php
                $harga = (int) str_replace(['Rp ', ',', '.'], '', $emas['antam']);
                $dp = $harga * 0.05;
                $biayaAdmin = $harga * 0.005;
                $adminMaterai = $biayaAdmin + 10000;
                $totalUangMuka = $dp + $adminMaterai;
                $pembiayaan = $harga - $dp;
            @endphp
            <tr>
                <td>{{ str_replace('.0 gram', '', $emas['kepingan']) }}</td>
                <td>{{ number_format($harga, 0, ',', '.') }}</td>
                <td>{{ number_format($adminMaterai, 0, ',', '.') }}</td>
                <td>{{ number_format($dp, 0, ',', '.') }}</td>
                <td>{{ number_format($totalUangMuka, 0, ',', '.') }}</td>
                <td>{{ number_format($pembiayaan, 0, ',', '.') }}</td>
                @foreach($keterangan['tenor'] as $tenor)
                @php
                    $bunga = ($pembiayaan * 0.05 * $tenor/12);
                    $total = $pembiayaan + $bunga;
                    $angsuran = round($total / $tenor);
                @endphp
                    <td>{{ number_format($angsuran, 0, ',', '.') }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="note">
        * Untuk Harga Emas berubah setiap hari, mohon konfirmasi ke kami untuk update terbaru
    </div>

    <div class="footer">
        <div class="info-title">Syarat Pengajuan:</div>
        <ul class="footer-list">
            @foreach($keterangan['info_tambahan'] as $info)
                <li>{{ $info }}</li>
            @endforeach
        </ul>
    </div>
</body>
</html>
