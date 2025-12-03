<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Makan Bergizi Gratis</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #2d3748;
        }

        .header h1 {
            font-size: 18pt;
            color: #2d3748;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 10pt;
            color: #718096;
        }

        .info-box {
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 12px;
            margin-bottom: 15px;
        }

        .info-box h3 {
            font-size: 12pt;
            color: #2d3748;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid #cbd5e0;
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }

        .info-label {
            display: table-cell;
            width: 40%;
            font-weight: 600;
            color: #4a5568;
        }

        .info-value {
            display: table-cell;
            width: 60%;
            color: #2d3748;
        }

        .highlight {
            background-color: #c6f6d5;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin: 15px 0;
        }

        .highlight .amount {
            font-size: 16pt;
            font-weight: bold;
            color: #22543d;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9pt;
            font-weight: 600;
        }

        .status-aktif {
            background-color: #c6f6d5;
            color: #22543d;
        }

        .status-setoran {
            background-color: #bee3f8;
            color: #2c5282;
        }

        .footer {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 9pt;
            color: #718096;
        }

        .timestamp {
            text-align: right;
            font-size: 9pt;
            color: #718096;
            margin-top: 10px;
        }

        .divider {
            border-top: 1px dashed #cbd5e0;
            margin: 15px 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1> STRUK MAKAN BERGIZI GRATIS</h1>
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
        @if (!empty($record->data_nasabah['email']))
            <div class="info-row">
                <div class="info-label">Email</div>
                <div class="info-value">{{ $record->data_nasabah['email'] }}</div>
            </div>
        @endif
    </div>

    <!-- Informasi Rekening -->
    {{-- <div class="info-box">
        <h3>💳 Informasi Rekening</h3>
        <div class="info-row">
            <div class="info-label">No. Tabungan</div>
            <div class="info-value"><strong>{{ $record->no_tabungan }}</strong></div>
        </div>
        <div class="info-row">
            <div class="info-label">Produk</div>
            <div class="info-value">{{ $record->data_produk['nama'] ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Status</div>
            <div class="info-value">
                <span class="status-badge status-aktif">
                    {{ strtoupper($record->data_rekening['status'] ?? 'AKTIF') }}
                </span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal Buka</div>
            <div class="info-value">{{ $record->data_rekening['tanggal_buka'] ?? '-' }}</div>
        </div>
    </div> --}}

    {{-- <!-- Saldo -->
    <div class="highlight">
        <div style="font-size: 10pt; color: #4a5568; margin-bottom: 5px;">Saldo Rekening</div>
        <div class="amount">{{ $record->data_rekening['saldo_formatted'] ?? 'Rp 0' }}</div>
    </div> --}}

    <div class="divider"></div>

    {{-- <!-- Transaksi Terakhir -->
    @if (!empty($record->data_transaksi_terakhir))
        <div class="info-box">
            <h3>📋 Transaksi Terakhir</h3>
            <div class="info-row">
                <div class="info-label">Kode Transaksi</div>
                <div class="info-value">{{ $record->data_transaksi_terakhir['kode_transaksi'] ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Jenis</div>
                <div class="info-value">
                    <span class="status-badge status-setoran">
                        {{ strtoupper($record->data_transaksi_terakhir['jenis_transaksi_label'] ?? '-') }}
                    </span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Jumlah</div>
                <div class="info-value">
                    <strong>{{ $record->data_transaksi_terakhir['jumlah_formatted'] ?? 'Rp 0' }}</strong></div>
            </div>
            <div class="info-row">
                <div class="info-label">Tanggal</div>
                <div class="info-value">{{ $record->data_transaksi_terakhir['tanggal_transaksi'] ?? '-' }}</div>
            </div>
            @if (!empty($record->data_transaksi_terakhir['teller']))
                <div class="info-row">
                    <div class="info-label">Teller</div>
                    <div class="info-value">{{ $record->data_transaksi_terakhir['teller'] }}</div>
                </div>
            @endif
        </div>
    @endif --}}

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
        <p style="margin-top: 5px;">Struk ini adalah bukti sah checkout program MBG</p>
        <p style="margin-top: 5px;">Untuk informasi lebih lanjut, hubungi customer service kami</p>
    </div>

    <div class="timestamp">
        Dicetak pada: {{ $tanggal_cetak }}
    </div>
</body>

</html>
