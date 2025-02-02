<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Form Pengajuan Kredit</title>
    <style>
        @page {
            margin: 0.5cm;
        }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.5;
            color: #333;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            margin-top: 10px;
        }
        .logo {
            width: 200px;
            margin-bottom: 10px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 12px;
            margin-bottom: 8px;
        }
        .subtitle {
            font-size: 11px;
            color: #666;
            margin-bottom: 15px;
        }
        .form-number {
            font-size: 11px;
            text-align: right;
            font-weight: bold;
            position: absolute;
            top: 5px;
            right: 5px;
        }
        .form-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .form-table td {
            padding: 4px 8px;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            width: 45%;
            font-size: 12px;
        }
        .input-field {
            border-bottom: 1px solid #999;
            min-height: 25px;
            position: relative;
            padding-left: 8px;
            font-size: 11px;
        }
        .input-field::before {
            content: ':';
            position: absolute;
            left: 0;
            top: 0;
        }
        .signature-section {
            margin-top: 15px;
            text-align: right;
        }
        .signature-box {
            border: 1px solid #000;
            width: 150px;
            height: 60px;
            margin-top: 8px;
            display: inline-block;
        }
        .info-text {
            font-size: 9px;
            color: #666;
            font-style: italic;
            margin-top: 2px;
        }
        .checkbox-option {
            font-size: 11px;
            display: flex;
            gap: 8px;
            margin: 4px 0;
        }
        .checkbox-option input[type="checkbox"] {
            margin-right: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="form-number">
        No: FORM-KR-{{ str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT) }}
    </div>

    <div class="header">
        <img src="{{ public_path('images/logo_koperasi.jpg') }}" class="logo" style="width: 250px; height: auto;">
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
            <td>
                <div class="input-field"></div>
                <div class="info-text">
                    Wajib follow akun media sosial Koperasi:
                    Instagram & Facebook @koperasisinaraartha
                </div>
            </td>
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
            <td>
                <div class="checkbox-option">
                    <label>
                        <input type="checkbox" name="survey" value="yes">
                        Ya
                    </label>
                    <label>
                        <input type="checkbox" name="survey" value="no">
                        Tidak
                    </label>
                </div>
            </td>
        </tr>
        <tr>
            <td class="label">Limit Pengajuan Kredit</td>
            <td><div class="input-field">Rp.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Tenor: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Cicilan:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></td>
        </tr>
        <tr>
            <td class="label">Jenis Pengajuan</td>
            <td>
                <div class="checkbox-option">
                    <label>
                        <input type="checkbox" name="loan_type" value="jaminan">
                        Dengan Jaminan
                    </label>
                    <label>
                        <input type="checkbox" name="loan_type" value="tanpa_jaminan">
                        Tanpa Jaminan
                    </label>
                </div>
            </td>
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
        <tr>
            <td class="label">Tujuan Pinjaman</td>
            <td><div class="input-field"></div></td>
        </tr>
    </table>

    <div style="text-align: right; margin-top: 15px; margin-bottom: 10px; font-size: 11px;">
        <p>........................., ...............................</p>
    </div>

    <table style="width: 100%; margin-top: 10px;">
        <tr>
            <td style="width: 50%; text-align: center;">
                <p style="font-size: 11px; margin-bottom: 5px;">Marketing/Referral</p>
                <div class="signature-box" style="margin: 0 auto;"></div>
                <p style="font-size: 11px; margin: 5px 0;">(............................................)</p>
                <p class="info-text">Tanda tangan dan nama lengkap</p>
            </td>
            <td style="width: 50%; text-align: center;">
                <p style="font-size: 11px; margin-bottom: 5px;">Yang mengajukan,</p>
                <div class="signature-box" style="margin: 0 auto;"></div>
                <p style="font-size: 11px; margin: 5px 0;">(............................................)</p>
                <p class="info-text">Tanda tangan dan nama lengkap</p>
            </td>
        </tr>
    </table>
</body>
</html>
