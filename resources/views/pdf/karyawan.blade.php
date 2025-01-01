<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Karyawan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.4;
            color: #333;
            font-size: 11px;
            margin: 0;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            padding: 6px 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            position: relative;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
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
        .profile-image {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: block;
            margin: 10px auto;
        }
        .section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        .section-title {
            background-color: #2c3e50;
            color: white;
            padding: 5px 10px;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 12px;
            border-radius: 3px;
        }
        .info-table {
            width: 100%;
        }
        .info-table td {
            padding: 4px 8px;
        }
        .info-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .label {
            font-weight: bold;
            color: #444;
            width: 30%;
        }
        .value {
            color: #333;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
        .two-column {
            width: 48%;
            float: left;
            margin-right: 2%;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo_koperasi.jpg') }}" alt="Logos">
        <h1>DATA KARYAWAN</h1>
    </div>

    <div class="two-column">
        <div class="section">
            <div class="section-title">Informasi Pribadi</div>
            <table class="info-table">
                <tr>
                    <td class="label">NIK Karyawan</td>
                    <td class="value">{{ $karyawan->nik_karyawan }}</td>
                </tr>
                <tr>
                    <td class="label">Nama Lengkap</td>
                    <td class="value">{{ $karyawan->first_name }} {{ $karyawan->last_name }}</td>
                </tr>
                <tr>
                    <td class="label">Tempat, Tanggal Lahir</td>
                    <td class="value">{{ $karyawan->tempat_lahir }}, {{ $karyawan->tanggal_lahir->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Jenis Kelamin</td>
                    <td class="value">{{ $karyawan->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                </tr>
                <tr>
                    <td class="label">Status Pernikahan</td>
                    <td class="value">{{ $karyawan->status_pernikahan }}</td>
                </tr>
                <tr>
                    <td class="label">Agama</td>
                    <td class="value">{{ $karyawan->agama }}</td>
                </tr>
                <tr>
                    <td class="label">Golongan Darah</td>
                    <td class="value">{{ $karyawan->golongan_darah }}</td>
                </tr>
                <tr>
                    <td class="label">Alamat</td>
                    <td class="value">{{ $karyawan->alamat }}</td>
                </tr>
                <tr>
                    <td class="label">No. KTP</td>
                    <td class="value">{{ $karyawan->no_ktp }}</td>
                </tr>
                <tr>
                    <td class="label">No. NPWP</td>
                    <td class="value">{{ $karyawan->no_npwp }}</td>
                </tr>
                <tr>
                    <td class="label">Email</td>
                    <td class="value">{{ $karyawan->email }}</td>
                </tr>
                <tr>
                    <td class="label">No. Telepon</td>
                    <td class="value">{{ $karyawan->no_telepon }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Informasi Kepegawaian</div>
            <table class="info-table">
                <tr>
                    <td class="label">Nomor Pegawai</td>
                    <td class="value">{{ $karyawan->nomor_pegawai }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Bergabung</td>
                    <td class="value">{{ $karyawan->tanggal_bergabung->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Status Kepegawaian</td>
                    <td class="value">{{ $karyawan->status_kepegawaian }}</td>
                </tr>
                <tr>
                    <td class="label">Departemen</td>
                    <td class="value">{{ $karyawan->departemen }}</td>
                </tr>
                <tr>
                    <td class="label">Jabatan</td>
                    <td class="value">{{ $karyawan->jabatan }}</td>
                </tr>
                <tr>
                    <td class="label">Level Jabatan</td>
                    <td class="value">{{ $karyawan->level_jabatan }}</td>
                </tr>
                <tr>
                    <td class="label">Lokasi Kerja</td>
                    <td class="value">{{ $karyawan->lokasi_kerja }}</td>
                </tr>
                <tr>
                    <td class="label">Gaji Pokok</td>
                    <td class="value">Rp {{ number_format($karyawan->gaji_pokok, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Status</td>
                    <td class="value">{{ $karyawan->is_active ? 'Aktif' : 'Tidak Aktif' }}</td>
                </tr>
                @if(!$karyawan->is_active)
                <tr>
                    <td class="label">Tanggal Keluar</td>
                    <td class="value">{{ $karyawan->tanggal_keluar ? $karyawan->tanggal_keluar->format('d/m/Y') : '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Alasan Keluar</td>
                    <td class="value">{{ $karyawan->alasan_keluar ?? '-' }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    <div class="two-column">
        <div class="section">
            <div class="section-title">Pendidikan</div>
            <table class="info-table">
                <tr>
                    <td class="label">Pendidikan Terakhir</td>
                    <td class="value">{{ $karyawan->pendidikan_terakhir }}</td>
                </tr>
                <tr>
                    <td class="label">Institusi</td>
                    <td class="value">{{ $karyawan->nama_institusi }}</td>
                </tr>
                <tr>
                    <td class="label">Jurusan</td>
                    <td class="value">{{ $karyawan->jurusan }}</td>
                </tr>
                <tr>
                    <td class="label">Tahun Lulus</td>
                    <td class="value">{{ $karyawan->tahun_lulus }}</td>
                </tr>
                <tr>
                    <td class="label">IPK</td>
                    <td class="value">{{ $karyawan->ipk }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Informasi Bank & BPJS</div>
            <table class="info-table">
                <tr>
                    <td class="label">Nama Bank</td>
                    <td class="value">{{ $karyawan->nama_bank }}</td>
                </tr>
                <tr>
                    <td class="label">Nomor Rekening</td>
                    <td class="value">{{ $karyawan->nomor_rekening }}</td>
                </tr>
                <tr>
                    <td class="label">Nama Pemilik Rekening</td>
                    <td class="value">{{ $karyawan->nama_pemilik_rekening }}</td>
                </tr>
                <tr>
                    <td class="label">No. BPJS Kesehatan</td>
                    <td class="value">{{ $karyawan->no_bpjs_kesehatan }}</td>
                </tr>
                <tr>
                    <td class="label">No. BPJS Ketenagakerjaan</td>
                    <td class="value">{{ $karyawan->no_bpjs_ketenagakerjaan }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Kontak Darurat</div>
            <table class="info-table">
                <tr>
                    <td class="label">Nama</td>
                    <td class="value">{{ $karyawan->kontak_darurat_nama }}</td>
                </tr>
                <tr>
                    <td class="label">Hubungan</td>
                    <td class="value">{{ $karyawan->kontak_darurat_hubungan }}</td>
                </tr>
                <tr>
                    <td class="label">Nomor Telepon</td>
                    <td class="value">{{ $karyawan->kontak_darurat_telepon }}</td>
                </tr>
            </table>
            @if($karyawan->foto_profil)
                <img src="{{ storage_path('app/public/' . $karyawan->foto_profil) }}" class="profile-image">
            @endif
        </div>
    </div>

    <div style="clear: both;"></div>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }} | Dokumen ini digenerate secara otomatis dan sah tanpa tanda tangan</p>
    </div>
</body>
</html>
