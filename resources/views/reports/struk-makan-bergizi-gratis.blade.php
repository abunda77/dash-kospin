<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Makan Bergizi Sinara</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #333;
            padding: 15px;
            width: 210mm;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2d3748;
        }

        .header h1 {
            font-size: 14pt;
            color: #2d3748;
            margin-bottom: 3px;
            font-weight: bold;
        }

        .header .subtitle {
            font-size: 9pt;
            color: #718096;
        }

        .info-box {
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            padding: 10px;
            margin-bottom: 10px;
        }

        .info-box h3 {
            font-size: 10pt;
            color: #2d3748;
            margin-bottom: 6px;
            font-weight: bold;
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 4px;
        }

        .info-label {
            display: table-cell;
            width: 45%;
            font-size: 9pt;
            color: #4a5568;
        }

        .info-value {
            display: table-cell;
            width: 55%;
            font-size: 9pt;
            color: #2d3748;
            font-weight: 600;
        }

        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 8pt;
            color: #718096;
        }

        .timestamp {
            text-align: right;
            font-size: 7pt;
            color: #a0aec0;
            margin-top: 8px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>STRUK MAKAN BERGIZI GRATIS</h1>
        <div class="subtitle">SINARA ARTHA</div>
        <div class="subtitle">{{ $record->tanggal_pemberian->format('d F Y') }}</div>
    </div>

    <!-- Data Nasabah -->
    <div class="info-box">
        <h3>Data Nasabah</h3>
        <div class="info-row">
            <div class="info-label">Nama Lengkap</div>
            <div class="info-value">{{ $record->data_nasabah['nama_lengkap'] ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">No. Telepon</div>
            <div class="info-value">{{ $record->data_nasabah['phone'] ?? '-' }}</div>
        </div>
    </div>

    <!-- Informasi Checkout -->
    <div class="info-box">
        <h3>Informasi Checkout</h3>
        <div class="info-row">
            <div class="info-label">Tanggal Pemberian</div>
            <div class="info-value">{{ $record->tanggal_pemberian->format('d/m/Y') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Waktu Checkout</div>
            <div class="info-value">{{ $record->scanned_at->format('d/m/Y H:i:s') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">ID Transaksi</div>
            <div class="info-value">#{{ str_pad($record->id, 6, '0', STR_PAD_LEFT) }}</div>
        </div>
    </div>

    <div class="footer">
        <p><strong>Terima kasih telah menggunakan layanan Makan Bergizi Gratis</strong></p>
        <p style="margin-top: 3px;">Struk ini adalah bukti sah checkout program MBG</p>
        <p style="margin-top: 3px;">Untuk informasi lebih lanjut, hubungi customer service kami</p>
    </div>

    <div class="timestamp">
        Dicetak pada: {{ $tanggal_cetak }}
    </div>
</body>

</html>
