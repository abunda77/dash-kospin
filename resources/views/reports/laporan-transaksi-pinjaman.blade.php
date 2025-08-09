<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi Pinjaman</title>
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
        /* Optimized column widths for transaction report */
        .col-no { width: 3%; text-align: center; }
        .col-no-pinjaman { width: 10%; }
        .col-nama { width: 15%; }
        .col-produk { width: 12%; }
        .col-tanggal { width: 8%; text-align: center; }
        .col-angsuran-ke { width: 6%; text-align: center; }
        .col-pokok { width: 11%; text-align: right; }
        .col-bunga { width: 11%; text-align: right; }
        .col-denda { width: 10%; text-align: right; }
        .col-total { width: 11%; text-align: right; }
        .col-status { width: 3%; text-align: center; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { 
            margin-top: 15px; 
            text-align: right; 
            font-size: 9px;
        }
        
        /* Responsive text sizing */
        .currency { 
            font-size: 7px; 
            white-space: nowrap; 
        }
        .date { 
            font-size: 7px; 
            white-space: nowrap; 
        }
        .name-text {
            font-size: 8px;
            line-height: 1.1;
        }
        .status-text {
            font-size: 7px;
            font-weight: bold;
        }
        .angsuran-text {
            font-size: 8px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>KOSPIN SINARA ARTHA</h2>
        <p>Laporan Transaksi Pinjaman</p>
        <p>{{ $periode }}</p>
        <p>Tanggal Cetak: {{ $tanggal_cetak }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-no">No.</th>
                <th class="col-no-pinjaman">No. Pinjaman</th>
                <th class="col-nama">Nama Nasabah</th>
                <th class="col-produk">Produk</th>
                <th class="col-tanggal">Tanggal</th>
                <th class="col-angsuran-ke">Angsuran Ke</th>
                <th class="col-pokok">Pokok</th>
                <th class="col-bunga">Bunga</th>
                <th class="col-denda">Denda</th>
                <th class="col-total">Total Bayar</th>
                <th class="col-status">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalPokok = 0; $totalBunga = 0; $totalDenda = 0; $totalPembayaran = 0;
            @endphp
            @foreach($transactions as $index => $t)
                @php
                    // $t is lean array produced by leanSanitizeModel
                    $angsuranPokok = $t['angsuran_pokok'] ?? 0;
                    $angsuranBunga = $t['angsuran_bunga'] ?? 0;
                    $denda = $t['denda'] ?? 0;
                    $totalBayar = $t['total_pembayaran'] ?? 0;
                    $totalPokok += $angsuranPokok; $totalBunga += $angsuranBunga; $totalDenda += $denda; $totalPembayaran += $totalBayar;

                    // Flattened / relation derived fields
                    $noPinjaman = \App\Helpers\PdfHelper::cleanUtf8String($t['pinjaman']['no_pinjaman'] ?? ($t['no_pinjaman'] ?? ''));
                    $userName = \App\Helpers\PdfHelper::cleanUtf8String($t['pinjaman_user_name'] ?? ($t['user_name'] ?? ''));
                    $produkName = \App\Helpers\PdfHelper::cleanUtf8String($t['pinjaman_produk_nama'] ?? ($t['pinjaman']['produkPinjaman']['nama_produk'] ?? ''));
                    $tanggalPembayaran = $t['tanggal_pembayaran'] ?? null;
                    $angsuranKe = $t['angsuran_ke'] ?? 0;
                    $statusPembayaran = \App\Helpers\PdfHelper::cleanUtf8String($t['status_pembayaran'] ?? '');
                @endphp
                <tr>
                    <td class="col-no text-center">{{ $index + 1 }}</td>
                    <td class="col-no-pinjaman">{{ $noPinjaman }}</td>
                    <td class="col-nama name-text">{{ $userName }}</td>
                    <td class="col-produk">{{ $produkName }}</td>
                    <td class="col-tanggal date">{{ \App\Helpers\PdfHelper::formatDate($tanggalPembayaran) }}</td>
                    <td class="col-angsuran-ke angsuran-text">{{ $angsuranKe }}</td>
                    <td class="col-pokok currency">{{ \App\Helpers\PdfHelper::formatCurrency($angsuranPokok) }}</td>
                    <td class="col-bunga currency">{{ \App\Helpers\PdfHelper::formatCurrency($angsuranBunga) }}</td>
                    <td class="col-denda currency">{{ \App\Helpers\PdfHelper::formatCurrency($denda) }}</td>
                    <td class="col-total currency">{{ \App\Helpers\PdfHelper::formatCurrency($totalBayar) }}</td>
                    <td class="col-status status-text">{{ ucfirst($statusPembayaran) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" class="text-right" style="font-weight: bold;">Total</th>
                <th class="col-pokok currency" style="font-weight: bold;">{{ \App\Helpers\PdfHelper::formatCurrency($totalPokok) }}</th>
                <th class="col-bunga currency" style="font-weight: bold;">{{ \App\Helpers\PdfHelper::formatCurrency($totalBunga) }}</th>
                <th class="col-denda currency" style="font-weight: bold;">{{ \App\Helpers\PdfHelper::formatCurrency($totalDenda) }}</th>
                <th class="col-total currency" style="font-weight: bold;">{{ \App\Helpers\PdfHelper::formatCurrency($totalPembayaran) }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Total Transaksi: {{ is_countable($transactions) ? count($transactions) : (method_exists($transactions,'count') ? $transactions->count() : 0) }}</p>
        <p>Total Pembayaran: Rp {{ number_format($totalPembayaran, 0, ',', '.') }}</p>
    </div>
</body>
</html>
