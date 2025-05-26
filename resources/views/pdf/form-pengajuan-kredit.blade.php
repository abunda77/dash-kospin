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
            font-size: 9px;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .logo {
            width: 300px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 14px;
            font-weight: bold;
            margin: 5px 0;
        }
        .subtitle {
            font-size: 10px;
            color: #666;
            margin-bottom: 10px;
        }
        .form-number {
            font-size: 10px;
            text-align: right;
            font-weight: bold;
            position: absolute;
            top: 3px;
            right: 3px;
        }
        .form-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .form-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            width: 40%;
            font-size: 11px;
        }
        .input-field {
            border-bottom: 1px solid #999;
            min-height: 20px;
            position: relative;
            padding-left: 6px;
            font-size: 10px;
        }
        .input-field::before {
            content: ':';
            position: absolute;
            left: 0;
            top: 0;
        }
        .signature-section {
            margin-top: 10px;
            text-align: right;
        }
        .signature-box {
            border: 1px solid #000;
            width: 120px;
            height: 50px;
            margin-top: 5px;
            display: inline-block;
        }
        .info-text {
            font-size: 8px;
            color: #666;
            font-style: italic;
            margin-top: 1px;
        }
        .checkbox-option {
            font-size: 10px;
            display: flex;
            gap: 5px;
            margin: 2px 0;
        }
        .checkbox-option input[type="checkbox"] {
            margin-right: 3px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="form-number">
        No: FORM-KR-{{ str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT) }}
    </div>

    <div class="header">
        <img src="{{ public_path('images/logo_koperasi.jpg') }}" class="logo">
        <div class="title">FORM PENGAJUAN KREDIT</div>
        <div class="subtitle">Mohon isi form berikut dengan lengkap dan jelas</div>
    </div>

    <table class="form-table">
        <tr>
            <td class="label">Nama Lengkap</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">Nomor KTP</td>
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
            <td class="label">Alamat Rumah</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">Media Sosial</td>
            <td>
                <div class="input-field"></div>
                <div class="info-text">
                    Wajib follow: Instagram & Facebook @koperasisinaraartha
                </div>
            </td>
        </tr>
        <tr>
            <td class="label">Saudara Serumah + HP</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">Saudara Tidak Serumah + HP</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">Kesediaan Disurvei</td>
            <td>
                <div class="checkbox-option">
                    <label><input type="checkbox" name="survey" value="yes"> Ya</label>
                    <label><input type="checkbox" name="survey" value="no"> Tidak</label>
                </div>
            </td>
        </tr>
        <tr>
            <td class="label">Domisili Tempat Tinggal</td>
            <td>
                <div class="checkbox-option">
                    <label><input type="checkbox" name="domisili_tinggal" value="ya"> Ya</label>
                    <label><input type="checkbox" name="domisili_tinggal" value="tidak"> Tidak</label>
                </div>
            </td>
        </tr>
        <tr>
            <td class="label">Domisili Usaha</td>
            <td>
                <div class="checkbox-option">
                    <label><input type="checkbox" name="domisili_usaha" value="ya"> Ya</label>
                    <label><input type="checkbox" name="domisili_usaha" value="tidak"> Tidak</label>
                </div>
            </td>
        </tr>
        <tr>
            <td class="label">Brand Usaha</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">Omset Penjualan/Bulan</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">Limit Pengajuan</td>
            <td><div class="input-field">Rp. __________ Tenor: __________ Cicilan: __________</div></td>
        </tr>
        <tr>
            <td class="label">Jenis Pengajuan</td>
            <td>
                <div class="checkbox-option">
                    <label><input type="checkbox" name="loan_type" value="jaminan"> Dengan Jaminan</label>
                    <label><input type="checkbox" name="loan_type" value="tanpa_jaminan"> Tanpa Jaminan</label>
                </div>
            </td>
        </tr>
        <tr>
            <td class="label">Pekerjaan</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">Alamat Kantor/Usaha</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">No. Telp Kantor/Usaha</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">Penghasilan/Bulan</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">Hutang/Cicilan Aktif</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">Jumlah Keluarga</td>
            <td><div class="input-field"></div></td>
        </tr>
        <tr>
            <td class="label">Tujuan Pinjaman</td>
            <td><div class="input-field"></div></td>
        </tr>
    </table>

    <div style="text-align: right; margin: 10px 0; font-size: 10px;">
        <p>........................., ...............................</p>
    </div>

    <table style="width: 100%; margin-top: 5px;">
        <tr>
            <td style="width: 50%; text-align: center;">
                <p style="font-size: 10px; margin-bottom: 3px;">Marketing/Referral</p>
                <div class="signature-box"></div>
                <p style="font-size: 10px; margin: 3px 0;">(............................................)</p>
                <p class="info-text">Tanda tangan dan nama lengkap</p>
            </td>
            <td style="width: 50%; text-align: center;">
                <p style="font-size: 10px; margin-bottom: 3px;">Yang mengajukan,</p>
                <div class="signature-box"></div>
                <p style="font-size: 10px; margin: 3px 0;">(............................................)</p>
                <p class="info-text">Tanda tangan dan nama lengkap</p>
            </td>
        </tr>
    </table>

    <table style="position: absolute; bottom: 80px; left: 10px; font-size: 10px; border-collapse: collapse; width: 100%;">
        <tr>
            <!-- Kolom 1 -->
            <td style="border: none; padding: 0; vertical-align: top; width: 15%;">Kode Reff</td>
            <td style="border: none; padding: 5px; vertical-align: top; width: 5%;">:</td>
            <td style="border: none; padding: 5px; vertical-align: top; width: 25%;">.....................</td>

            <!-- Jarak antar kolom -->
            <td style="width: 5%;"></td>

            <!-- Kolom 2 -->
            <td style="border: none; padding: 0; vertical-align: top; width: 50%;" colspan="3">Pencairan pinjaman: CASH / TRANSFER (pilih salah satu)</td>
        </tr>
        <tr>
            <!-- Kolom 1 -->
            <td style="border: none; padding: 0; vertical-align: top;">Nama Reff</td>
            <td style="border: none; padding: 5px; vertical-align: top;">:</td>
            <td style="border: none; padding: 5px; vertical-align: top;">.....................</td>

            <!-- Jarak antar kolom -->
            <td></td>

            <!-- Kolom 2 -->
            <td style="border: none; padding: 0; vertical-align: top; width: 15%;">Bank</td>
            <td style="border: none; padding: 5px; vertical-align: top; width: 5%;">:</td>
            <td style="border: none; padding: 5px; vertical-align: top; width: 30%;">.....................</td>
        </tr>
        <tr>
            <!-- Kolom 1 -->
            <td style="border: none; padding: 0; vertical-align: top;">No.Hp</td>
            <td style="border: none; padding: 5px; vertical-align: top;">:</td>
            <td style="border: none; padding: 5px; vertical-align: top;">.....................</td>

            <!-- Jarak antar kolom -->
            <td></td>

             <!-- Kolom 2 -->
            <td style="border: none; padding: 0; vertical-align: top;">No rekening</td>
            <td style="border: none; padding: 5px; vertical-align: top;">:</td>
            <td style="border: none; padding: 5px; vertical-align: top;">.....................</td>
        </tr>
        <tr>
            <!-- Kolom 1 (kosong) -->
            <td></td>
            <td></td>
            <td></td>

            <!-- Jarak antar kolom -->
            <td></td>

             <!-- Kolom 2 -->
            <td style="border: none; padding: 0; vertical-align: top;">Nama di rekening</td>
            <td style="border: none; padding: 5px; vertical-align: top;">:</td>
            <td style="border: none; padding: 5px; vertical-align: top;">.....................</td>
        </tr>
    </table>
</body>
</html>
