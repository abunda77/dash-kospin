<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profil Nasabah</title>
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
        .profile-image {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            position: absolute;
            top: 0;
            right: 0;
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
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            display: inline-block;
        }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-info { background-color: #dbeafe; color: #1e40af; }
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
        <h1>PROFIL NASABAH</h1>
        @if($profile->avatar)
            <img src="{{ storage_path('app/public/' . $profile->avatar) }}" class="profile-image">
        @endif
    </div>

    <div class="two-column">
        <div class="section">
            <div class="section-title">Informasi Pribadi</div>
            <table class="info-table">
                <tr>
                    <td class="label">Nama Pengguna</td>
                    <td class="value">{{ $profile->user->name }}</td>
                </tr>
                <tr>
                    <td class="label">Nama Lengkap</td>
                    <td class="value">{{ $profile->first_name }} {{ $profile->last_name }}</td>
                </tr>
                <tr>
                    <td class="label">Jenis Kelamin</td>
                    <td class="value">{{ $profile->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Lahir</td>
                    <td class="value">{{ $profile->birthday->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Status</td>
                    <td class="value">{{ ucfirst($profile->mariage) }}</td>
                </tr>
                <tr>
                    <td class="label">Pekerjaan</td>
                    <td class="value">{{ $profile->job }}</td>
                </tr>
                <tr>
                    <td class="label">Nama Ibu Kandung</td>
                    <td class="value">{{ $profile->ibu_kandung }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Kontak</div>
            <table class="info-table">
                <tr>
                    <td class="label">Telepon</td>
                    <td class="value">{{ $profile->phone }}</td>
                </tr>
                <tr>
                    <td class="label">WhatsApp</td>
                    <td class="value">{{ $profile->whatsapp }}</td>
                </tr>
                <tr>
                    <td class="label">Email</td>
                    <td class="value">{{ $profile->email }}</td>
                </tr>
                <tr>
                    <td class="label">Alamat</td>
                    <td class="value">{{ $profile->address }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="two-column">
        <div class="section">
            <div class="section-title">Identitas</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="label">Tanda Pengenal</div>
                    <div class="value">{{ strtoupper($profile->sign_identity) }}</div>
                </div>
                <div class="info-item">
                    <div class="label">No. Identitas</div>
                    <div class="value">{{ $profile->no_identity }}</div>
                </div>
            </div>

            @php
                $imagePath = null;
                if ($profile->image_identity) {
                    if (is_string($profile->image_identity)) {
                        $imagePath = storage_path('app/public/' . $profile->image_identity);
                    } elseif (is_array($profile->image_identity) && !empty($profile->image_identity)) {
                        $imagePath = storage_path('app/public/' . $profile->image_identity[0]);
                    }
                }
            @endphp

            @if($imagePath && file_exists($imagePath))
                <div style="margin-top: 8px; text-align: center;">
                    <img src="{{ $imagePath }}" style="max-width: 200px; max-height: 100px;">
                </div>
            @endif
        </div>

        <div class="section">
            <div class="section-title">Informasi Tambahan</div>
            <table class="info-table">
                <tr>
                    <td class="label">Pendapatan</td>
                    <td class="value">Rp {{ number_format($profile->monthly_income, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Tipe Member</td>
                    <td class="value"><span class="badge badge-info">{{ ucfirst($profile->type_member) }}</span></td>
                </tr>
                <tr>
                    <td class="label">Status</td>
                    <td class="value"><span class="badge badge-{{ $profile->is_active ? 'success' : 'warning' }}">
                        {{ $profile->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                    </td>
                </tr>
                @if($profile->notes)
                <tr>
                    <td class="label">Catatan</td>
                    <td class="value">{{ $profile->notes }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    <div style="clear: both;"></div>

    <div class="section">
        <div class="section-title">Alamat Lengkap</div>
        <table class="info-table">
            @php
                $getRegionName = function($code) {
                    return DB::table('regions')->where('code', $code)->value('name') ?? '-';
                };
            @endphp
            <tr>
                <td class="label" style="width: 25%">Provinsi</td>
                <td class="value">{{ $getRegionName($profile->province_id) }}</td>
                <td class="label" style="width: 25%">Kabupaten/Kota</td>
                <td class="value">{{ $getRegionName($profile->district_id) }}</td>
            </tr>
            <tr>
                <td class="label">Kecamatan</td>
                <td class="value">{{ $getRegionName($profile->city_id) }}</td>
                <td class="label">Desa/Kelurahan</td>
                <td class="value">{{ $getRegionName($profile->village_id) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }} | Dokumen ini digenerate secara otomatis dan sah tanpa tanda tangan</p>
    </div>
</body>
</html>
