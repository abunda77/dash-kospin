<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Tabungan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
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
        tr {
            line-height: 1.5em;
        }
        @page {
            margin-top: 120px;
            margin-left: 1px;
            margin-right: 1px;
        }
    </style>
</head>
<body>
    <table>
        <tbody>
            <tr>
                <td style="width: 13%; text-align: center;">{{ $transaksi ? $transaksi->tanggal_transaksi->format('d/m/y') : now()->format('d/m/y') }}</td>
                <td style="text-align: left;width: 7.2%;">001</td>

                <td class="text-right" style="width: 18.1%;"></td>
                <td class="text-right" style="width: 15.9%;">{{ $transaksi ? number_format($transaksi->saldo_berjalan, 0, ',', '.') : number_format($tabungan->saldo, 0, ',', '.') }}</td>
                <td style="width: 5.1%;"></td>
                <td class="text-right" style="width: 24.6%;">{{ $transaksi ? number_format($transaksi->saldo_berjalan, 0, ',', '.') : number_format($tabungan->saldo, 0, ',', '.') }}</td>
                {{-- <td style="text-align: right;width: 15.9%;">{{ $transaksi ? $transaksi->kode_teller : auth()->user()->username ?? '-' }}</td> --}}
                <td style="text-align: right;width: 15.9%;">001</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
