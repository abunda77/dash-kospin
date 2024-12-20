<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mutasi Tabungan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            padding: 10px 0;
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
        .header h2 {
            margin: 10px;
            padding: 0;
            font-size: 20px;
            font-weight: bold;
            color: #2d3748;
            line-height: 1.4;
        }
        .info {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #4a5568;
            color: white;
            font-size: 13px;
            font-weight: bold;
        }


        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 12px;
            color: #4a5568;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo_koperasi.jpg') }}" alt="Logos">
        <h2>Laporan Mutasi Tabungan</h2>
        @if(isset($filterDate['start']) && isset($filterDate['end']))
            <p>Periode: {{ \Carbon\Carbon::parse($filterDate['start'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filterDate['end'])->format('d/m/Y') }}</p>
        @endif
    </div>

    <div class="info">
        <table style="width: 50%; border: none;">
            <tr>
                <td style="border: none;">No. Rekening</td>
                <td style="border: none;">: {{ $tabungan->no_tabungan }}</td>
            </tr>
            <tr>
                <td style="border: none;">Nama</td>
                <td style="border: none;">: {{ $tabungan->profile->first_name }} {{ $tabungan->profile->last_name }}</td>
            </tr>
            <tr>
                <td style="border: none;">Produk</td>
                <td style="border: none;">: {{ $tabungan->produkTabungan->nama_produk }}</td>
            </tr>
            <tr>
                <td style="border: none;">Saldo</td>
                <td style="border: none;">: Rp {{ number_format($saldo_berjalan, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kode Transaksi</th>
                <th>Debet</th>
                <th>Kredit</th>
                <th>PC</th>
                <th>Saldo</th>
                <th>Kode Teller</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaksi as $t)
            <tr>
                <td>{{ \Carbon\Carbon::parse($t->tanggal_transaksi)->format('d/m/Y H:i:s') }}</td>
                <td>{{ $t->kode_transaksi }}</td>
                <td style="text-align: right">
                    {{ $t->jenis_transaksi === 'debit' ? 'Rp ' . number_format($t->jumlah, 0, ',', '.') : '-' }}
                </td>
                <td style="text-align: right">
                    {{ $t->jenis_transaksi === 'kredit' ? 'Rp ' . number_format($t->jumlah, 0, ',', '.') : '-' }}
                </td>
                <td></td>
                <td style="text-align: right">
                    Rp {{ number_format($t->saldo_berjalan, 0, ',', '.') }}
                </td>
                <td>{{ $t->kode_teller }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
