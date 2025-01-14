<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tabel Mutasi Tabungan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 0px solid #000;
            padding: 1px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
        }
        .text-right {
            text-align: right;
        }
        .info {
            margin-bottom: 15px;
            font-size: 10px;
        }
        {!! $customStyles ?? '' !!}
    </style>
</head>
<body>
    {{-- <div class="header">
        <h3>TABEL MUTASI TABUNGAN</h3>
    </div>

    <div class="info">
        <p><strong>No Rekening:</strong> {{ $tabungan->no_tabungan }}</p>
        <p><strong>Nama:</strong> {{ $tabungan->profile?->nama }}</p>
    </div> --}}

    <table>
        <thead>
            {{-- <tr>
                <th>Tgl</th>
                <th>Kode</th>
                <th>D/K</th>
                <th>Jumlah</th>
                <th>Saldo</th>
                <th>Keterangan</th>
            </tr> --}}
        </thead>
        <tbody>
            @foreach($transaksi as $t)
            <tr>
                <td style="width: 13%;">{{ $t->tanggal_transaksi->format('d/m/y') }}</td>
                <td style="text-align: left;width: 7.2%;">{{ $t->kode_transaksi }}</td>
                <td class="text-right" style="width: 15.9%;">{{ $t->jenis_transaksi === 'kredit' ? number_format($t->jumlah, 0, ',', '.') : '' }}</td>
                <td class="text-right" style="width: 18.1%;">{{ $t->jenis_transaksi === 'debit' ? number_format($t->jumlah, 0, ',', '.') : '' }}</td>
                <td style="width: 5.1%;"></td>
                <td class="text-right" style="width: 24.6%;">{{ number_format($t->saldo_berjalan, 0, ',', '.') }}</td>
                <td style="text-align: right;width: 15.9%;">{{ $t->kode_teller }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
