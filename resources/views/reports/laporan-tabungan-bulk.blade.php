<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Tabungan Terpilih</title>
    <style>
        @page { 
            margin: 15mm; 
            size: A4 portrait; 
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
        /* Column widths */
        .col-no { width: 5%; text-align: center; }
        .col-no-tabungan { width: 15%; }
        .col-nama { width: 25%; }
        .col-produk { width: 20%; }
        .col-saldo { width: 15%; text-align: right; }
        .col-tgl-buka { width: 12%; text-align: center; }
        .col-status { width: 8%; text-align: center; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
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
        <p>Laporan Tabungan Terpilih</p>
        <p>Total Rekening: {{ is_countable($records) ? count($records) : (method_exists($records,'count') ? $records->count() : 0) }}</p>
        <p>Tanggal Cetak: {{ $generatedAt ?? now()->format('d M Y H:i:s') }}</p>
    </div>

    @php
        $totalSaldo = 0;
        $totalRekening = is_countable($records) ? count($records) : (method_exists($records,'count') ? $records->count() : 0);
        // Calculate total for summary
        foreach($records as $tabungan) {
            $isArray = is_array($tabungan);
            $saldo = $isArray ? ($tabungan['saldo'] ?? 0) : ($tabungan->saldo ?? 0);
            $totalSaldo += $saldo;
        }
        $avgSaldo = $totalRekening > 0 ? $totalSaldo / $totalRekening : 0;
    @endphp

    <div class="summary-box">
        <strong>Ringkasan:</strong><br>
        Total Rekening: {{ number_format($totalRekening) }}<br>
        Total Saldo: {{ \App\Helpers\PdfHelper::formatCurrency($totalSaldo) }}<br>
        Rata-rata Saldo: {{ \App\Helpers\PdfHelper::formatCurrency($avgSaldo) }}<br>
    </div>

    <h3>Daftar Tabungan Terpilih</h3>
    <table>
        <thead>
            <tr>
                <th class="col-no">No.</th>
                <th class="col-no-tabungan">No. Tabungan</th>
                <th class="col-nama">Nama Nasabah</th>
                <th class="col-produk">Produk</th>
                <th class="col-saldo">Saldo</th>
                <th class="col-tgl-buka">Tanggal Buka</th>
                <th class="col-status">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $runningTotal = 0; @endphp
            @foreach($records as $index => $tabungan)
                @php
                    // Handle both model and lean array
                    $isArray = is_array($tabungan);
                    $saldo = $isArray ? ($tabungan['saldo'] ?? 0) : ($tabungan->saldo ?? 0);
                    $runningTotal += $saldo;
                    $noTabungan = \App\Helpers\PdfHelper::cleanUtf8String($isArray ? ($tabungan['no_tabungan'] ?? '') : ($tabungan->no_tabungan ?? ''));
                    $userName = \App\Helpers\PdfHelper::cleanUtf8String($isArray ? ($tabungan['user_name'] ?? ($tabungan['profile']['user']['name'] ?? '')) : ($tabungan->profile?->user?->name ?? ''));
                    $produkName = \App\Helpers\PdfHelper::cleanUtf8String($isArray ? ($tabungan['produkTabungan']['nama_produk'] ?? '') : ($tabungan->produkTabungan?->nama_produk ?? ''));
                    $tanggalBuka = $isArray ? ($tabungan['tanggal_buka_rekening'] ?? null) : ($tabungan->tanggal_buka_rekening ?? null);
                    $statusRekening = \App\Helpers\PdfHelper::cleanUtf8String($isArray ? ($tabungan['status_rekening'] ?? '') : ($tabungan->status_rekening ?? ''));
                @endphp
                <tr>
                    <td class="col-no text-center">{{ $index + 1 }}</td>
                    <td class="col-no-tabungan">{{ $noTabungan }}</td>
                    <td class="col-nama name-text">{{ $userName ?: 'N/A' }}</td>
                    <td class="col-produk">{{ $produkName ?: 'N/A' }}</td>
                    <td class="col-saldo currency">{{ \App\Helpers\PdfHelper::formatCurrency($saldo) }}</td>
                    <td class="col-tgl-buka date">{{ \App\Helpers\PdfHelper::formatDate($tanggalBuka) }}</td>
                    <td class="col-status text-center">{{ ucfirst($statusRekening) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <th colspan="4" class="text-right" style="font-weight: bold;">TOTAL</th>
                <th class="col-saldo currency" style="font-weight: bold;">{{ \App\Helpers\PdfHelper::formatCurrency($runningTotal) }}</th>
                <th colspan="2"></th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Total Rekening: {{ $totalRekening }} | Total Saldo: {{ \App\Helpers\PdfHelper::formatCurrency($runningTotal) }}
    </div>
</body>
</html>