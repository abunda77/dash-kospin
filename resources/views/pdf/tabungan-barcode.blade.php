<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kartu Member - {{ $tabungan->no_tabungan }}</title>

    <style>
        @page {
            size: 85.6mm 53.98mm;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            width: 85.6mm;
            height: 53.98mm;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
        }

        .card {
            position: relative;
            width: 85.6mm;
            height: 53.98mm;
            overflow: hidden;
            background: #ffffff;
            
        }
        .card-logo {
    position: absolute;
    top: 4.5mm;
    left: 50%;
    transform: translateX(-50%);

    width: 55mm;
    height: auto;
    max-height: 12mm;

    object-fit: contain;
    display: block;
}

        .member-name {
            position: absolute;
            left: 6mm;
            bottom: 20mm;
            width: 48mm;

            font-family: "Times New Roman", serif;
            font-size: 3mm;
            line-height: 3.6mm;
            font-weight: normal;
            letter-spacing: 0.25mm;
            text-transform: uppercase;
            color: #111;

            white-space: normal;
            overflow: hidden;
        }

        .qr-wrapper {
            position: absolute;
            right: 5mm;
            bottom: 4mm;
            width: 21mm;
            height: 21mm;
            background: #ffffff;
            padding: 1.2mm;
        }

        .qr-wrapper img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: contain;
        }

        .qr-placeholder {
            width: 100%;
            height: 100%;
            border: 0.3mm solid #333;
            font-size: 2.2mm;
            text-align: center;
            padding-top: 6mm;
            color: #333;
        }

        @media print {
            html,
            body {
                width: 85.6mm;
                height: 53.98mm;
            }

            .card {
                page-break-after: always;
            }
        }
    </style>
</head>

<body>
    <div class="card">
        <img src="{{ public_path('images/logo_sinarartha_light.png') }}" alt="Logo" class="card-logo">
        <div class="member-name">
            {{ $tabungan->profile->first_name }} {{ $tabungan->profile->last_name }}
        </div>

        <div class="qr-wrapper">
            @if(isset($hasQrCode) && $hasQrCode && isset($qrCodePath))
                <img src="{{ $qrCodePath }}" alt="QR Code">
            @else
                <div class="qr-placeholder">
                    QR<br>CODE
                </div>
            @endif
        </div>

    </div>
</body>
</html>