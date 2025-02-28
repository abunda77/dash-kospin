<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Simulasi Pembiayaan Emas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            font-size: 12px;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 20px;
        }
        .header img {
            height: 80px;
            width: auto;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin: 5px 0;
            color: #2c3e50;
        }
        .date {
            text-align: right;
            margin-bottom: 20px;
            font-style: italic;
            color: #666;
        }
        .info-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .info-item {
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }
        .info-item:before {
            content: "•";
            margin-right: 8px;
            color: #3498db;
        }
        .simulasi-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 11px;
        }
        .simulasi-table th {
            background-color: #3498db;
            color: white;
            padding: 8px;
            text-align: center;
            font-weight: bold;
        }
        .simulasi-table td {
            padding: 6px;
            border: 0.5px solid #bebcbc;
            text-align: right;
            font-weight: normal;
        }
        .simulasi-table tr:nth-child(odd) {
            background-color: #ffffff;
        }
        .simulasi-table tr:nth-child(even) {
            background-color: #e6e6e6;
        }
        .simulasi-table tr:hover {
            background-color: #e8f4ff;
        }
        .footer {
            margin-top: 20px;
            font-size: 10px;
            color: #666;
            text-align: left;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .footer-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
        }
        .footer-list li {
            display: inline-block;
            margin: 0 15px;
            position: relative;
        }
        .footer-list li:before {
            content: "•";
            color: #3498db;
            margin-right: 5px;
            font-weight: bold;
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
        <div class="info-item">Bunga: {{ $keterangan['bunga_tahunan'] }} per tahun</div>
        <div class="info-item">Tenor: {{ implode(', ', $keterangan['tenor']) }} bulan</div>
    </div>

    <table class="simulasi-table">
        <thead>
            <tr>
                <th>Berat (gram)</th>
                <th>Harga Emas</th>
                <th>DP (5%)</th>
                @foreach($keterangan['tenor'] as $tenor)
                    <th>{{ $tenor }} Bulan</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($hargaEmas as $emas)
            @php
                $harga = (int) str_replace(['Rp ', ',', '.'], '', $emas['antam']);
                $dp = $harga * 0.05;
                $pembiayaan = $harga - $dp;
            @endphp
            <tr>
                <td style="text-align: center">{{ $emas['kepingan'] }}</td>
                <td>Rp {{ number_format($harga, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($dp, 0, ',', '.') }}</td>
                @foreach($keterangan['tenor'] as $tenor)
                @php
                    $bunga = ($pembiayaan * 0.12 * $tenor/12);
                    $total = $pembiayaan + $bunga;
                    $angsuran = round($total / $tenor);
                @endphp
                    <td>Rp {{ number_format($angsuran, 0, ',', '.') }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <ul class="footer-list">
            @foreach($keterangan['info_tambahan'] as $info)
                <li>{{ $info }}</li>
            @endforeach
        </ul>
    </div>
</body>
</html>
