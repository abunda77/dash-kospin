<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Formulir Pendaftaran Anggota</title>
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
        .terms {
            margin: 20px 0;
            text-align: justify;
        }
        .terms h2 {
            text-align: center;
            font-size: 14px;
            margin: 15px 0;
        }
        .terms-list {
            margin-left: 20px;
        }
        .terms-list li {
            margin-bottom: 5px;
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
        <h1>FORMULIR PENDAFTARAN ANGGOTA</h1>
        <h1>KOPERASI SINARA ARTHA</h1>
    </div>

    <div class="section">
        <div class="section-title">DATA PRIBADI</div>
        <div class="form-group">
            <span class="label">Nama Lengkap</span>:
            <span class="value">{{ $profile->first_name }} {{ $profile->last_name }}</span>
        </div>
        <div class="form-group">
            <span class="label">Tempat, Tanggal Lahir</span>:
            <span class="value">{{ $profile->birthday ? $profile->birthday->format('d/m/Y') : '-' }}</span>
        </div>
        <div class="form-group">
            <span class="label">Jenis Kelamin</span>:
            <span class="value">{{ $profile->gender }}</span>
        </div>
        <div class="form-group">
            <span class="label">Alamat Lengkap</span>:
            <span class="value">{{ $profile->address }}</span>
        </div>
        <div class="form-group">
            <span class="label">Nomor Telepon/HP</span>:
            <span class="value">{{ $profile->phone }}</span>
        </div>
        <div class="form-group">
            <span class="label">Email</span>:
            <span class="value">{{ $profile->email }}</span>
        </div>
        <div class="form-group">
            <span class="label">Pekerjaan</span>:
            <span class="value">{{ $profile->job }}</span>
        </div>
        <div class="form-group">
            <span class="label">Nomor KTP</span>:
            <span class="value">{{ $profile->no_identity }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">DATA KEUANGAN</div>
        <div class="form-group">
            <span class="label">Pendapatan Per Bulan</span>:
            <span class="value">Rp {{ number_format($profile->monthly_income, 0, ',', '.') }}</span>
        </div>
        <div class="form-group">
            <span class="label">Sumber Pendapatan Utama</span>:
            <span class="value">{{ $profile->job }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">DATA LAIN-LAIN</div>
        <div class="form-group">
            <span class="label">Referensi Anggota (jika ada)</span>:
            <span class="value"></span>
        </div>
        <div class="form-group">
            <span class="label">Tujuan Bergabung</span>:
            <span class="value"></span>
        </div>
    </div>

    <div class="terms">
        <h2>Syarat dan Ketentuan Menjadi Anggota Koperasi SINARA ARTHA</h2>

        <p>Dengan ini saya menyatakan bahwa saya telah memahami dan setuju untuk memenuhi syarat dan kewajiban sebagai anggota Koperasi SINARA ARTHA, sebagai berikut:</p>

        <h3>Syarat Menjadi Anggota:</h3>
        <ol class="terms-list">
            <li>Warga Negara Indonesia yang sudah berusia minimal 18 tahun atau sudah menikah.</li>
            <li>Memiliki KTP yang masih berlaku.</li>
            <li>Mengisi formulir pendaftaran anggota dengan lengkap dan benar.</li>
            <li>Membayar simpanan pokok dan simpanan wajib sesuai ketentuan koperasi.</li>
            <li>Menyetujui dan mematuhi anggaran dasar dan anggaran rumah tangga (AD/ART) Koperasi SINARA ARTHA.</li>
            <li>Bersedia mengikuti program dan kegiatan yang diselenggarakan oleh koperasi.</li>
        </ol>

        <h3>Kewajiban Anggota:</h3>
        <ol class="terms-list">
            <li>Membayar Simpanan Wajib setiap bulan sesuai dengan ketentuan koperasi.</li>
            <li>Menghadiri Rapat Anggota Tahunan (RAT) sebagai bentuk partisipasi dan tanggung jawab.</li>
            <li>Menaati peraturan dan ketentuan yang ditetapkan oleh koperasi.</li>
            <li>Berperan aktif dalam kegiatan dan usaha yang diselenggarakan oleh koperasi untuk meningkatkan kesejahteraan bersama.</li>
            <li>Tidak melakukan tindakan merugikan koperasi secara materi atau nama baik.</li>
            <li>Membayar pinjaman tepat waktu apabila meminjam dana dari koperasi, sesuai perjanjian dan peraturan yang berlaku.</li>
            <li>Melaporkan setiap perubahan data pribadi kepada pengurus koperasi.</li>
        </ol>

        <h3>Hak Anggota:</h3>
        <ol class="terms-list">
            <li>Mengikuti kegiatan usaha koperasi serta mendapatkan pelayanan yang sama.</li>
            <li>Mengajukan usulan atau saran dalam rapat anggota.</li>
            <li>Mendapatkan sisa hasil usaha (SHU) koperasi setiap tahun sesuai kontribusi.</li>
            <li>Meminjam dana sesuai peraturan yang berlaku di koperasi.</li>
            <li>Meminta laporan tahunan koperasi dan transparansi terkait operasional koperasi.</li>
        </ol>

        <p>Dengan menandatangani formulir ini, saya menyatakan bahwa saya telah membaca dan menyetujui syarat, kewajiban, serta hak sebagai anggota Koperasi SINARA ARTHA. Saya akan mematuhi seluruh peraturan yang berlaku di dalam koperasi.</p>
    </div>

    <div class="footer">
        <div class="signature">
            <p>Surabaya, {{ now()->format('d F Y') }}</p>
            <p>Yang bertanda tangan,</p>
            <div class="signature-box"></div>
            <p>({{ $profile->first_name }} {{ $profile->last_name }})</p>
        </div>
    </div>
</body>
</html>
