<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi Pinjaman</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; margin: 20px; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; }
        .header p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { border: 1px solid #ddd; padding: 5px; text-align: left; font-size: 10px; }
        table th { background-color: #f2f2f2; }
        .footer { margin-top: 30px; text-align: right; }
        .text-right { text-align: right; }
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
                <th>No.</th>
                <th>No. Pinjaman</th>
                <th>Nama Nasabah</th>
                <th>Produk</th>
                <th>Tanggal</th>
                <th>Angsuran Ke</th>
                <th>Pokok</th>
                <th>Bunga</th>
                <th>Denda</th>
                <th>Total Bayar</th>
                <th>Status</th>
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
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $noPinjaman }}</td>
                    <td>{{ $userName }}</td>
                    <td>{{ $produkName }}</td>
                    <td>{{ \App\Helpers\PdfHelper::formatDate($tanggalPembayaran) }}</td>
                    <td>{{ $angsuranKe }}</td>
                    <td class="text-right">{{ \App\Helpers\PdfHelper::formatCurrency($angsuranPokok) }}</td>
                    <td class="text-right">{{ \App\Helpers\PdfHelper::formatCurrency($angsuranBunga) }}</td>
                    <td class="text-right">{{ \App\Helpers\PdfHelper::formatCurrency($denda) }}</td>
                    <td class="text-right">{{ \App\Helpers\PdfHelper::formatCurrency($totalBayar) }}</td>
                    <td>{{ ucfirst($statusPembayaran) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" class="text-right">Total</th>
                <th class="text-right">{{ \App\Helpers\PdfHelper::formatCurrency($totalPokok) }}</th>
                <th class="text-right">{{ \App\Helpers\PdfHelper::formatCurrency($totalBunga) }}</th>
                <th class="text-right">{{ \App\Helpers\PdfHelper::formatCurrency($totalDenda) }}</th>
                <th class="text-right">{{ \App\Helpers\PdfHelper::formatCurrency($totalPembayaran) }}</th>
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
