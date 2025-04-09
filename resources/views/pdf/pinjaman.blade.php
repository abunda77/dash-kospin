<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Formulir Pengajuan Pinjaman</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header img {
            height: 100px;
            width: auto;
            margin-bottom: 15px;
            object-fit: contain;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin: 5px 0;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            font-size: 14px;
            background: #f5f5f5;
            padding: 5px;
            margin-bottom: 10px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 10px;
        }
        .label {
            width: 200px;
            display: inline-block;
        }
        .value {
            border-bottom: 1px dotted #999;
            display: inline-block;
            min-width: 300px;
        }
        .footer {
            margin-top: 30px;
        }
        .signature {
            float: right;
            width: 250px;
            text-align: center;
        }
        .signature-box {
            border: 1px solid #999;
            height: 80px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo_koperasi.jpg') }}" alt="Logo Koperasi">
        <h1>FORMULIR PENGAJUAN PINJAMAN</h1>
        <h1>KOPERASI SINARA ARTHA</h1>
    </div>

    <div class="section">
        <div class="form-group">
            <span class="label">Nomor Pinjaman</span>:
            <span class="value">{{ $pinjaman->no_pinjaman }}</span>
        </div>
        <div class="form-group">
            <span class="label">Tanggal Pengajuan</span>:
            <span class="value">{{ $pinjaman->tanggal_pinjaman->format('d/m/Y') }}</span>
        </div>
        <div class="form-group">
            <span class="label">No. Kontrak</span>:
            <span class="value">{{ $pinjaman->no_pinjaman }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">DATA NASABAH</div>
        <div class="form-group">
            <span class="label">1. Nama Lengkap</span>:
            <span class="value">{{ $pinjaman->profile->first_name }} {{ $pinjaman->profile->last_name }}</span>
        </div>
        <div class="form-group">
            <span class="label">2. Nama Panggilan</span>:
            <span class="value">{{ $pinjaman->profile->first_name }}</span>
        </div>
        <div class="form-group">
            <span class="label">3. Tempat, Tanggal Lahir</span>:
            <span class="value">{{ $pinjaman->profile->birthday ? $pinjaman->profile->birthday->format('d/m/Y') : '-' }}</span>
        </div>
        <div class="form-group">
            <span class="label">4. Jenis Kelamin</span>:
            <span class="value">{{ $pinjaman->profile->gender }}</span>
        </div>
        <div class="form-group">
            <span class="label">5. Status Pernikahan</span>:
            <span class="value">{{ $pinjaman->profile->mariage }}</span>
        </div>
        <div class="form-group">
            <span class="label">6. Nama Ibu Kandung</span>:
            <span class="value">{{ $pinjaman->profile->ibu_kandung }}</span>
        </div>
        <div class="form-group">
            <span class="label">7. Alamat KTP</span>:
            <span class="value">{{ $pinjaman->profile->address }}</span>
        </div>
        @php
            $getRegionName = function($code) {
                return DB::table('regions')->where('code', $code)->value('name') ?? '-';
            };
        @endphp
        <div class="form-group">
            <span class="label">Provinsi</span>:
            <span class="value">{{ $getRegionName($pinjaman->profile->province_id) }}</span>
        </div>
        <div class="form-group">
            <span class="label">Kabupaten/Kota</span>:
            <span class="value">{{ $getRegionName($pinjaman->profile->district_id) }}</span>
        </div>
        <div class="form-group">
            <span class="label">Kecamatan</span>:
            <span class="value">{{ $getRegionName($pinjaman->profile->city_id) }}</span>
        </div>
        <div class="form-group">
            <span class="label">Desa/Kelurahan</span>:
            <span class="value">{{ $getRegionName($pinjaman->profile->village_id) }}</span>
        </div>
        <div class="form-group">
            <span class="label">8. Nomor Telepon/HP</span>:
            <span class="value">{{ $pinjaman->profile->phone }}</span>
        </div>
        <div class="form-group">
            <span class="label">9. Email</span>:
            <span class="value">{{ $pinjaman->profile->email }}</span>
        </div>
        <div class="form-group">
            <span class="label">10. Pekerjaan</span>:
            <span class="value">{{ $pinjaman->profile->job }}</span>
        </div>
        <div class="form-group">
            <span class="label">11. Penghasilan Per Bulan</span>:
            <span class="value">Rp {{ number_format($pinjaman->profile->monthly_income, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">DATA IDENTITAS</div>
        <div class="form-group">
            <span class="label">1. Nomor KTP</span>:
            <span class="value">{{ $pinjaman->profile->no_identity }}</span>
        </div>
        <div class="form-group">
            <span class="label">2. Nomor NPWP (jika ada)</span>:
            <span class="value">-</span>
        </div>
        <div class="form-group">
            <span class="label">3. Jenis Identitas Lain</span>:
            <span class="value">{{ $pinjaman->profile->sign_identity }}</span>
        </div>
        <div class="form-group">
            <span class="label">4. Nomor Identitas Lain</span>:
            <span class="value">-</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">INFORMASI PINJAMAN</div>
        <div class="form-group">
            <span class="label">1. Jenis Pinjaman</span>:
            <span class="value">{{ $pinjaman->jenis_pinjaman }}</span>
        </div>
        <div class="form-group">
            <span class="label">2. Jumlah Pinjaman</span>:
            <span class="value">Rp {{ number_format($pinjaman->jumlah_pinjaman, 0, ',', '.') }}</span>
        </div>
        <div class="form-group">
            <span class="label">3. Jangka Waktu</span>:
            <span class="value">{{ $pinjaman->jangka_waktu }} {{ $pinjaman->jangka_waktu_satuan }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">KETENTUAN PINJAMAN</div>
        <ol>
            <li>Simpanan Pokok sebesar Rp 100.000 harus disetor pada saat pembukaan rekening.</li>
            <li>Simpanan Wajib sebesar Rp 50.000 disetorkan setiap bulan.</li>
            <li>Penutupan Rekening akan dikenakan biaya sebesar Rp 50.000,- (lima puluh ribu rupiah).</li>
            <li>Rekening yang tidak aktif atau tidak memenuhi kewajiban simpanan selama 6 bulan berturut-turut akan dinyatakan dormant dan dapat ditutup oleh pihak koperasi.</li>
            <li>Nasabah wajib memberitahukan koperasi jika ada perubahan data pribadi.</li>
        </ol>

        <p style="text-align: justify;">
            Dengan menandatangani formulir ini, saya menyatakan bahwa data yang saya berikan adalah benar
            dan dapat dipertanggungjawabkan. Saya menyetujui ketentuan yang berlaku dan akan mematuhi
            seluruh peraturan yang ditetapkan oleh Koperasi SINARA ARTHA, termasuk ketentuan biaya
            penutupan rekening sebesar Rp 50.000,- jika saya memutuskan untuk menutup rekening ini.
        </p>
    </div>

    <div class="footer">
        <div class="signature">
            <p>SURABAYA, {{ now()->format('d F Y') }}</p>
            <p>Yang bertanda tangan,</p>
            <div class="signature-box"></div>
            <p>({{ $pinjaman->profile->first_name }} {{ $pinjaman->profile->last_name }})</p>
        </div>
    </div>
</body>
</html>
