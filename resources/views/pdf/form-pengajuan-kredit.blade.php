<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Form Pengajuan Kredit</title>
    <style>
        @page {
            margin: 1cm;
        }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.8;
            color: #333;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            margin-top: 45px;
        }
        .logo {
            width: 250px;
            margin-bottom: 8px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .subtitle {
            font-size: 11px;
            color: #666;
            margin-bottom: 12px;
        }
        .form-number {
            font-size: 11px;
            text-align: right;
            font-weight: bold;
            position: absolute;
            top: 2px;
            right: 2px;
        }
        .form-table {
            width: 100%;
            border-collapse: collapse;
        }
        .form-table td {
            padding: 3px 8px;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            width: 45%;
        }
        .input-field {
            border-bottom: 1px solid #ccc;
            min-height: 16px;
            position: relative;
            padding-left: 10px;
        }
        .input-field::before {
            content: ':';
            position: absolute;
            left: 0;
            top: 0;
        }
        .signature-section {
            margin-top: 20px;
            text-align: right;
        }
        .signature-box {
            border: 1px solid #000;
            width: 150px;
            height: 70px;
            margin-top: 8px;
            display: inline-block;
        }
        .info-text {
            font-size: 9px;
            color: #666;
            font-style: italic;
        }
        .checkbox-option {
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="form-number">
        No: FORM-KR-{{ str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT) }}
    </div>

    <div class="header">
        <img src="{{ public_path('images/logo_koperasi.jpg') }}" class="logo" style="width: 400px; height: auto;">
        <div class="title">FORM PENGAJUAN KREDIT</div>
        <div class="subtitle">Mohon isi form berikut dengan lengkap dan jelas</div>
    </div>

    <table class="form-table">
        <tr>
            <td class="label">Nama Lengkap</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">Nomor KTP/ID Card</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">Nomor HP/Whatsapp</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">Email</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">Nama Ibu Kandung</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">Alamat Rumah (jika tidak sesuai KTP)</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">Nama Akun Media Sosial</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">Nama Saudara Serumah + Nomor HP/Whatsapp</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">Nama Saudara Tidak Serumah + Nomor HP/Whatsapp</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">Kesediaan untuk Disurvei</td>
            <td><div class="input-field checkbox-option"> Ya / Tidak</div></td>
        </tr>
        <tr>
            <td class="label">Limit Pengajuan Kredit</td>
            <td><div class="input-field">Rp. {{ $nominalPinjaman ? number_format((float)$nominalPinjaman, 0, ',', '.') : '' }}</div></td>
        </tr>
        <tr>
            <td class="label">Jenis Pengajuan</td>
            <td><div class="input-field checkbox-option">Dengan Jaminan / Tanpa Jaminan</div></td>
        </tr>
        <tr>
            <td class="label">Pekerjaan</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">Alamat Kantor/Tempat Usaha</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">No. Telp Kantor/Tempat Usaha</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">Penghasilan per Bulan</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">Daftar Hutang/Cicilan Aktif</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">Jumlah Anggota Keluarga</td>
            <td><div class="input-field"></div></td>
        </tr>
    </table>

    <div style="text-align: right; margin-top: 30px; margin-bottom: 20px;">
        <p>........................., ...............................</p>
    </div>

    <table style="width: 100%; margin-top: 20px;">
        <tr>
            <td style="width: 50%; text-align: center;">
                <p>Marketing/Referral</p>
                <div class="signature-box" style="margin: 0 auto;"></div>
                <p>(............................................)</p>
                <p class="info-text">Tanda tangan dan nama lengkap</p>
            </td>
            <td style="width: 50%; text-align: center;">
                <p>Yang mengajukan,</p>
                <div class="signature-box" style="margin: 0 auto;"></div>
                <p>(............................................)</p>
                <p class="info-text">Tanda tangan dan nama lengkap</p>
            </td>
        </tr>
    </table>
</body>
</html>
