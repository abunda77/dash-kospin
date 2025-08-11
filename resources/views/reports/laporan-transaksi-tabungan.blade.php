<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi Tabungan</title>
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
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(3, 1fr); 
            gap: 10px; 
            margin-bottom: 15px; 
        }
        .stat-card { 
            border: 1px solid #ddd; 
            padding: 8px; 
            border-radius: 3px; 
            background: #f9f9f9;
            font-size: 9px;
        }
        .stat-value { 
            font-size: 12px; 
            font-weight: bold; 
            color: #2563eb; 
            margin-bottom: 2px;
        }
        .stat-label { 
            color: #666; 
            font-size: 8px;
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
        /* Column widths for landscape */
        .col-no { width: 4%; text-align: center; }
        .col-tanggal { width: 12%; text-align: center; }
        .col-no-tabungan { width: 12%; }
        .col-nasabah { width: 18%; }
        .col-jenis { width: 10%; text-align: center; }
        .col-jumlah { width: 15%; text-align: right; }
        .col-keterangan { width: 20%; }
        .col-teller { width: 9%; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .debit { color: #10b981; font-weight: bold; }
        .kredit { color: #f59e0b; font-weight: bold; }
        .footer { 
            margin-top: 15px; 
            text-align: right; 
            font-size: 9px;
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
    </style>
</head>
<body>
    <div class="header">
        <h2>KOSPIN SINARA ARTHA</h2>
        <p>Laporan Transaksi Tabungan</p>
        @if(isset($periode))<p>{{ $periode }}</p>@endif
        @if(isset($productName))<p>Produk: {{ $productName }}</p>@endif
        <p>Tanggal Cetak: {{ $generatedAt ?? now()->format('d M Y H:i:s') }}</p>
    </div>

    @if(isset($stats))
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ \App\Helpers\PdfHelper::formatCurrency($stats['total_deposits'] ?? 0) }}</div>
            <div class="stat-label">Total Setoran ({{ number_format($stats['deposit_count'] ?? 0) }} transaksi)</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ \App\Helpers\PdfHelper::formatCurrency($stats['total_withdrawals'] ?? 0) }}</div>
            <div class="stat-label">Total Penarikan ({{ number_format($stats['withdrawal_count'] ?? 0) }} transaksi)</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ \App\Helpers\PdfHelper::formatCurrency(($stats['total_deposits'] ?? 0) - ($stats['total_withdrawals'] ?? 0)) }}</div>
            <div class="stat-label">Net Flow</div>
        </div>
    </div>
    @endif

    <h3>Daftar Transaksi</h3>
    <table>
        <thead>
            <tr>
                <th class="col-no">No.</th>
                <th class="col-tanggal">Tanggal</th>
                <th class="col-no-tabungan">No. Tabungan</th>
                <th class="col-nasabah">Nasabah</th>
                <th class="col-jenis">Jenis</th>
                <th class="col-jumlah">Jumlah</th>
                <th class="col-keterangan">Keterangan</th>
                <th class="col-teller">Teller</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalSetoran = 0; 
                $totalPenarikan = 0;
                $JENIS_SETORAN = 'setoran';
            @endphp
            @foreach($transactionData as $index => $transaksi)
                @php
                    // Handle both model and lean array
                    $isArray = is_array($transaksi);
                    $jenisTransaksi = $isArray ? ($transaksi['jenis_transaksi'] ?? '') : ($transaksi->jenis_transaksi ?? '');
                    $jumlah = $isArray ? ($transaksi['jumlah'] ?? 0) : ($transaksi->jumlah ?? 0);
                    
                    if ($jenisTransaksi === $JENIS_SETORAN) {
                        $totalSetoran += $jumlah;
                        $jenisClass = 'debit';
                        $jenisText = 'Setoran';
                    } else {
                        $totalPenarikan += $jumlah;
                        $jenisClass = 'kredit';
                        $jenisText = 'Penarikan';
                    }
                    
                    $tanggalTransaksi = $isArray ? ($transaksi['tanggal_transaksi'] ?? null) : ($transaksi->tanggal_transaksi ?? null);
                    $noTabungan = \App\Helpers\PdfHelper::cleanUtf8String($isArray ? ($transaksi['tabungan']['no_tabungan'] ?? '') : ($transaksi->tabungan->no_tabungan ?? ''));
                    $userName = \App\Helpers\PdfHelper::cleanUtf8String($isArray ? ($transaksi['tabungan_user_name'] ?? ($transaksi['tabungan']['profile']['user']['name'] ?? '')) : ($transaksi->tabungan->profile?->user?->name ?? ''));
                    $keterangan = \App\Helpers\PdfHelper::cleanUtf8String($isArray ? ($transaksi['keterangan'] ?? '') : ($transaksi->keterangan ?? ''));
                    $adminName = \App\Helpers\PdfHelper::cleanUtf8String($isArray ? ($transaksi['admin']['name'] ?? '') : ($transaksi->admin?->name ?? ''));
                @endphp
                <tr>
                    <td class="col-no text-center">{{ $index + 1 }}</td>
                    <td class="col-tanggal date">{{ $tanggalTransaksi ? \Carbon\Carbon::parse($tanggalTransaksi)->format('d M Y H:i') : '-' }}</td>
                    <td class="col-no-tabungan">{{ $noTabungan ?: 'N/A' }}</td>
                    <td class="col-nasabah name-text">{{ $userName ?: 'N/A' }}</td>
                    <td class="col-jenis text-center {{ $jenisClass }}">{{ $jenisText }}</td>
                    <td class="col-jumlah currency">{{ \App\Helpers\PdfHelper::formatCurrency($jumlah) }}</td>
                    <td class="col-keterangan">{{ $keterangan ?: '-' }}</td>
                    <td class="col-teller name-text">{{ $adminName ?: 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-right" style="font-weight: bold;">Total Setoran</th>
                <th class="col-jumlah currency debit" style="font-weight: bold;">{{ \App\Helpers\PdfHelper::formatCurrency($totalSetoran) }}</th>
                <th colspan="3"></th>
            </tr>
            <tr>
                <th colspan="4" class="text-right" style="font-weight: bold;">Total Penarikan</th>
                <th class="col-jumlah currency kredit" style="font-weight: bold;">{{ \App\Helpers\PdfHelper::formatCurrency($totalPenarikan) }}</th>
                <th colspan="3"></th>
            </tr>
            <tr>
                <th colspan="4" class="text-right" style="font-weight: bold;">Net Flow</th>
                <th class="col-jumlah currency" style="font-weight: bold;">{{ \App\Helpers\PdfHelper::formatCurrency($totalSetoran - $totalPenarikan) }}</th>
                <th colspan="3"></th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Total Transaksi: {{ is_countable($transactionData) ? count($transactionData) : (method_exists($transactionData,'count') ? $transactionData->count() : 0) }} | Setoran: {{ \App\Helpers\PdfHelper::formatCurrency($totalSetoran) }} | Penarikan: {{ \App\Helpers\PdfHelper::formatCurrency($totalPenarikan) }}
    </div>
</body>
</html>