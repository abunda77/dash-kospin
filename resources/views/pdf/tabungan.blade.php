<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Formulir Pembukaan Rekening</title>
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
        <h1>FORMULIR PEMBUKAAN REKENING TABUNGAN</h1>
        <h1>KOPERASI SINARA ARTHA</h1>
    </div>

    <div class="section">
        <div class="form-group">
            <span class="label">Nomor Rekening</span>:
            <span class="value">{{ $tabungan->no_tabungan }}</span>
        </div>
        <div class="form-group">
            <span class="label">Tanggal Pembukaan Rekening</span>:
            <span class="value">{{ $tabungan->tanggal_buka_rekening->format('d/m/Y') }}</span>
        </div>
        <div class="form-group">
            <span class="label">No. Kontrak</span>:
            <span class="value">{{ $tabungan->no_tabungan }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">DATA NASABAH</div>
        <div class="form-group">
            <span class="label">1. Nama Lengkap</span>:
            <span class="value">{{ $tabungan->profile->first_name }} {{ $tabungan->profile->last_name }}</span>
        </div>
        <div class="form-group">
            <span class="label">2. Nama Panggilan</span>:
            <span class="value">{{ $tabungan->profile->first_name }}</span>
        </div>
        <div class="form-group">
            <span class="label">3. Tempat, Tanggal Lahir</span>:
            <span class="value">{{ $tabungan->profile->birthday ? $tabungan->profile->birthday->format('d/m/Y') : '-' }}</span>
        </div>
        <div class="form-group">
            <span class="label">4. Jenis Kelamin</span>:
            <span class="value">{{ $tabungan->profile->gender }}</span>
        </div>
        <div class="form-group">
            <span class="label">5. Status Pernikahan</span>:
            <span class="value">{{ $tabungan->profile->mariage }}</span>
        </div>
        <div class="form-group">
            <span class="label">6. Nama Ibu Kandung</span>:
            <span class="value">{{ $tabungan->profile->ibu_kandung }}</span>
        </div>
        <div class="form-group">
            <span class="label">7. Alamat KTP</span>:
            <span class="value">{{ $tabungan->profile->address }}</span>
        </div>
        <div class="form-group">
            <span class="label">9. Nomor Telepon/HP</span>:
            <span class="value">{{ $tabungan->profile->phone }}</span>
        </div>
        <div class="form-group">
            <span class="label">10. Email</span>:
            <span class="value">{{ $tabungan->profile->email }}</span>
        </div>
        <div class="form-group">
            <span class="label">11. Pekerjaan</span>:
            <span class="value">{{ $tabungan->profile->job }}</span>
        </div>
        <div class="form-group">
            <span class="label">14. Penghasilan Per Bulan</span>:
            <span class="value">Rp {{ number_format($tabungan->profile->monthly_income, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">DATA IDENTITAS</div>
        <div class="form-group">
            <span class="label">1. Nomor KTP</span>:
            <span class="value">{{ $tabungan->profile->no_identity }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">INFORMASI LAINNYA</div>
        <div class="form-group">
            <span class="label">1. Jenis Rekening</span>:
            <span class="value">{{ $tabungan->produkTabungan->nama_produk }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">KETENTUAN REKENING</div>
        <ol>
            <li>Simpanan Pokok sebesar Rp 100.000 harus disetor pada saat pembukaan rekening.</li>
            <li>Simpanan Wajib sebesar Rp 50.000 disetorkan setiap bulan.</li>
            <li>Penutupan Rekening akan dikenakan biaya sebesar Rp 50.000,- (lima puluh ribu rupiah).</li>
            <li>Rekening yang tidak aktif selama 6 bulan berturut-turut akan dinyatakan dormant.</li>
            <li>Nasabah wajib memberitahukan koperasi jika ada perubahan data pribadi.</li>
        </ol>
    </div>

    <div class="footer">
        <div class="signature">
            <p>Surabaya, {{ now()->format('d F Y') }}</p>
            <p>Yang bertanda tangan,</p>
            <div class="signature-box"></div>
            <p>({{ $tabungan->profile->first_name }} {{ $tabungan->profile->last_name }})</p>
        </div>
    </div>
</body>
</html>