<!DOCTYPE html>
<html>
<head>
    <title>Laporan Saldo Tabungan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
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
        tr:nth-child(even) {
            background-color: #e3e5e7;
        }
        tr:nth-child(odd) {
            background-color: #ffffff;
        }
        tr:hover {
            background-color: #f5f5f5;
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
        .date {
            text-align: right;
            margin-bottom: 20px;
            font-size: 12px;
            color: #4a5568;
        }
        td:last-child {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo_koperasi.jpg') }}"
             onerror="this.src='path/to/fallback/image.jpg'">
        <h2>Laporan Saldo Tabungan</h2>
    </div>

    <div class="date">
        Tanggal: {{ date('d/m/Y') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Lengkap</th>
                <th>Nama Depan</th>
                <th>Nama Belakang</th>
                <th>No Rekening</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($saldoTabungans as $index => $saldo)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $saldo->tabungan->profile->first_name }} {{ $saldo->tabungan->profile->last_name }}</td>
                    <td>{{ $saldo->tabungan->profile->first_name }}</td>
                    <td>{{ $saldo->tabungan->profile->last_name }}</td>
                    <td>{{ $saldo->tabungan->no_tabungan }}</td>
                    <td>Rp {{ number_format($saldo->saldo_akhir, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
