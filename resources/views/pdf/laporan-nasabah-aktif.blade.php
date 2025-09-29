<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Nasabah Aktif</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #1a1a1a;
            background-color: #ffffff;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            padding: 15px 0;
            border-bottom: 3px solid #1f2937;
            background-color: #f8fafc;
            border-radius: 8px 8px 0 0;
        }

        .header h1 {
            font-size: 18px;
            font-weight: 900;
            margin-bottom: 8px;
            color: #111827;
        }

        .header h2 {
            font-size: 15px;
            margin-bottom: 5px;
            color: #374151;
            font-weight: 700;
        }

        .header p {
            font-size: 11px;
            color: #6b7280;
            font-weight: 500;
        }

        .info-section {
            margin-bottom: 20px;
            background-color: #f1f5f9;
            padding: 15px;
            border-radius: 8px;
            border: 2px solid #cbd5e1;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            padding: 2px 0;
        }

        .info-label {
            font-weight: 700;
            width: 40%;
            color: #1f2937;
        }

        .info-value {
            width: 55%;
            color: #374151;
            font-weight: 600;
        }

        .stats-section {
            margin-bottom: 20px;
        }

        .stats-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            gap: 10px;
        }

        .stat-box {
            width: 30%;
            text-align: center;
            padding: 12px 8px;
            border: 2px solid #3b82f6;
            border-radius: 8px;
            background-color: #dbeafe;
        }

        .stat-box:nth-child(2) {
            border-color: #10b981;
            background-color: #d1fae5;
        }

        .stat-box:nth-child(3) {
            border-color: #f59e0b;
            background-color: #fef3c7;
        }

        .stat-value {
            font-size: 16px;
            font-weight: 900;
            color: #1f2937;
        }

        .stat-label {
            font-size: 9px;
            color: #374151;
            margin-top: 3px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            border: 2px solid #374151;
            border-radius: 8px;
            overflow: hidden;
        }

        .table th,
        .table td {
            border: 1px solid #6b7280;
            padding: 8px 6px;
            text-align: left;
            font-size: 9px;
        }

        .table th {
            background-color: #374151;
            color: #ffffff;
            font-weight: 700;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .table tr:nth-child(odd) {
            background-color: #ffffff;
        }

        .table tfoot tr {
            background-color: #e5e7eb;
            font-weight: 700;
            color: #1f2937;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 25px;
            text-align: right;
            font-size: 9px;
            color: #6b7280;
            font-weight: 500;
            border-top: 2px solid #e5e7eb;
            padding-top: 10px;
        }

        .page-break {
            page-break-before: always;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .stat-box,
            .info-section,
            .header,
            .table th {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>KOPERASI SIMPAN PINJAM SINARA ARTHA</h1>
        <h2>LAPORAN NASABAH AKTIF</h2>
        <p>Periode: {{ $dateFrom }} s/d {{ $dateTo }}</p>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Periode Laporan:</span>
            <span class="info-value">{{ $dateFrom }} s/d {{ $dateTo }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Jenis Transaksi:</span>
            <span class="info-value">
                @if($transactionType === 'all')
                    Semua Transaksi (Tabungan & Pinjaman)
                @elseif($transactionType === 'savings')
                    Transaksi Tabungan
                @else
                    Transaksi Pinjaman
                @endif
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Cetak:</span>
            <span class="info-value">{{ $generatedAt }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Nasabah Aktif:</span>
            <span class="info-value">{{ number_format($data->count()) }} nasabah</span>
        </div>
    </div>

    <div class="stats-section">
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-value">{{ number_format($data->count()) }}</div>
                <div class="stat-label">Total Nasabah Aktif</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">{{ number_format($data->sum('total_savings_transactions')) }}</div>
                <div class="stat-label">Total Transaksi Tabungan</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">{{ number_format($data->sum('total_loan_transactions')) }}</div>
                <div class="stat-label">Total Transaksi Pinjaman</div>
            </div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">No. Identitas</th>
                <th width="20%">Nama Lengkap</th>
                <th width="12%">No. Telepon</th>
                <th width="12%">Transaksi Tabungan Terakhir</th>
                <th width="12%">Transaksi Pinjaman Terakhir</th>
                <th width="8%">Jml Trx Tabungan</th>
                <th width="8%">Jml Trx Pinjaman</th>
                <th width="8%">Total Transaksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $nasabah)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $nasabah->no_identity ?: '-' }}</td>
                    <td>{{ $nasabah->first_name }} {{ $nasabah->last_name }}</td>
                    <td>{{ $nasabah->phone ?: '-' }}</td>
                    <td class="text-center">
                        {{ $nasabah->last_savings_transaction ? \Carbon\Carbon::parse($nasabah->last_savings_transaction)->format('d/m/Y') : '-' }}
                    </td>
                    <td class="text-center">
                        {{ $nasabah->last_loan_transaction ? \Carbon\Carbon::parse($nasabah->last_loan_transaction)->format('d/m/Y') : '-' }}
                    </td>
                    <td class="text-center">{{ number_format($nasabah->total_savings_transactions ?: 0) }}</td>
                    <td class="text-center">{{ number_format($nasabah->total_loan_transactions ?: 0) }}</td>
                    <td class="text-center">{{ number_format(($nasabah->total_savings_transactions ?: 0) + ($nasabah->total_loan_transactions ?: 0)) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #e5e7eb; font-weight: bold;">
                <td colspan="6" class="text-center">TOTAL</td>
                <td class="text-center">{{ number_format($data->sum('total_savings_transactions')) }}</td>
                <td class="text-center">{{ number_format($data->sum('total_loan_transactions')) }}</td>
                <td class="text-center">{{ number_format($data->sum('total_savings_transactions') + $data->sum('total_loan_transactions')) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis pada {{ $generatedAt }}</p>
        <p>Kospin Sinara Artha - Sistem Informasi Koperasi</p>
    </div>
</body>
</html>