<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Pinjaman Terpilih</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-info {
            font-size: 11px;
            color: #666;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .summary-item {
            margin-bottom: 5px;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">KOPERASI SIMPAN PINJAM</div>
        <div class="report-title">{{ $reportTitle ?? 'Laporan Pinjaman Terpilih' }}</div>
        <div class="report-info">
            Dicetak pada: {{ $generatedAt ?? now()->format('d M Y H:i') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">No. Pinjaman</th>
                <th width="20%">Nama Anggota</th>
                <th width="15%">Produk Pinjaman</th>
                <th width="12%">Tanggal Pinjaman</th>
                <th width="12%">Jumlah Pinjaman</th>
                <th width="12%">Sisa Pinjaman</th>
                <th width="9%">Status</th>
            </tr>
        </thead>
        <tbody>            @forelse($loans as $index => $loan)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \App\Helpers\PdfHelper::cleanUtf8String($loan->no_pinjaman ?? null) }}</td>
                    <td>{{ \App\Helpers\PdfHelper::cleanUtf8String($loan->profile->user->name ?? null) }}</td>
                    <td>{{ \App\Helpers\PdfHelper::cleanUtf8String($loan->produkPinjaman->nama_produk ?? null) }}</td>
                    <td>{{ \App\Helpers\PdfHelper::formatDate($loan->tanggal_pinjaman) }}</td>
                    <td class="text-right">{{ \App\Helpers\PdfHelper::formatCurrency($loan->jumlah_pinjaman) }}</td>
                    <td class="text-right">{{ \App\Helpers\PdfHelper::formatCurrency($loan->sisa_pinjaman ?? $loan->jumlah_pinjaman) }}</td>
                    <td class="text-center">
                        @if($loan->status_pinjaman == 'approved')
                            <span style="color: green;">Disetujui</span>
                        @elseif($loan->status_pinjaman == 'pending')
                            <span style="color: orange;">Menunggu</span>
                        @elseif($loan->status_pinjaman == 'rejected')
                            <span style="color: red;">Ditolak</span>
                        @else
                            <span style="color: gray;">{{ ucfirst($loan->status_pinjaman) }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data pinjaman yang ditemukan</td>
                </tr>
            @endforelse
        </tbody>
    </table>    <div class="summary">
        <div class="summary-item">Total Pinjaman: {{ $totalLoans ?? 0 }} pinjaman</div>
        <div class="summary-item">Total Nilai Pinjaman: {{ \App\Helpers\PdfHelper::formatCurrency($totalAmount ?? 0) }}</div>
    </div>

    <div style="margin-top: 30px; font-size: 11px; color: #666;">
        <em>Dicetak oleh sistem pada {{ $generatedAt ?? now()->format('d M Y H:i') }}</em>
    </div>
</body>
</html>