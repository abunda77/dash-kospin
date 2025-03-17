<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Formulir Pembukaan Deposito</title>
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
        <h1>FORMULIR PEMBUKAAN REKENING DEPOSITO</h1>
        <h1>KOPERASI SINARA ARTHA</h1>
    </div>

    <div class="section">
        <div class="form-group">
            <span class="label">Nomor Rekening</span>:
            <span class="value">{{ $deposito->nomor_rekening }}</span>
        </div>
        <div class="form-group">
            <span class="label">Tanggal Pembukaan</span>:
            <span class="value">{{ \Carbon\Carbon::parse($deposito->tanggal_pembukaan)->format('d/m/Y') }}</span>
        </div>
        <div class="form-group">
            <span class="label">Jangka Waktu</span>:
            <span class="value">{{ $deposito->jangka_waktu }} Bulan</span>
        </div>
        <div class="form-group">
            <span class="label">Tanggal Jatuh Tempo</span>:
            <span class="value">{{ \Carbon\Carbon::parse($deposito->tanggal_jatuh_tempo)->format('d/m/Y') }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">DATA NASABAH</div>
        <div class="form-group">
            <span class="label">1. Nama Lengkap</span>:
            <span class="value">{{ $deposito->profile->first_name }} {{ $deposito->profile->last_name }}</span>
        </div>
        <div class="form-group">
            <span class="label">2. Nama Panggilan</span>:
            <span class="value">{{ $deposito->profile->first_name }}</span>
        </div>
        <div class="form-group">
            <span class="label">3. Tempat, Tanggal Lahir</span>:
            <span class="value">{{ $deposito->profile->birthday ? $deposito->profile->birthday->format('d/m/Y') : '-' }}</span>
        </div>
        <div class="form-group">
            <span class="label">4. Jenis Kelamin</span>:
            <span class="value">{{ $deposito->profile->gender }}</span>
        </div>
        <div class="form-group">
            <span class="label">5. Status Pernikahan</span>:
            <span class="value">{{ $deposito->profile->mariage }}</span>
        </div>
        <div class="form-group">
            <span class="label">6. Nama Ibu Kandung</span>:
            <span class="value">{{ $deposito->profile->ibu_kandung }}</span>
        </div>
        <div class="form-group">
            <span class="label">7. Alamat KTP</span>:
            <span class="value">{{ $deposito->profile->address }}</span>
        </div>
        @php
            $getRegionName = function($code) {
                return DB::table('regions')->where('code', $code)->value('name') ?? '-';
            };
        @endphp
        <div class="form-group">
            <span class="label">Provinsi</span>:
            <span class="value">{{ $getRegionName($deposito->profile->province_id) }}</span>
        </div>
        <div class="form-group">
            <span class="label">Kabupaten/Kota</span>:
            <span class="value">{{ $getRegionName($deposito->profile->district_id) }}</span>
        </div>
        <div class="form-group">
            <span class="label">Kecamatan</span>:
            <span class="value">{{ $getRegionName($deposito->profile->city_id) }}</span>
        </div>
        <div class="form-group">
            <span class="label">Desa/Kelurahan</span>:
            <span class="value">{{ $getRegionName($deposito->profile->village_id) }}</span>
        </div>
        <div class="form-group">
            <span class="label">8. Nomor Telepon/HP</span>:
            <span class="value">{{ $deposito->profile->phone }}</span>
        </div>
        <div class="form-group">
            <span class="label">9. Email</span>:
            <span class="value">{{ $deposito->profile->email }}</span>
        </div>
        <div class="form-group">
            <span class="label">10. Pekerjaan</span>:
            <span class="value">{{ $deposito->profile->job }}</span>
        </div>
        <div class="form-group">
            <span class="label">11. Penghasilan Per Bulan</span>:
            <span class="value">Rp {{ number_format($deposito->profile->monthly_income, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">DATA IDENTITAS</div>
        <div class="form-group">
            <span class="label">1. Nomor KTP</span>:
            <span class="value">{{ $deposito->profile->no_identity }}</span>
        </div>
        <div class="form-group">
            <span class="label">2. Nomor NPWP (jika ada)</span>:
            <span class="value">-</span>
        </div>
        <div class="form-group">
            <span class="label">3. Jenis Identitas Lain</span>:
            <span class="value">{{ $deposito->profile->sign_identity }}</span>
        </div>
        <div class="form-group">
            <span class="label">4. Nomor Identitas Lain</span>:
            <span class="value">-</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">INFORMASI DEPOSITO</div>
        <div class="form-group">
            <span class="label">1. Nominal Penempatan</span>:
            <span class="value">Rp {{ number_format($deposito->nominal_penempatan, 0, ',', '.') }}</span>
        </div>
        <div class="form-group">
            <span class="label">2. Suku Bunga</span>:
            <span class="value">{{ $deposito->rate_bunga }}% p.a</span>
        </div>
        <div class="form-group">
            <span class="label">3. Nominal Bunga</span>:
            <span class="value">Rp {{ number_format($deposito->nominal_bunga, 0, ',', '.') }}</span>
        </div>
        <div class="form-group">
            <span class="label">4. Perpanjangan Otomatis</span>:
            <span class="value">{{ $deposito->perpanjangan_otomatis ? 'Ya' : 'Tidak' }}</span>
        </div>
    </div>
    <div class="section">
        <div class="section-title">INFORMASI REKENING BANK</div>
        <div class="form-group">
            <span class="label">1. Nama Bank</span>:
            <span class="value">{{ match($deposito->nama_bank) {
                'bca' => 'Bank Central Asia (BCA)',
                'bni' => 'Bank Negara Indonesia (BNI)',
                'bri' => 'Bank Rakyat Indonesia (BRI)',
                'mandiri' => 'Bank Mandiri',
                'cimb' => 'CIMB Niaga',
                'danamon' => 'Bank Danamon',
                'permata' => 'Bank Permata',
                'btn' => 'Bank Tabungan Negara (BTN)',
                'bsi' => 'Bank Syariah Indonesia (BSI)',
                'mega' => 'Bank Mega',
                'ocbc' => 'OCBC NISP',
                'panin' => 'Panin Bank',
                'uob' => 'UOB Indonesia',
                'maybank' => 'Maybank Indonesia',
                'other' => 'Bank Lainnya'
            } }}</span>
        </div>
        <div class="form-group">
            <span class="label">2. Nomor Rekening</span>:
            <span class="value">{{ $deposito->nomor_rekening_bank }}</span>
        </div>
        <div class="form-group">
            <span class="label">3. Nama Pemilik Rekening</span>:
            <span class="value">{{ $deposito->nama_pemilik_rekening_bank }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">KETENTUAN DEPOSITO</div>
        <ol>
            {{-- <li>Pencairan deposito sebelum jatuh tempo akan dikenakan penalti sebesar 0.5% dari nominal deposito.</li> --}}
            <li>Perpanjangan otomatis akan mengikuti suku bunga yang berlaku saat perpanjangan.</li>
            <li>Bunga deposito akan dibayarkan setiap bulan ke rekening tabungan yang ditunjuk.</li>
            <li>Deposito dapat dijadikan jaminan kredit dengan nilai maksimal 80% dari nominal deposito.</li>
            <li>Nasabah wajib memberitahukan koperasi jika ada perubahan data pribadi.</li>
        </ol>

        <p style="text-align: justify;">
            Dengan menandatangani formulir ini, saya menyatakan bahwa data yang saya berikan adalah benar
            dan dapat dipertanggungjawabkan. Saya menyetujui ketentuan yang berlaku dan akan mematuhi
            seluruh peraturan yang ditetapkan oleh Koperasi SINARA ARTHA, termasuk ketentuan penalti
            pencairan sebelum jatuh tempo dan mekanisme perpanjangan deposito.
        </p>
    </div>



    <div class="footer">
        <div class="signature">
            <p>Surabaya, {{ now()->format('d F Y') }}</p>
            <p>Yang bertanda tangan,</p>
            <div class="signature-box"></div>
            <p>({{ $deposito->profile->first_name }} {{ $deposito->profile->last_name }})</p>
        </div>
    </div>
</body>
</html>
