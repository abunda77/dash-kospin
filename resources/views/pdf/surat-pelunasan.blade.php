<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Pelunasan Kredit</title>
    <style>
        @page {
            margin: 1.5cm 2cm;
        }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            font-size: 13px;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .logo {
            height: 100px;
            width: auto;
            margin-bottom: 5px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin: 5px 0;
        }
        .subtitle {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        .date-location {
            margin-top: 25px;
            margin-bottom: 25px;
            display: block;
            text-align: left;
            font-weight: bold;
        }
        .info-header {
            margin-bottom: 15px;
            width: 100%;
        }
        .info-header td {
            padding: 0 4px 4px 0;
            vertical-align: top;
        }
        .info-header .right {
            text-align: right;
        }
        .content {
            margin-bottom: 30px;
        }
        .content p {
            margin: 8px 0;
            text-align: justify;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .data-table td {
            padding: 5px 10px;
            vertical-align: top;
        }
        .signature-section {
            margin-top: 30px;
            text-align: right;
            padding-right: 60px;
        }
        .signature-box {
            display: inline-block;
            margin-top: 30px;
            text-align: center;
            width: 200px;
        }
        .footer {
            margin-top: 50px;
            font-size: 10px;
            text-align: center;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        .perihal {
            font-weight: normal;
            margin-top: 5px;
        }
        .signer-name {
            margin-top: 70px;
            font-weight: bold;
            text-decoration: underline;
        }
        .signer-title {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo_koperasi.jpg') }}" class="logo">


    </div>

    <div class="date-location">Surabaya, {{ \Carbon\Carbon::parse($pelunasan->tanggal_pelunasan)->translatedFormat('d F Y') }}</div>

    @php
        $bulanRomawi = [
            '01' => 'I', '02' => 'II', '03' => 'III', '04' => 'IV',
            '05' => 'V', '06' => 'VI', '07' => 'VII', '08' => 'VIII',
            '09' => 'IX', '10' => 'X', '11' => 'XI', '12' => 'XII'
        ];
        $bulan = \Carbon\Carbon::now()->format('m');
        $uniqueCode = strtoupper(substr(md5($pelunasan->id_pelunasan . $pelunasan->no_pinjaman), 0, 4));
        $pinjNumber = preg_replace('/[^0-9]/', '', $pelunasan->no_pinjaman);
    @endphp

    <table class="info-header">
        <tr>
            <td style="width: 70px;">Nomor</td>
            <td style="width: 15px;">:</td>
            <td><strong>KOP-PLN/{{ \Carbon\Carbon::now()->format('d') }}/{{ $bulanRomawi[$bulan] }}/{{ \Carbon\Carbon::now()->format('Y') }}-{{ $pinjNumber }}/{{ $uniqueCode }}</strong></td>
        </tr>
        <tr>
            <td>Perihal</td>
            <td>:</td>
            <td><strong>Surat Pelunasan Kredit</strong></td>
        </tr>
    </table>

    <div class="content">
        <p>Kepada Yth.:<br><strong>{{ $pelunasan->profile->first_name }} {{ $pelunasan->profile->last_name }}</strong><br>Di Tempat</p>
<br>
        <p>Dengan hormat,</p>
        <p>Bersama surat ini kami sampaikan bahwa Saudara/i dengan data kredit sebagai berikut:</p>

        <table class="data-table">
            <tr>
                <td style="width: 30%;">Nama Nasabah</td>
                <td style="width: 5%;">:</td>
                <td><strong>{{ $pelunasan->profile->first_name }} {{ $pelunasan->profile->last_name }}</strong></td>
            </tr>
            <tr>
                <td>No. Kredit</td>
                <td>:</td>
                <td><strong>{{ $pelunasan->no_pinjaman }}</strong></td>
            </tr>
            <tr>
                <td>Jumlah Kredit</td>
                <td>:</td>
                <td><strong>Rp {{ number_format($pelunasan->pinjaman->jumlah_pinjaman, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td>Tanggal Pelunasan</td>
                <td>:</td>
                <td><strong>{{ \Carbon\Carbon::parse($pelunasan->tanggal_pelunasan)->translatedFormat('d F Y') }}</strong></td>
            </tr>
        </table>

        <p>Dengan demikian, kami menyatakan bahwa Saudara/i telah melunasi seluruh kewajiban kredit kepada <strong>Koperasi Sinara Artha</strong> sampai dengan tanggal <strong>{{ \Carbon\Carbon::parse($pelunasan->tanggal_pelunasan)->translatedFormat('d F Y') }}</strong>, dengan jumlah pelunasan sebesar <strong>Rp {{ number_format($pelunasan->jumlah_pelunasan, 0, ',', '.') }}</strong>.</p>
        <br>
        <p>Surat pelunasan ini harap disimpan sebagai <strong>bukti yang sah</strong> serta dapat digunakan dengan semestinya.</p>

    </div>

    <div class="signature-section">

        <div class="signature-box">
          <p>Hormat kami,</p>
            <div class="signer-name">Andesta Rully</div>
            <div class="signer-title">(Ketua Koperasi Sinara Artha)</div>
        </div>
    </div>

    <div class="footer">
        Dokumen ini dicetak secara otomatis pada {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i:s') }}
    </div>
</body>
</html>
