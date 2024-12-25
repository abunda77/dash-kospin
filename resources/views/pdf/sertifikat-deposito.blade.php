<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Sertifikat Deposito</title>
    <style>
        @page {
            size: landscape;
            margin: 0;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .border-container {
            position: relative;
            margin: 30px;
            height: calc(100vh - 60px);
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
            padding: 100px 120px;
        }
        .header {
            text-align: center;
            margin-bottom: 5px;
        }
        .header img {
            height: 100px;
            margin-bottom: 3px;
        }
        .header .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #2B3990;
            margin: 3px 0;
        }
        .header .company-address {
            font-size: 9px;
            margin: 1px 0;
            line-height: 1.2;
        }
        .certificate-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            color: #FF4500;
            margin: 8px 0;
        }
        .nominal {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #2B3990;
            margin: 8px 0;
        }
        .terbilang {
            text-align: center;
            font-style: italic;
            margin: 3px 0 10px 0;
            font-size: 11px;
        }
        .info-table {
            width: 100%;
            margin: 8px 0;
            border-collapse: collapse;
        }
        .info-table td, .info-table th {
            padding: 4px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 10px;
        }
        .info-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .customer-info {
            margin-top: 12px;
            margin-left: 50px;
            font-size: 10px;
            line-height: 1.3;
        }
        .customer-info p {
            margin: 2px 0;
        }
        .certificate-number {
            /* position: absolute; */
            top: 25px;
            right: 50px;
            font-size: 9px;
        }
        .signature {
            position: absolute;
            bottom: 100px;
            right: 200px;
            text-align: center;
            font-size: 10px;
        }
        .signature p {
            margin: 1px 0;
        }
        .signature .name {
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 3px;
            margin-top: 50px;
            width: 140px;
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
                <div class="certificate-number">No. Sertifikat : {{ $deposito->nomor_rekening }}</div>
                <p>No Anggota : {{ $deposito->profile->no_anggota ?? '-' }}</p>
                <p>Atas Nama : {{ $deposito->profile->first_name }} {{ $deposito->profile->last_name }}</p>
                <p>Alamat : {{ $deposito->profile->address }}</p>
            </div>

            <div class="signature">
                <p>Surabaya, {{ \Carbon\Carbon::parse($deposito->tanggal_pembukaan)->format('d F Y') }}</p>
                <p>Ketua / Pengurus</p>
                <p>KOPERASI SINARA ARTHA</p>
                <br><br>
                <p class="name">Jimmy Tandiono</p>
            </div>
        </div>
    </div>
</body>
</html>