<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tabel Mutasi Tabungan</title>
    <style>
        body {
            font-family: "Courier New", Courier, monospace;
            font-size: 12px;
            line-height: 1.4;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .filter-info {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <table style="border: 0;">
        <tbody>
            @foreach($transaksi as $row)
                <tr>
                    <td style="border: 0;">{{ $row->tanggal_transaksi }}</td>
                    <td style="border: 0;">{{ $row->kode_transaksi }}</td>
                    <td style="border: 0; text-align: right">
                        {{ $row->jenis_transaksi === 'debit' ? number_format($row->jumlah, 2, ',', '.') : '' }}
                    </td>
                    <td style="border: 0; text-align: right">
                        {{ $row->jenis_transaksi === 'kredit' ? number_format($row->jumlah, 2, ',', '.') : '' }}
                    </td>
                    <td style="border: 0;"></td>
                    <td style="border: 0; text-align: right">
                        {{ number_format($row->saldo_berjalan, 2, ',', '.') }}
                    </td>
                    <td style="border: 0;">{{ $row->kode_teller }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
