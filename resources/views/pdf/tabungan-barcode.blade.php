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
           background: #ffffff url('{{ public_path('images/bg_kartu_simpanan.jpg') }}') no-repeat center center;
            background-size: cover;
            
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
            color: #ffffff;

            white-space: normal;
            overflow: hidden;
        }

        .product-name {
            position: absolute;
            left: 6mm;
            bottom: 15mm;
            width: 48mm;

            font-family: Arial, Helvetica, sans-serif;
            font-size: 2.2mm;
            line-height: 3mm;
            font-weight: bold;
            color: #ffffff;
            text-transform: uppercase;
        }

        .qr-wrapper {
            position: absolute;
            right: 2mm;
            bottom: 3mm;
            width: 12mm;
            height: 12mm;
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
        <!-- <img src="{{ public_path('images/logo_sinarartha_light.png') }}" alt="Logo" class="card-logo"> -->
        <div class="member-name">
            {{ $tabungan->profile->first_name }} {{ $tabungan->profile->last_name }}
        </div>

        @if(isset($namaProduk) && $namaProduk)
        <div class="product-name">
            {{ $namaProduk }}
        </div>
        @endif

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