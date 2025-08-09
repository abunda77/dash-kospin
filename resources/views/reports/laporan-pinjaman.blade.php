<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pinjaman</title>
    <style>
        @page { 
            margin: 15mm; 
            size: A4 landscape; 
        }
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            margin: 0; 
            font-size: 10px; 
            line-height: 1.2;
        }
        .header { 
            text-align: center; 
            margin-bottom: 15px; 
        }
        .header h2 { 
            margin: 0; 
            padding: 0; 
            font-size: 16px;
            font-weight: bold;
        }
        .header p { 
            margin: 3px 0; 
            font-size: 11px;
        }
        .summary-box {
            margin-bottom: 15px; 
            border: 1px solid #ddd; 
            padding: 8px; 
            background: #f9f9f9;
            font-size: 9px;
            line-height: 1.3;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 8px;
            table-layout: fixed;
        }
        table th, table td { 
            border: 1px solid #ccc; 
            padding: 3px 2px; 
            text-align: left; 
            word-wrap: break-word;
            overflow: hidden;
        }
        table th { 
            background-color: #e8e8e8; 
            font-weight: bold;
            text-align: center;
            font-size: 8px;
        }
        /* Optimized column widths */
        .col-no { width: 4%; text-align: center; }
        .col-no-pinjaman { width: 12%; }
        .col-nama { width: 18%; }
        .col-produk { width: 15%; }
        .col-jumlah { width: 15%; text-align: right; }
        .col-tgl-pinjaman { width: 12%; text-align: center; }
        .col-jatuh-tempo { width: 12%; text-align: center; }
        .col-status { width: 12%; text-align: center; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { 
            margin-top: 15px; 
            text-align: right; 
            font-size: 9px;
        }
        .page-break { page-break-before: always; }
        
        /* Responsive text sizing */
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
    </style>
</head>
<body>
    <div class="header">
        <h2>KOSPIN SINARA ARTHA</h2>
        <p>Laporan Pinjaman</p>
        @if(isset($periode))<p>{{ $periode }}</p>@endif
        <p>Tanggal Cetak: {{ now()->format('d/m/Y') }}</p>
    </div>

    @if(isset($stats))
    <div class="summary-box">
        <strong>Ringkasan:</strong><br>
        Pinjaman Aktif: {{ number_format($stats['active_loans'] ?? 0) }}<br>
        Total Pinjaman: {{ \App\Helpers\PdfHelper::formatCurrency($stats['total_loan_amount'] ?? 0) }}<br>
        Rata-rata Pinjaman: {{ \App\Helpers\PdfHelper::formatCurrency($stats['avg_loan_amount'] ?? 0) }}<br>
        Pinjaman Jatuh Tempo: {{ number_format($stats['overdue_loans'] ?? 0) }}<br>
    </div>
    @endif

    <h3>Daftar Pinjaman</h3>
    <table>
        <thead>
            <tr>
                <th class="col-no">No.</th>
                <th class="col-no-pinjaman">No. Pinjaman</th>
                <th class="col-nama">Nama Nasabah</th>
                <th class="col-produk">Produk</th>
                <th class="col-jumlah">Jumlah Pinjaman</th>
                <th class="col-tgl-pinjaman">Tanggal Pinjaman</th>
                <th class="col-jatuh-tempo">Jatuh Tempo</th>
                <th class="col-status">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $totalPinjaman = 0; @endphp
            @foreach($pinjamans as $index => $p)
                @php
                    // $p can be model or lean array
                    $isArray = is_array($p);
                    $jumlah = $isArray ? ($p['jumlah_pinjaman'] ?? 0) : ($p->jumlah_pinjaman ?? 0);
                    $totalPinjaman += $jumlah;
                    $noPinjaman = \App\Helpers\PdfHelper::cleanUtf8String($isArray ? ($p['no_pinjaman'] ?? '') : ($p->no_pinjaman ?? ''));
                    $userName = \App\Helpers\PdfHelper::cleanUtf8String($isArray ? ($p['pinjaman_user_name'] ?? $p['user_name'] ?? ($p['profile']['user']['name'] ?? '')) : ($p->profile->user->name ?? ''));
                    $produkName = \App\Helpers\PdfHelper::cleanUtf8String($isArray ? ($p['pinjaman_produk_nama'] ?? ($p['produkPinjaman']['nama_produk'] ?? '')) : ($p->produkPinjaman->nama_produk ?? ''));
                    $tanggalPinjaman = $isArray ? ($p['tanggal_pinjaman'] ?? null) : ($p->tanggal_pinjaman ?? null);
                    $tanggalJatuhTempo = $isArray ? ($p['tanggal_jatuh_tempo'] ?? null) : ($p->tanggal_jatuh_tempo ?? null);
                    $statusPinjaman = \App\Helpers\PdfHelper::cleanUtf8String($isArray ? ($p['status_pinjaman'] ?? '') : ($p->status_pinjaman ?? ''));
                @endphp
                <tr>
                    <td class="col-no text-center">{{ $index + 1 }}</td>
                    <td class="col-no-pinjaman">{{ $noPinjaman }}</td>
                    <td class="col-nama name-text">{{ $userName }}</td>
                    <td class="col-produk">{{ $produkName }}</td>
                    <td class="col-jumlah currency">{{ \App\Helpers\PdfHelper::formatCurrency($jumlah) }}</td>
                    <td class="col-tgl-pinjaman date">{{ \App\Helpers\PdfHelper::formatDate($tanggalPinjaman) }}</td>
                    <td class="col-jatuh-tempo date">{{ \App\Helpers\PdfHelper::formatDate($tanggalJatuhTempo) }}</td>
                    <td class="col-status text-center">{{ ucfirst($statusPinjaman) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-right" style="font-weight: bold;">Total</th>
                <th class="col-jumlah currency" style="font-weight: bold;">{{ \App\Helpers\PdfHelper::formatCurrency($totalPinjaman) }}</th>
                <th colspan="3"></th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Total Pinjaman: {{ is_countable($pinjamans) ? count($pinjamans) : (method_exists($pinjamans,'count') ? $pinjamans->count() : 0) }} | Nilai Total: {{ \App\Helpers\PdfHelper::formatCurrency($totalPinjaman) }}
    </div>
</body>
</html>
