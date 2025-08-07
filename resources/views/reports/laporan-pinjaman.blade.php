<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pinjaman</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            padding: 0;
        }
        .header p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
        }
        .page-break {
            page-break-after: always;
        }        .text-right {
            text-align: right;
        }
        .stats-section {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .stats-title {
            margin: 0 0 15px 0;
            text-align: center;
            color: #333;
            font-size: 14px;
            font-weight: bold;
        }
        .stats-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .stats-table td {
            padding: 5px;
            border: none;
            font-size: 11px;
        }
        .critical-stats {
            background-color: #fff2f2;
            padding: 10px;
            border: 1px solid #ffcccc;
            border-radius: 3px;
            margin-top: 10px;
        }
        .critical-title {
            margin: 0 0 10px 0;
            color: #d32f2f;
            font-size: 12px;
            font-weight: bold;
        }
        .product-stats {
            margin-top: 15px;
            background-color: #f0f8ff;
            padding: 10px;
            border: 1px solid #cce7ff;
            border-radius: 3px;
        }
        .product-title {
            margin: 0 0 10px 0;
            color: #1976d2;
            font-size: 12px;
            font-weight: bold;
        }
        .section-title {
            color: #333;
            margin-bottom: 15px;
            font-size: 14px;
            font-weight: bold;
        }
        .footer-section {
            border-top: 2px solid #333;
            padding-top: 15px;
            margin-top: 30px;
        }
        .footer-table {
            width: 100%;
            border: none;
        }
        .footer-table td {
            border: none;
            font-size: 11px;
        }
    </style>
</head>
<body>    <div class="header">
        <h2>KOSPIN SINARA ARTHA</h2>
        <p>Laporan Pinjaman</p>
        @if(isset($periode))
        <p>{{ $periode }}</p>
        @endif
        <p>Tanggal Cetak: {{ now()->format('d/m/Y') }}</p>
    </div>

    <!-- Statistics Section -->
    <div style="margin-bottom: 30px; border: 1px solid #ddd; padding: 15px; background-color: #f9f9f9;">
        <h3 style="margin: 0 0 15px 0; text-align: center; color: #333;">Ringkasan Statistik</h3>
        
        <!-- Main Stats -->
        <table style="width: 100%; margin-bottom: 15px;">
            <tr>
                <td style="width: 50%; padding: 5px; border: none;">
                    <strong>Pinjaman Aktif:</strong> {{ number_format($stats['active_loans'] ?? 0) }} pinjaman
                </td>
                <td style="width: 50%; padding: 5px; border: none;">
                    <strong>Total Pinjaman:</strong> {{ \App\Helpers\PdfHelper::formatCurrency($stats['total_loan_amount'] ?? 0) }}
                </td>
            </tr>
            <tr>
                <td style="padding: 5px; border: none;">
                    <strong>Rata-rata Pinjaman:</strong> {{ \App\Helpers\PdfHelper::formatCurrency($stats['avg_loan_amount'] ?? 0) }}
                </td>
                <td style="padding: 5px; border: none;">
                    <strong>Pinjaman Jatuh Tempo:</strong> {{ number_format($stats['overdue_loans'] ?? 0) }} pinjaman
                </td>
            </tr>
            <tr>
                <td style="padding: 5px; border: none;">
                    <strong>Total Pembayaran:</strong> {{ \App\Helpers\PdfHelper::formatCurrency($stats['total_payments'] ?? 0) }}
                </td>
                <td style="padding: 5px; border: none;">
                    <strong>Jumlah Transaksi:</strong> {{ number_format($stats['payment_count'] ?? 0) }} transaksi
                </td>
            </tr>
        </table>

        <!-- Critical 90 Days Stats -->
        @if(isset($critical90DaysStats))
        <div style="background-color: #fff2f2; padding: 10px; border: 1px solid #ffcccc; border-radius: 3px;">
            <h4 style="margin: 0 0 10px 0; color: #d32f2f;">⚠️ Statistik Keterlambatan Kritis (> 90 Hari)</h4>
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%; padding: 3px; border: none; font-size: 11px;">
                        <strong>Total Akun Bermasalah:</strong> {{ number_format($critical90DaysStats['total_accounts'] ?? 0) }} akun
                    </td>
                    <td style="width: 50%; padding: 3px; border: none; font-size: 11px;">
                        <strong>Total Tunggakan:</strong> {{ \App\Helpers\PdfHelper::formatCurrency($critical90DaysStats['total_overdue'] ?? 0) }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 3px; border: none; font-size: 11px;">
                        <strong>Persentase Risiko:</strong> {{ number_format($critical90DaysStats['risk_percentage'] ?? 0, 1) }}%
                    </td>
                    <td style="padding: 3px; border: none; font-size: 11px;">
                        <strong>Rata-rata Keterlambatan:</strong> {{ number_format($critical90DaysStats['avg_overdue_days'] ?? 0) }} hari
                    </td>
                </tr>
            </table>
        </div>
        @endif

        <!-- Product Distribution -->
        @if(isset($productDistribution) && !empty($productDistribution))
        <div style="margin-top: 15px; background-color: #f0f8ff; padding: 10px; border: 1px solid #cce7ff; border-radius: 3px;">
            <h4 style="margin: 0 0 10px 0; color: #1976d2;">📊 Distribusi Produk Pinjaman</h4>
            <table style="width: 100%; font-size: 11px;">
                @foreach($productDistribution as $index => $product)
                <tr>
                    <td style="width: 60%; padding: 2px; border: none;">
                        {{ $product['nama_produk'] ?? 'Tidak diketahui' }}
                    </td>
                    <td style="width: 20%; padding: 2px; border: none; text-align: center;">
                        {{ number_format($product['total_loans'] ?? 0) }} pinjaman
                    </td>
                    <td style="width: 20%; padding: 2px; border: none; text-align: right;">
                        {{ \App\Helpers\PdfHelper::formatCurrency($product['total_amount'] ?? 0) }}
                    </td>
                </tr>
                @endforeach
            </table>
        </div>        @endif
    </div>

    <!-- Main Data Table -->
    <div style="margin-top: 20px;">
        <h3 style="color: #333; margin-bottom: 15px;">Daftar Pinjaman</h3>
    </div>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>No. Pinjaman</th>
                <th>Nama Nasabah</th>
                <th>Produk Pinjaman</th>
                <th>Jumlah Pinjaman</th>
                <th>Tanggal Pinjaman</th>
                <th>Jatuh Tempo</th>
                <th>Status</th>
            </tr>
        </thead>        <tbody>
            @php
                $totalPinjaman = 0;
            @endphp            @foreach($pinjamans as $index => $pinjaman)
            @php
                $totalPinjaman += $pinjaman->jumlah_pinjaman;
                // Safe access to prevent errors and clean strings
                $noPinjaman = \App\Helpers\PdfHelper::cleanUtf8String($pinjaman->no_pinjaman ?? null);
                $userName = \App\Helpers\PdfHelper::cleanUtf8String($pinjaman->profile->user->name ?? null);
                $produkName = \App\Helpers\PdfHelper::cleanUtf8String($pinjaman->produkPinjaman->nama_produk ?? null);
                $jumlahPinjaman = $pinjaman->jumlah_pinjaman ?? 0;
                $tanggalPinjaman = $pinjaman->tanggal_pinjaman ?? null;
                $tanggalJatuhTempo = $pinjaman->tanggal_jatuh_tempo ?? null;
                $statusPinjaman = \App\Helpers\PdfHelper::cleanUtf8String($pinjaman->status_pinjaman ?? null);
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $noPinjaman }}</td>
                <td>{{ $userName }}</td>
                <td>{{ $produkName }}</td>
                <td class="text-right">{{ \App\Helpers\PdfHelper::formatCurrency($jumlahPinjaman) }}</td>
                <td>{{ \App\Helpers\PdfHelper::formatDate($tanggalPinjaman) }}</td>
                <td>{{ \App\Helpers\PdfHelper::formatDate($tanggalJatuhTempo) }}</td>
                <td>{{ ucfirst($statusPinjaman) }}</td>
            </tr>
            @endforeach
        </tbody>        <tfoot>
            <tr>
                <th colspan="4" class="text-right">Total</th>
                <th class="text-right">{{ \App\Helpers\PdfHelper::formatCurrency($totalPinjaman) }}</th>
                <th colspan="3"></th>
            </tr>
        </tfoot>
    </table>    <div class="footer">
        <div class="footer-section">
            <table class="footer-table">
                <tr>
                    <td style="width: 50%;">
                        <strong>Ringkasan Laporan:</strong><br>
                        Total Pinjaman: {{ is_countable($pinjamans) ? count($pinjamans) : ($pinjamans ? $pinjamans->count() : 0) }} pinjaman<br>
                        Total Nilai: {{ \App\Helpers\PdfHelper::formatCurrency($totalPinjaman) }}<br>
                        @if(isset($critical90DaysStats) && $critical90DaysStats['total_accounts'] > 0)
                        <span style="color: #d32f2f;">⚠️ Akun Bermasalah: {{ number_format($critical90DaysStats['total_accounts']) }} akun</span>
                        @endif
                    </td>
                    <td style="width: 50%; text-align: right;">
                        <strong>Informasi Cetak:</strong><br>
                        Tanggal: {{ now()->format('d/m/Y H:i') }}<br>
                        Periode: {{ $periode ?? 'Semua Periode' }}<br>
                        Filter Produk: {{ $productName ?? 'Semua Produk' }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
