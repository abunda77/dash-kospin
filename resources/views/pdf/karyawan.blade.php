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

        th,
        td {
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

    @if ($karyawan->foto_profil)
        <div style="text-align: center; margin-bottom: 15px;">
            <img src="{{ storage_path('app/public/' . $karyawan->foto_profil) }}" class="profile-image">
        </div>
    @endif

    <div class="section">
        <div class="section-title">Informasi Karyawan</div>
        <table class="info-table">
            <tr>
                <td class="label">No NIK</td>
                <td class="value">{{ $karyawan->nik_karyawan }}</td>
            </tr>
            <tr>
                <td class="label">Nama</td>
                <td class="value">{{ $karyawan->nama }}</td>
            </tr>
            <tr>
                <td class="label">Tempat Lahir</td>
                <td class="value">{{ $karyawan->tempat_lahir }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal Lahir</td>
                <td class="value">{{ \Carbon\Carbon::parse($karyawan->tanggal_lahir)->format('d F Y') }}</td>
            </tr>
            <tr>
                <td class="label">Alamat</td>
                <td class="value">{{ $karyawan->alamat }}</td>
            </tr>
            <tr>
                <td class="label">No. Telepon</td>
                <td class="value">{{ $karyawan->no_telepon }}</td>
            </tr>
            @if ($karyawan->no_telepon_keluarga)
                <tr>
                    <td class="label">No. HP Keluarga</td>
                    <td class="value">{{ $karyawan->no_telepon_keluarga }}</td>
                </tr>
            @endif
            <tr>
                <td class="label">Status</td>
                <td class="value">
                    <span
                        style="padding: 2px 8px; border-radius: 3px; font-size: 10px; background-color: {{ $karyawan->is_active ? '#d1fae5' : '#fef3c7' }}; color: {{ $karyawan->is_active ? '#065f46' : '#92400e' }};">
                        {{ $karyawan->is_active ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }} | Dokumen ini digenerate secara otomatis dan sah tanpa
            tanda tangan</p>
    </div>
</body>

</html>
