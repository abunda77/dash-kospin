<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Sertifikat {{ $tabungan->produkTabungan->nama_produk }}</title>
    <style>
        @page {
            size: A5 landscape;
            margin: 0;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .border-container {
            position: relative;
            margin: 10px;
            height: calc(100vh - 20px);
            background: white;
        }

        .border-frame {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('{{ public_path('images/border-ornament.png') }}');
            background-size: 100% 100%;
            background-repeat: no-repeat;
            z-index: 1;
        }

        .content-wrapper {
            position: relative;
            z-index: 2;
            padding: 42px 55px;
        }

        .header {
            text-align: center;
            margin-bottom: 3px;
        }

        .header img {
            height: 70px;
            margin-bottom: 2px;
        }

        .certificate-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #FF4500;
            margin: 5px 0;
            text-transform: uppercase;
        }

        .nominal {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #2B3990;
            margin: 8px 0 2px;
        }

        .terbilang {
            text-align: center;
            font-style: italic;
            margin: 2px 0 10px;
            font-size: 9px;
        }

        .info-table {
            width: 100%;
            margin: 5px 0;
            border-collapse: collapse;
        }

        .info-table td,
        .info-table th {
            padding: 5px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 8px;
        }

        .info-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .customer-info {
            margin-top: 10px;
            margin-left: 20px;
            font-size: 8px;
            line-height: 1.3;
            width: 390px;
        }

        .customer-info table {
            border-collapse: collapse;
            width: 100%;
        }

        .customer-info td {
            border: none;
            padding: 2px 0;
            vertical-align: top;
        }

        .customer-info td:first-child {
            width: 125px;
        }

        .alamat-wrap {
            word-break: break-word;
            white-space: pre-line;
        }

        .bank-details {
            position: absolute;
            top: 250px;
            left: 480px;
            font-size: 8px;
            width: 210px;
            background-color: rgba(255, 255, 255, 0.88);
            padding: 4px 6px;
            border-radius: 2px;
        }

        .bank-details h4 {
            font-size: 9px;
            margin: 0 0 4px 0;
            color: #2B3990;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
            padding-bottom: 2px;
            text-transform: uppercase;
        }

        .bank-details table {
            width: 100%;
            border-collapse: collapse;
        }

        .bank-details table td {
            padding: 1px 0;
            line-height: 1.4;
        }

        .bank-details table td:first-child {
            width: 95px;
            font-weight: 500;
        }

        .signature {
            position: absolute;
            bottom: 45px;
            right: 135px;
            text-align: center;
            font-size: 8px;
        }

        .signature p {
            margin: 1px 0;
        }

        .signature img {
            width: 80px;
            margin: 3px 0;
        }

        .signature .name {
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 2px;
            margin-top: 3px;
            width: 100px;
        }
    </style>
</head>

<body>
    <div class="border-container">
        <div class="border-frame"></div>
        <div class="content-wrapper">
            <div class="header">
                <img src="{{ public_path('images/logo_koperasi.jpg') }}" alt="Logo Koperasi">
            </div>

            <div class="certificate-title">Sertifikat {{ $tabungan->produkTabungan->nama_produk }}</div>

            <div class="nominal">Rp. {{ number_format($tabungan->saldo, 0, ',', '.') }}</div>
            <div class="terbilang">Terbilang: {{ ucwords(terbilang($tabungan->saldo)) }} Rupiah</div>

            <table class="info-table">
                <tr>
                    <th>No. Sertifikat</th>
                    <th>Tanggal Pembukaan</th>
                    <th>Jangka Waktu</th>
                    <th>Akhir Kontrak</th>
                    <th>Bunga</th>
                    <th>Produk Tabungan</th>
                </tr>
                <tr>
                    <td>{{ $tabungan->no_tabungan }}</td>
                    <td>{{ $tabungan->tanggal_buka_rekening->translatedFormat('d F Y') }}</td>
                    <td>{{ $jangkaWaktu }} Bulan</td>
                    <td>{{ $akhirKontrak->translatedFormat('d F Y') }}</td>
                    <td>{{ $tabungan->produkTabungan->beayaTabungan->persentase_bunga ?? '-' }}% p.a</td>
                    <td>{{ $tabungan->produkTabungan->nama_produk }}</td>
                </tr>
            </table>

            <div class="customer-info">
                <table>
                    <tr>
                        <td>Nama</td>
                        <td>: {{ trim($tabungan->profile->first_name . ' ' . $tabungan->profile->last_name) }}</td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td class="alamat-wrap">: {{ $tabungan->profile->address }}</td>
                    </tr>
                    <tr>
                        <td>No. HP</td>
                        <td>: {{ $tabungan->profile->phone }}</td>
                    </tr>
                </table>
            </div>

            <div class="bank-details">
                <h4>Informasi Rekening Bank</h4>
                <table>
                    <tr>
                        <td>Atas Nama</td>
                        <td>: {{ $bankAtasNama }}</td>
                    </tr>
                    <tr>
                        <td>No. Rekening</td>
                        <td>: {{ $bankNoRekening }}</td>
                    </tr>
                    <tr>
                        <td>Nama Bank</td>
                        <td>: {{ $bankNamaBank }}</td>
                    </tr>
                </table>
            </div>

            <div class="signature">
                <p>Denpasar, {{ now()->translatedFormat('d F Y') }}</p>
                <p>Ketua / Pengurus</p>
                <p>KOPERASI SINARA ARTHA NAYA</p>
                <img src="{{ public_path('images/ttd_andesta_nobg.png') }}" alt="Tanda Tangan">
                <p class="name">JIMMY TANDIONO</p>
            </div>
        </div>
    </div>
</body>

</html>
