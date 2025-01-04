<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pencairan Deposito</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        .title {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .signature {
            text-align: center;
            margin-top: 50px;
            width: 100%;
        }
        .signature-box {
            display: inline-block;
            width: 45%;
            text-align: center;
        }
        .date {
            text-align: right;
            margin-top: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
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
        .total {
            font-size: 14px;
            font-weight: bold;
            text-align: right;
            margin: 20px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo_koperasi.jpg') }}" alt="Logo Koperasi">
        <h1 class="title">BUKTI PENCAIRAN DEPOSITO</h1>
    </div>

    <table>
        <tr>
            <th width="30%">Nomor Rekening</th>
            <td>{{ $deposito->nomor_rekening }}</td>
        </tr>
        <tr>
            <th>Nama Nasabah</th>
            <td>{{ $deposito->profile->first_name }} {{ $deposito->profile->last_name }}</td>
        </tr>
        <tr>
            <th>Tanggal Pembukaan</th>
            <td>{{ $deposito->tanggal_pembukaan->format('d M Y') }}</td>
        </tr>
        <tr>
            <th>Tanggal Jatuh Tempo</th>
            <td>{{ $deposito->tanggal_jatuh_tempo->format('d M Y') }}</td>
        </tr>
        <tr>
            <th>Jangka Waktu</th>
            <td>{{ $deposito->jangka_waktu }} Bulan</td>
        </tr>
        <tr>
            <th>Nominal Penempatan</th>
            <td>Rp {{ number_format($deposito->nominal_penempatan, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Suku Bunga</th>
            <td>{{ $deposito->rate_bunga }}%</td>
        </tr>
        <tr>
            <th>Nominal Bunga</th>
            <td>Rp {{ number_format($deposito->nominal_bunga, 2, ',', '.') }}</td>
        </tr>
    </table>

    <div class="total">
        Total Pencairan: Rp {{ number_format($deposito->nominal_penempatan + $deposito->nominal_bunga, 2, ',', '.') }}
    </div>

    <div class="date">
        Surabaya, {{ date('d/m/Y') }}
    </div>

    <div class="signature">
        <div class="signature-box">
            Nasabah,<br><br><br><br>
            ({{ $deposito->profile->first_name }} {{ $deposito->profile->last_name }})
        </div>
        <div class="signature-box">
            Teller,<br><br><br><br>
            (................................)
        </div>
    </div>

</body>
</html>
