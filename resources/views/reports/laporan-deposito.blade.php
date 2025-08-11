<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Deposito</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .header h2 {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }
        
        .info-section {
            margin-bottom: 20px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            border: 1px solid #ddd;
            padding: 15px;
            text-align: center;
            background-color: #f9f9f9;
        }
        
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        
        .stat-label {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
        
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-ended {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .status-cancelled {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN DEPOSITO</h1>
        <h2>Koperasi Simpan Pinjam</h2>
        <p>
            Periode: {{ \Carbon\Carbon::parse($filters['tanggal_mulai'])->format('d M Y') }} - 
            {{ \Carbon\Carbon::parse($filters['tanggal_akhir'])->format('d M Y') }}
        </p>
    </div>

    <div class="info-section">
        <div class="info-row">
            <strong>Tanggal Cetak:</strong>
            <span>{{ now()->format('d M Y H:i:s') }}</span>
        </div>
        <div class="info-row">
            <strong>Filter Status:</strong>
            <span>
                @if($filters['status'] === 'all')
                    Semua Status
                @else
                    {{ ucfirst($filters['status']) }}
                @endif
            </span>
        </div>
        <div class="info-row">
            <strong>Filter Jangka Waktu:</strong>
            <span>
                @if($filters['jangka_waktu'] === 'all')
                    Semua Jangka Waktu
                @else
                    {{ $filters['jangka_waktu'] }} Bulan
                @endif
            </span>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['total_deposito']) }}</div>
            <div class="stat-label">Total Deposito</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">Rp {{ number_format($stats['total_nominal'], 0, ',', '.') }}</div>
            <div class="stat-label">Total Nominal</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">Rp {{ number_format($stats['total_bunga'], 0, ',', '.') }}</div>
            <div class="stat-label">Total Bunga</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">Rp {{ number_format($stats['rata_rata_nominal'], 0, ',', '.') }}</div>
            <div class="stat-label">Rata-rata Nominal</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">No. Rekening</th>
                <th width="20%">Nama Nasabah</th>
                <th width="15%">Nominal</th>
                <th width="8%">Jangka Waktu</th>
                <th width="10%">Tgl Pembukaan</th>
                <th width="10%">Tgl Jatuh Tempo</th>
                <th width="12%">Bunga</th>
                <th width="8%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $deposito)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $deposito->nomor_rekening }}</td>
                <td>{{ trim($deposito->profile->first_name . ' ' . $deposito->profile->last_name) }}</td>
                <td class="text-right">Rp {{ number_format($deposito->nominal_penempatan, 0, ',', '.') }}</td>
                <td class="text-center">{{ $deposito->jangka_waktu }} Bulan</td>
                <td class="text-center">{{ $deposito->tanggal_pembukaan->format('d/m/Y') }}</td>
                <td class="text-center">{{ $deposito->tanggal_jatuh_tempo->format('d/m/Y') }}</td>
                <td class="text-right">Rp {{ number_format($deposito->nominal_bunga, 0, ',', '.') }}</td>
                <td class="text-center">
                    <span class="status-badge status-{{ $deposito->status }}">
                        @if($deposito->status === 'active')
                            Aktif
                        @elseif($deposito->status === 'ended')
                            Berakhir
                        @else
                            Dibatalkan
                        @endif
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td colspan="3" class="text-center">TOTAL</td>
                <td class="text-right">Rp {{ number_format($data->sum('nominal_penempatan'), 0, ',', '.') }}</td>
                <td colspan="3"></td>
                <td class="text-right">Rp {{ number_format($data->sum('nominal_bunga'), 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    @if($data->count() > 20)
    <div class="page-break">
        <h3>Ringkasan Berdasarkan Status</h3>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Jumlah</th>
                    <th>Total Nominal</th>
                    <th>Total Bunga</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $statusGroups = $data->groupBy('status');
                @endphp
                @foreach($statusGroups as $status => $items)
                <tr>
                    <td>
                        @if($status === 'active')
                            Aktif
                        @elseif($status === 'ended')
                            Berakhir
                        @else
                            Dibatalkan
                        @endif
                    </td>
                    <td class="text-center">{{ $items->count() }}</td>
                    <td class="text-right">Rp {{ number_format($items->sum('nominal_penempatan'), 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($items->sum('nominal_bunga'), 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <h3>Ringkasan Berdasarkan Jangka Waktu</h3>
        <table>
            <thead>
                <tr>
                    <th>Jangka Waktu</th>
                    <th>Jumlah</th>
                    <th>Total Nominal</th>
                    <th>Total Bunga</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $jangkaWaktuGroups = $data->groupBy('jangka_waktu');
                @endphp
                @foreach($jangkaWaktuGroups as $jangkaWaktu => $items)
                <tr>
                    <td>{{ $jangkaWaktu }} Bulan</td>
                    <td class="text-center">{{ $items->count() }}</td>
                    <td class="text-right">Rp {{ number_format($items->sum('nominal_penempatan'), 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($items->sum('nominal_bunga'), 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d M Y H:i:s') }}</p>
        <p>Halaman 1 dari 1</p>
    </div>
</body>
</html>