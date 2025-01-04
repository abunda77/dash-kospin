<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Jatuh Tempo Deposito</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Daftar Jatuh Tempo Deposito</h2>
        <p>
            Periode:
            @switch($periode)
                @case('bulan-ini')
                    Bulan Ini
                    @break
                @case('bulan-depan')
                    Bulan Depan
                    @break
                @case('tahun-ini')
                    Tahun Ini
                    @break
                @case('tahun-depan')
                    Tahun Depan
                    @break
            @endswitch
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. Rekening</th>
                <th>Nama Nasabah</th>
                <th>Nominal</th>
                <th>Jangka Waktu</th>
                <th>Jatuh Tempo</th>
                <th>Bunga</th>
                <th>Total Penarikan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->nomor_rekening }}</td>
                    <td>{{ $item->profile->first_name }} {{ $item->profile->last_name }}</td>
                    <td class="text-right">{{ number_format($item->nominal_penempatan, 2, ',', '.') }}</td>
                    <td>{{ $item->jangka_waktu }} Bulan</td>
                    <td>{{ $item->tanggal_jatuh_tempo->format('d M Y') }}</td>
                    <td class="text-right">{{ number_format($item->nominal_bunga, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->nominal_penempatan + $item->nominal_bunga, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
