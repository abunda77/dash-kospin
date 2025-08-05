<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keterlambatan Lebih Dari 90 Hari</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
           .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #dc2626;
            padding-bottom: 20px;
        }
        
        .header img {
            height: 100px;
            width: auto;
            margin-bottom: 15px;
            object-fit: contain;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        
        .header h1 {
            color: #dc2626;
            font-size: 18px;
            margin: 0;
            font-weight: bold;
        }
         
        .header p {
            margin: 5px 0;
            color: #666;
        }
        
        .stats-section {
            background-color: #fef2f2;
            border: 2px solid #fecaca;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        .stat-item {
            text-align: center;
            background: white;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #fca5a5;
        }
        
        .stat-value {
            font-size: 14px;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 3px;
        }
        
        .stat-label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
        }
        
        .warning-box {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 12px;
            margin-bottom: 20px;
        }
        
        .warning-box h3 {
            color: #d97706;
            margin: 0 0 8px 0;
            font-size: 12px;
        }
        
        .warning-box p {
            margin: 0;
            font-size: 10px;
            color: #92400e;
            line-height: 1.4;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 9px;
        }
        
        table th {
            background-color: #dc2626;
            color: white;
            padding: 8px 4px;
            text-align: center;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        
        table td {
            padding: 6px 4px;
            border: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #fef2f2;
        }
        
        table tbody tr:hover {
            background-color: #fee2e2;
        }
        
        .text-left {
            text-align: left !important;
        }
        
        .text-right {
            text-align: right !important;
        }
        
        .text-danger {
            color: #dc2626;
            font-weight: bold;
        }
        
        .text-warning {
            color: #d97706;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            border-top: 2px solid #dc2626;
            padding-top: 15px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
          .page-break {
            page-break-before: always;
            margin: 0;
            padding: 0;
            break-before: page;
            clear: both;
            height: 0;
            display: block;
        }
        
        .summary-section {
            background-color: #f3f4f6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .risk-indicator {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            color: white;
        }
        
        .risk-critical {
            background-color: #dc2626;
        }
        
        .risk-high {
            background-color: #ea580c;
        }
        
        .risk-medium {
            background-color: #d97706;
        }
    </style>
</head>
<body>    {{-- Header --}}    
    <div class="header">
        {{-- <img src="{{ public_path('images/logo_koperasi.jpg') }}" alt="Logo Koperasi"> --}}
       
        <h1>LAPORAN KETERLAMBATAN KRITIS</h1>
        <h2 style="margin: 5px 0; font-size: 14px; color: #dc2626;">LEBIH DARI 90 HARI</h2>
        <p>Koperasi SinaraArtha</p>
        <p>Tanggal Cetak: {{ $today->format('d F Y H:i') }} WIB</p>
        <p style="color: #dc2626; font-weight: bold;"># PERHATIAN: TINDAKAN SEGERA DIPERLUKAN #</p>
    </div>

    {{-- Statistics Overview --}}
    <div class="stats-section">
        <h3 style="text-align: center; margin-bottom: 15px; color: #dc2626; font-size: 14px;">
            RINGKASAN STATISTIK KETERLAMBATAN KRITIS
        </h3>
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <tbody style="font-size: 14px;">
                <tr>
                    <td style="padding: 8px; border: 1px solid #fca5a5; background-color: white; text-align: left; font-weight: bold; width: 50%;">Total Akun Bermasalah</td>
                    <td style="padding: 8px; border: 1px solid #fca5a5; background-color: white; text-align: right; color: #dc2626; font-weight: bold; width: 50%;">{{ number_format($stats['total_pinjaman']) }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #fca5a5; background-color: #fef2f2; text-align: left; font-weight: bold;">Nominal Pinjaman</td>
                    <td style="padding: 8px; border: 1px solid #fca5a5; background-color: #fef2f2; text-align: right; color: #dc2626; font-weight: bold;">Rp {{ number_format($stats['total_nominal_pinjaman'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #fca5a5; background-color: white; text-align: left; font-weight: bold;">Total Tunggakan</td>
                    <td style="padding: 8px; border: 1px solid #fca5a5; background-color: white; text-align: right; color: #dc2626; font-weight: bold;">Rp {{ number_format($stats['total_tunggakan'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #fca5a5; background-color: #fef2f2; text-align: left; font-weight: bold;">Total Denda</td>
                    <td style="padding: 8px; border: 1px solid #fca5a5; background-color: #fef2f2; text-align: right; color: #dc2626; font-weight: bold;">Rp {{ number_format(abs($stats['total_denda']), 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #fca5a5; background-color: white; text-align: left; font-weight: bold;">Rata-rata Keterlambatan</td>
                    <td style="padding: 8px; border: 1px solid #fca5a5; background-color: white; text-align: right; color: #dc2626; font-weight: bold;">{{ number_format($stats['rata_rata_hari_terlambat'], 0) }} Hari</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #fca5a5; background-color: #fef2f2; text-align: left; font-weight: bold;">Total Angsuran Pokok</td>
                    <td style="padding: 8px; border: 1px solid #fca5a5; background-color: #fef2f2; text-align: right; color: #dc2626; font-weight: bold;">Rp {{ number_format($stats['total_angsuran_pokok'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #fca5a5; background-color: white; text-align: left; font-weight: bold;">Rasio Pinjaman Bermasalah</td>
                    <td style="padding: 8px; border: 1px solid #fca5a5; background-color: white; text-align: right; color: #dc2626; font-weight: bold;">{{ number_format($stats['rasio_pinjaman_bermasalah'], 2, ',', '.') }}%</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Page Break --}}
    <div class="page-break"></div>
    
    {{-- Warning Notice --}}
    <div class="warning-box">
        <h3>## PEMBERITAHUAN PENTING</h3>
        <p>
            Laporan ini menampilkan daftar pinjaman dengan status keterlambatan KRITIS (lebih dari 90 hari). 
            Akun-akun ini memerlukan tindakan penagihan khusus dan intensif untuk mencegah kerugian yang lebih besar. 
            Pertimbangkan untuk melakukan restructuring atau tindakan hukum sesuai kebijakan koperasi.
        </p>
    </div>

    {{-- Data Table --}}
    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="12%">Nama Nasabah</th>
                <th width="10%">No Pinjaman</th>
                <th width="9%">Nominal Pinjaman</th>
                <th width="9%">Angsuran Pokok</th>
                <th width="8%">Bunga</th>
                <th width="8%">Denda</th>
                <th width="10%">Total Tunggakan</th>
                <th width="8%">Tgl Pinjaman</th>
                <th width="7%">Hari Terlambat</th>
                <th width="8%">Risk Level</th>
                <th width="8%">WhatsApp</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $record)
                @php
                    $angsuranPokok = $record->jumlah_pinjaman / $record->jangka_waktu;
                    $bungaPerBulan = ($record->jumlah_pinjaman * ($record->biayaBungaPinjaman->persentase_bunga/100)) / $record->jangka_waktu;
                    
                    // Hitung hari terlambat
                    $lastTransaction = $record->transaksiPinjaman()->orderBy('angsuran_ke', 'desc')->first();
                    if ($lastTransaction) {
                        $tanggalJatuhTempo = \Carbon\Carbon::parse($lastTransaction->tanggal_pembayaran)->addMonth()->startOfDay();
                    } else {
                        $tanggalJatuhTempo = \Carbon\Carbon::parse($record->tanggal_pinjaman)->addMonth()->startOfDay();
                    }
                    
                    $hariTerlambat = $today->diffInDays($tanggalJatuhTempo);
                    $jumlahBulanTerlambat = ceil($hariTerlambat / 30);
                    
                    // Hitung komponen tunggakan
                    $totalPokok = $angsuranPokok * $jumlahBulanTerlambat;
                    $totalBunga = $bungaPerBulan * $jumlahBulanTerlambat;
                    $angsuranTotal = $angsuranPokok + $bungaPerBulan;
                    $dendaPerHari = (0.05 * $angsuranTotal) / 30;
                    $totalDenda = $dendaPerHari * $hariTerlambat;
                    $totalTunggakan = $totalPokok + $totalBunga + $totalDenda;
                    
                    // Tentukan risk level
                    if ($hariTerlambat >= 180) {
                        $riskLevel = 'CRITICAL';
                        $riskClass = 'risk-critical';
                    } elseif ($hariTerlambat >= 120) {
                        $riskLevel = 'HIGH';
                        $riskClass = 'risk-high';
                    } else {
                        $riskLevel = 'MEDIUM';
                        $riskClass = 'risk-medium';
                    }
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ trim($record->profile->first_name . ' ' . $record->profile->last_name) }}</td>
                    <td>{{ $record->no_pinjaman }}</td>
                    <td class="text-right">Rp {{ number_format($record->jumlah_pinjaman, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($angsuranPokok, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($bungaPerBulan, 0, ',', '.') }}</td>
                    <td class="text-right text-danger">Rp {{ number_format($totalDenda, 0, ',', '.') }}</td>
                    <td class="text-right text-danger">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</td>
                    <td>{{ \Carbon\Carbon::parse($record->tanggal_pinjaman)->format('d/m/Y') }}</td>
                    <td class="text-danger">{{ $hariTerlambat }} hari</td>
                    <td>
                        <span class="risk-indicator {{ $riskClass }}">{{ $riskLevel }}</span>
                    </td>
                    <td>{{ $record->profile->whatsapp ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Summary Section --}}
    <div class="summary-section" style="margin-top: 30px;">
        <h3 style="margin-bottom: 15px; color: #dc2626;">REKOMENDASI TINDAKAN</h3>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; font-size: 10px;">
            <div>
                <h4 style="color: #dc2626; margin-bottom: 8px;">Tindakan Segera (Critical Risk):</h4>
                <ul style="margin: 0; padding-left: 15px; line-height: 1.4;">
                    <li>Kontak langsung via telepon dan kunjungan</li>
                    <li>Negosiasi restrukturisasi pembayaran</li>
                    <li>Evaluasi kemampuan bayar nasabah</li>
                    <li>Pertimbangkan tindakan hukum</li>
                </ul>
            </div>
            <div>
                <h4 style="color: #ea580c; margin-bottom: 8px;">Monitoring Intensif (High Risk):</h4>
                <ul style="margin: 0; padding-left: 15px; line-height: 1.4;">
                    <li>Pengingat harian via WhatsApp</li>
                    <li>Jadwalkan pertemuan dengan nasabah</li>
                    <li>Review agunan dan jaminan</li>
                    <li>Koordinasi dengan tim collection</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p><strong>Koperasi SinaraArtha</strong></p>
        <p>Laporan digenerate otomatis pada {{ $today->format('d F Y H:i:s') }} WIB</p>
        <p style="color: #dc2626; font-weight: bold;">
            ** DOKUMEN RAHASIA - HANYA UNTUK INTERNAL KOPERASI **
        </p>
        <p style="margin-top: 10px; font-size: 9px;">
            Total {{ count($data) }} akun bermasalah | 
            Total Risiko: Rp {{ number_format($stats['total_tunggakan'], 0, ',', '.') }}
        </p>
    </div>
</body>
</html>
