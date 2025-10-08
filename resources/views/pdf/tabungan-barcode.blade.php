<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Barcode Tabungan - {{ $tabungan->no_tabungan }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            text-align: center;
        }
        .container {
            max-width: 400px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 20px;
            border-radius: 10px;
        }
        .header {
            margin-bottom: 20px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .account-info {
            margin-bottom: 20px;
            text-align: left;
        }
        .account-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .account-info td {
            padding: 5px;
            border-bottom: 1px solid #eee;
        }
        .account-info td:first-child {
            font-weight: bold;
            width: 40%;
        }
        .qr-code {
            margin: 20px 0;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
        .scan-url {
            font-size: 10px;
            word-break: break-all;
            margin-top: 10px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="title">BARCODE TABUNGAN</div>
            <div>KoSPIN Dash</div>
        </div>
        
        <div class="account-info">
            <table>
                <tr>
                    <td>No. Rekening</td>
                    <td>: {{ $tabungan->no_tabungan }}</td>
                </tr>
                <tr>
                    <td>Nama Nasabah</td>
                    <td>: {{ $tabungan->profile->first_name }} {{ $tabungan->profile->last_name }}</td>
                </tr>
                <tr>
                    <td>Produk</td>
                    <td>: {{ $tabungan->produkTabungan->nama_produk }}</td>
                </tr>
                <tr>
                    <td>Saldo</td>
                    <td>: {{ format_rupiah($tabungan->saldo) }}</td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>: {{ ucfirst($tabungan->status_rekening) }}</td>
                </tr>
            </table>
        </div>
        
        <div class="qr-code">
            @if(isset($hasQrCode) && $hasQrCode && isset($qrCodeBase64))
                <img src="{{ $qrCodeBase64 }}" alt="QR Code" style="width: 200px; height: 200px;">
            @else
                <div style="width: 200px; height: 200px; border: 2px solid #333; display: table-cell; vertical-align: middle; text-align: center; font-size: 14px; color: #333;">
                    <div>
                        <strong>QR Code</strong><br><br>
                        Scan untuk akses<br>
                        detail rekening
                    </div>
                </div>
                @if(isset($error))
                    <div style="color: #888; font-size: 10px; margin-top: 10px;">{{ $error }}</div>
                @endif
            @endif
        </div>
        
        <div class="footer">
            <div>Scan barcode untuk melihat detail rekening</div>
            <div class="scan-url">{{ $scanUrl }}</div>
            <div style="margin-top: 10px;">
                Dicetak pada: {{ date('d/m/Y H:i:s') }}
            </div>
        </div>
    </div>
</body>
</html>