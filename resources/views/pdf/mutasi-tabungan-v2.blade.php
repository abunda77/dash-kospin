<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mutasi Tabungan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
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
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo_koperasi.jpg') }}" alt="Logos">
        <h2>MUTASI TABUNGAN</h2>
    </div>

    <div class="info">
        <p><strong>No Rekening:</strong> {{ $tabungan->no_tabungan }}</p>
        <p><strong>Nama:</strong> {{ $tabungan->profile?->first_name }} {{ $tabungan->profile?->last_name }}</p>
        <p><strong>Jenis Tabungan:</strong> {{ $tabungan->produkTabungan?->nama_produk }}</p>
        <p><strong>Saldo:</strong> Rp {{ number_format($saldo_berjalan, 0, ',', '.') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kode Transaksi</th>
                <th>Kredit</th>
                <th>Debit</th>
                <th>Saldo</th>
                <th>Teller</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaksi as $t)
            <tr>
                <td>{{ $t->tanggal_transaksi->format('d/m/Y H:i:s') }}</td>
                <td>{{ $t->kode_transaksi }}</td>
                <td class="text-right">
                    {{ $t->jenis_transaksi === 'debit' ? number_format($t->jumlah, 0, ',', '.') : '' }}
                </td>
                <td class="text-right">
                    {{ $t->jenis_transaksi === 'kredit' ? number_format($t->jumlah, 0, ',', '.') : '' }}
                </td>
                <td class="text-right">{{ number_format($t->saldo_berjalan, 0, ',', '.') }}</td>
                <td>{{ $t->admin->name ?? $t->kode_teller }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px; padding: 10px; border: 1px solid #000;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; width: 33%;">
                    <strong>Total Kredit:</strong><br>
                    Rp {{ number_format($transaksi->where('jenis_transaksi', 'debit')->sum('jumlah'), 0, ',', '.') }}
                </td>
                <td style="border: none; width: 33%;">
                    <strong>Total Debit:</strong><br>
                    Rp {{ number_format($transaksi->where('jenis_transaksi', 'kredit')->sum('jumlah'), 0, ',', '.') }}
                </td>
                <td style="border: none; width: 33%;">
                    <strong>Saldo Akhir:</strong><br>
                    Rp {{ number_format($transaksi->last()->saldo_berjalan, 2, ',', '.') }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
