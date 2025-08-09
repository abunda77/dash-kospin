<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Pinjaman Terpilih</title>
    <style>
        @page { 
            margin: 15mm; 
            size: A4 landscape; 
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            line-height: 1.2;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .report-info {
            font-size: 10px;
            color: #666;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 8px;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 3px 2px;
            text-align: left;
            word-wrap: break-word;
            overflow: hidden;
        }
        th {
            background-color: #e8e8e8;
            font-weight: bold;
            text-align: center;
            font-size: 8px;
        }
        /* Optimized column widths for bulk report */
        .col-no { width: 4%; text-align: center; }
        .col-no-pinjaman { width: 12%; }
        .col-nama { width: 18%; }
        .col-produk { width: 15%; }
        .col-tgl-pinjaman { width: 10%; text-align: center; }
        .col-jumlah { width: 13%; text-align: right; }
        .col-sisa { width: 13%; text-align: right; }
        .col-status { width: 15%; text-align: center; }
        
        .summary {
            margin-top: 15px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            font-size: 9px;
        }
        .summary-item {
            margin-bottom: 3px;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .currency { 
            font-size: 8px; 
            white-space: nowrap; 
        }
        .date { 
            font-size: 8px; 
            white-space: nowrap; 
        }
        .name-text {
            font-size: 8px;
            line-height: 1.1;
        }
        .status-approved { color: #006600; font-weight: bold; }
        .status-pending { color: #cc6600; font-weight: bold; }
        .status-rejected { color: #cc0000; font-weight: bold; }
        .status-default { color: #666666; }
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
                <th class="col-no">No</th>
                <th class="col-no-pinjaman">No. Pinjaman</th>
                <th class="col-nama">Nama Anggota</th>
                <th class="col-produk">Produk Pinjaman</th>
                <th class="col-tgl-pinjaman">Tanggal Pinjaman</th>
                <th class="col-jumlah">Jumlah Pinjaman</th>
                <th class="col-sisa">Sisa Pinjaman</th>
                <th class="col-status">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($loans as $index => $loan)
                <tr>
                    <td class="col-no text-center">{{ $index + 1 }}</td>
                    <td class="col-no-pinjaman">{{ \App\Helpers\PdfHelper::cleanUtf8String($loan->no_pinjaman ?? null) }}</td>
                    <td class="col-nama name-text">{{ \App\Helpers\PdfHelper::cleanUtf8String($loan->profile->user->name ?? null) }}</td>
                    <td class="col-produk">{{ \App\Helpers\PdfHelper::cleanUtf8String($loan->produkPinjaman->nama_produk ?? null) }}</td>
                    <td class="col-tgl-pinjaman date">{{ \App\Helpers\PdfHelper::formatDate($loan->tanggal_pinjaman) }}</td>
                    <td class="col-jumlah currency">{{ \App\Helpers\PdfHelper::formatCurrency($loan->jumlah_pinjaman) }}</td>
                    <td class="col-sisa currency">{{ \App\Helpers\PdfHelper::formatCurrency($loan->sisa_pinjaman ?? $loan->jumlah_pinjaman) }}</td>
                    <td class="col-status text-center">
                        @if($loan->status_pinjaman == 'approved')
                            <span class="status-approved">Disetujui</span>
                        @elseif($loan->status_pinjaman == 'pending')
                            <span class="status-pending">Menunggu</span>
                        @elseif($loan->status_pinjaman == 'rejected')
                            <span class="status-rejected">Ditolak</span>
                        @else
                            <span class="status-default">{{ ucfirst($loan->status_pinjaman) }}</span>
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