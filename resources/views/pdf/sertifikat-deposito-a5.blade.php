<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Sertifikat Deposito</title>
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
            padding: 40px 50px;
        }
        .header {
            text-align: center;
            margin-bottom: 3px;
        }
        .header img {
            height: 70px;
            margin-bottom: 2px;
        }
        .header .company-name {
            font-size: 14px;
            font-weight: bold;
            color: #2B3990;
            margin: 2px 0;
        }
        .header .company-address {
            font-size: 8px;
            margin: 1px 0;
            line-height: 1.1;
        }
        .certificate-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #FF4500;
            margin: 5px 0;
        }
        .nominal {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #2B3990;
            margin: 5px 0;
        }
        .terbilang {
            text-align: center;
            font-style: italic;
            margin: 2px 0 5px 0;
            font-size: 9px;
        }
        .info-table {
            width: 100%;
            margin: 5px 0;
            border-collapse: collapse;
        }
        .info-table td, .info-table th {
            padding: 3px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 8px;
        }
        .info-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .customer-info {
            margin-top: 8px;
            margin-left: 20px;
            font-size: 8px;
            line-height: 1.2;
        }
        .customer-info p {
            margin: 1px 0;
        }
        .certificate-number {
            top: 20px;
            right: 30px;
            font-size: 8px;
        }
        .signature {
            position: absolute;
            bottom: 50px;
            right: 180px;
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
        .bank-details {
            position: absolute;
            top: 220px;
            left: 420px;
            font-size: 8px;
            width: 200px;
            background-color: rgba(255, 255, 255, 0.85);
            padding: 3px 5px;
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
            line-height: 1.3;
        }
        .bank-details table td:first-child {
            width: 100px;
            font-weight: 500;
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

            <div class="certificate-title">SERTIFIKAT DEPOSITO</div>

            <div class="nominal">Rp. {{ number_format($deposito->nominal_penempatan, 0, ',', '.') }}</div>
            <div class="terbilang">Terbilang: {{ ucwords(terbilang($deposito->nominal_penempatan)) }} Rupiah</div>

            <table class="info-table">
                <tr>
                    <th>Jangka Waktu</th>
                    <th>Tanggal Transaksi</th>
                    <th>Tanggal Jatuh Tempo</th>
                    <th>Bunga</th>
                    <th>Kondisi</th>
                </tr>
                <tr>
                    <td>{{ $deposito->jangka_waktu }} Bulan</td>
                    <td>{{ \Carbon\Carbon::parse($deposito->tanggal_pembukaan)->format('d F Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($deposito->tanggal_jatuh_tempo)->format('d F Y') }}</td>
                    <td>{{ $deposito->rate_bunga }}% p.a</td>
                    <td>{{ $deposito->perpanjangan_otomatis ? 'ARO' : '-' }}</td>
                </tr>
            </table>

            <div class="customer-info">
                <table style="border-collapse: collapse; width: 100%;">
                    <tr>
                        <td style="width: 120px; border: none;">No. Sertifikat</td>
                        <td style="border: none;">: {{ $deposito->nomor_rekening }}</td>
                    </tr>
                    <tr>
                        <td style="border: none;">No Anggota</td>
                        <td style="border: none;">: {{ $deposito->profile->no_anggota ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="border: none;">Atas Nama</td>
                        <td style="border: none;">: {{ $deposito->profile->first_name }} {{ $deposito->profile->last_name }}</td>
                    </tr>
                    @php
                        $getRegionName = function($code) {
                            return DB::table('regions')->where('code', $code)->value('name') ?? '-';
                        };
                    @endphp
                    <tr>
                        <td style="border: none;">Alamat</td>
                        <td style="border: none;">: {{ $deposito->profile->address }}</td>
                    </tr>
                    <tr>
                        <td style="border: none;">Provinsi</td>
                        <td style="border: none;">: {{ $getRegionName($deposito->profile->province_id) }}</td>
                    </tr>
                    <tr>
                        <td style="border: none;">Kabupaten/Kota</td>
                        <td style="border: none;">: {{ $getRegionName($deposito->profile->district_id) }}</td>
                    </tr>
                    <tr>
                        <td style="border: none;">Kecamatan</td>
                        <td style="border: none;">: {{ $getRegionName($deposito->profile->city_id) }}</td>
                    </tr>
                    <tr>
                        <td style="border: none;">Desa/Kelurahan</td>
                        <td style="border: none;">: {{ $getRegionName($deposito->profile->village_id) }}</td>
                    </tr>
                </table>
            </div>

            <div class="bank-details">
                <h4>Informasi Rekening Bank</h4>
                <table>
                    <tr>
                        <td>Nama Bank</td>
                        <td>: {{ $deposito->nama_bank }}</td>
                    </tr>
                    <tr>
                        <td>Nomor Rekening</td>
                        <td>: {{ $deposito->nomor_rekening_bank }}</td>
                    </tr>
                    <tr>
                        <td>Nama Pemilik Rekening</td>
                        <td>: {{ $deposito->nama_pemilik_rekening_bank }}</td>
                    </tr>
                </table>
            </div>

            <div class="signature">
                <p>Surabaya, {{ \Carbon\Carbon::parse($deposito->tanggal_pembukaan)->format('d F Y') }}</p>
                <p>Ketua / Pengurus</p>
                <p>KOPERASI SINARA ARTHA</p>
                <img src="{{ public_path('images/ttd_andesta_nobg.png') }}" alt="Tanda Tangan">
                <p class="name">ANDESTA RULLI</p>
            </div>
        </div>
    </div>
</body>
</html>