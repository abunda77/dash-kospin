<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pinjaman</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; margin: 20px; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; }
        .header p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { border: 1px solid #ddd; padding: 5px; text-align: left; }
        table th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .footer { margin-top: 30px; text-align: right; }
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
    <div style="margin-bottom: 20px; border:1px solid #ddd; padding:10px; background:#f9f9f9;">
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
                <th>No.</th>
                <th>No. Pinjaman</th>
                <th>Nama Nasabah</th>
                <th>Produk</th>
                <th>Jumlah Pinjaman</th>
                <th>Tanggal Pinjaman</th>
                <th>Jatuh Tempo</th>
                <th>Status</th>
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
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $noPinjaman }}</td>
                    <td>{{ $userName }}</td>
                    <td>{{ $produkName }}</td>
                    <td class="text-right">{{ \App\Helpers\PdfHelper::formatCurrency($jumlah) }}</td>
                    <td>{{ \App\Helpers\PdfHelper::formatDate($tanggalPinjaman) }}</td>
                    <td>{{ \App\Helpers\PdfHelper::formatDate($tanggalJatuhTempo) }}</td>
                    <td>{{ ucfirst($statusPinjaman) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-right">Total</th>
                <th class="text-right">{{ \App\Helpers\PdfHelper::formatCurrency($totalPinjaman) }}</th>
                <th colspan="3"></th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Total Pinjaman: {{ is_countable($pinjamans) ? count($pinjamans) : (method_exists($pinjamans,'count') ? $pinjamans->count() : 0) }} | Nilai Total: {{ \App\Helpers\PdfHelper::formatCurrency($totalPinjaman) }}
    </div>
</body>
</html>
