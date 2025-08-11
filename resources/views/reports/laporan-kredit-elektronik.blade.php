<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Kredit Elektronik</title>
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
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .filter-info {
            background-color: #f5f5f5;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
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
            font-size: 11px;
        }
        td {
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .status {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .status-aktif { background-color: #d1fae5; color: #065f46; }
        .status-lunas { background-color: #dbeafe; color: #1e40af; }
        .status-macet { background-color: #fee2e2; color: #991b1b; }
        .status-dibatalkan { background-color: #f3f4f6; color: #374151; }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN KREDIT ELEKTRONIK</h1>
        <p>Koperasi Simpan Pinjam</p>
        <p>Periode: {{ \Carbon\Carbon::parse($filters['tanggal_mulai'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filters['tanggal_akhir'])->format('d/m/Y') }}</p>
    </div>

    <div class="filter-info">
        <strong>Filter Laporan:</strong><br>
        Status: {{ $filters['status_kredit'] === 'all' ? 'Semua Status' : ucfirst($filters['status_kredit']) }}<br>
        Jenis Barang: {{ $filters['jenis_barang'] === 'all' ? 'Semua Jenis' : $filters['jenis_barang'] }}
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['total_kredit']) }}</div>
            <div class="stat-label">Total Kredit</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ format_rupiah($stats['total_harga_barang']) }}</div>
            <div class="stat-label">Total Harga Barang</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ format_rupiah($stats['total_uang_muka']) }}</div>
            <div class="stat-label">Total Uang Muka</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ format_rupiah($stats['total_nilai_hutang']) }}</div>
            <div class="stat-label">Total Nilai Hutang</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ format_rupiah($stats['rata_rata_harga']) }}</div>
            <div class="stat-label">Rata-rata Harga</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Barang</th>
                <th>Anggota</th>
                <th>Barang</th>
                <th>Harga Barang</th>
                <th>Uang Muka</th>
                <th>Nilai Hutang</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->kode_barang }}</td>
                    <td>{{ $item->pinjaman->profile->nama_lengkap ?? 'N/A' }}</td>
                    <td>
                        <strong>{{ $item->nama_barang }}</strong><br>
                        <small>{{ $item->jenis_barang }} - {{ $item->merk }}</small>
                    </td>
                    <td class="text-right">{{ format_rupiah($item->harga_barang) }}</td>
                    <td class="text-right">{{ format_rupiah($item->uang_muka) }}</td>
                    <td class="text-right">{{ format_rupiah($item->nilai_hutang) }}</td>
                    <td class="text-center">
                        <span class="status status-{{ $item->status_kredit }}">
                            {{ ucfirst($item->status_kredit) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data kredit elektronik</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>