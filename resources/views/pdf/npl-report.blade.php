<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan NPL - {{ now()->format('d M Y') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
            padding: 0;
        }
        .header p {
            font-size: 12px;
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.stats {
            margin-bottom: 30px;
        }
        table.stats th, table.stats td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        table.stats th {
            background-color: #f2f2f2;
        }
        table.data th, table.data td {
            border: 1px solid #ddd;
            padding: 6px;
            font-size: 11px;
        }
        table.data th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-danger {
            color: #dc3545;
        }
        .page-break {
            page-break-after: always;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .npl-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            color: white;
            background-color: #3490dc;
        }
        .npl-badge.warning {
            background-color: #f59e0b;
        }
        .npl-badge.danger {
            background-color: #ef4444;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN NON PERFORMING LOAN (NPL)</h1>
        <p>Tanggal: {{ $today->format('d F Y') }}</p>
    </div>

    <h3>Ringkasan NPL</h3>
    
    <table class="stats">
        <tr>
            <th>Rasio NPL</th>
            <th>Total Akun NPL</th>
            <th>Total Nominal NPL</th>
            <th>Total Denda</th>
            <th>Rata-rata Keterlambatan</th>
        </tr>
        <tr>
            <td>{{ number_format($stats['rasio_npl'], 2, ',', '.') }}%</td>
            <td>{{ number_format($stats['total_pinjaman']) }}</td>
            <td>Rp {{ number_format($stats['total_nominal'], 0, ',', '.') }}</td>
            <td>Rp {{ number_format($stats['total_denda'], 0, ',', '.') }}</td>
            <td>{{ number_format($stats['rata_rata_hari_terlambat'], 0) }} hari</td>
        </tr>
    </table>

    <h3>Daftar Pinjaman Bermasalah (NPL)</h3>
    
    <table class="data">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Nama</th>
                <th>No Pinjaman</th>
                <th>Produk</th>
                <th>Nominal</th>
                <th>Tgl. Pinjaman</th>
                <th>Hari Terlambat</th>
                <th>Denda</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>            @forelse($data as $index => $record)
                @php
                    $hariTerlambat = $record->calculated_hari_terlambat;
                    $denda = $record->calculated_denda;
                    
                    $statusClass = 'npl-badge';
                    if ($hariTerlambat >= 180) {
                        $status = 'Kritis';
                        $statusClass .= ' danger';
                    } elseif ($hariTerlambat >= 120) {
                        $status = 'Bermasalah';
                        $statusClass .= ' warning';
                    } else {
                        $status = 'NPL';
                    }
                @endphp                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ trim($record->profile->first_name . ' ' . $record->profile->last_name) }}</td>
                    <td>{{ $record->no_pinjaman }}</td>
                    <td>{{ $record->produkPinjaman->nama_produk ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($record->jumlah_pinjaman, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $record->tanggal_pinjaman->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $hariTerlambat }} hari</td>
                    <td class="text-right">Rp {{ number_format($denda, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <div class="{{ $statusClass }}">{{ $status }}</div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Tidak ada data pinjaman bermasalah</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan Non Performing Loan (NPL) - Dihasilkan pada {{ now()->format('d M Y H:i:s') }}</p>
    </div>
</body>
</html>
