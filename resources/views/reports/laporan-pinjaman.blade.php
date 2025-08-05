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
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>KOSPIN SINARA ARTHA</h2>
        <p>Laporan Pinjaman</p>
        @if(isset($periode))
        <p>{{ $periode }}</p>
        @endif
        <p>Tanggal Cetak: {{ now()->format('d/m/Y') }}</p>
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
        <p>Total Pinjaman: {{ is_countable($pinjamans) ? count($pinjamans) : ($pinjamans ? $pinjamans->count() : 0) }} pinjaman</p>
        <p>Total Nilai: {{ \App\Helpers\PdfHelper::formatCurrency($totalPinjaman) }}</p>
    </div>
</body>
</html>
