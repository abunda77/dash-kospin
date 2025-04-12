<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Simulasi Angsuran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            padding: 10px 0;
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
        .header h2 {
            margin: 10px;
            padding: 0;
            font-size: 20px;
            font-weight: bold;
            color: #2d3748;
            line-height: 1.4;
        }
        .profile-info {
            margin-bottom: 20px;
        }
        .profile-info table {
            width: 100%;
        }
        .profile-info td {
            padding: 3px;
        }
        .table-simulasi {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table-simulasi th, .table-simulasi td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .table-simulasi th {
            background-color: #4a5568;
            color: white;
            font-size: 13px;
            font-weight: bold;
        }
        .table-simulasi tr:nth-child(even) {
            background-color: #e3e5e7;
        }
        .table-simulasi tr:nth-child(odd) {
            background-color: #ffffff;
        }
        .table-simulasi tr:hover {
            background-color: #f5f5f5;
        }
        .text-right {
            text-align: right;

        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo_koperasi.jpg') }}" alt="Logos">
        {{-- <img src="{{ storage_path('app/public/images/logo_koperasi.jpg') }}" alt="Logo"> --}}
        <h2>Simulasi Angsuran Pinjaman</h2>
    </div>

    <div class="profile-info">
        <table>
            <tr>
                <td width="150">No. Pinjaman</td>
                <td>: {{ $pinjaman->no_pinjaman }}</td>
            </tr>
            <tr>
                <td>Nama Anggota</td>
                <td>: {{ $pinjaman->profile->first_name }} {{ $pinjaman->profile->last_name }}</td>
            </tr>
            <tr>
                <td>Jumlah Pinjaman</td>
                <td>: Rp {{ number_format($pinjaman->jumlah_pinjaman, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Jangka Waktu</td>
                <td>: {{ $pinjaman->jangka_waktu }} {{ $pinjaman->jangka_waktu_satuan }}</td>
            </tr>
            @if($pinjaman->jangka_waktu_satuan != 'minggu')
            <tr>
                <td>Bunga</td>
                <td>: {{ $pinjaman->biayaBungaPinjaman->persentase_bunga }}% per tahun</td>
            </tr>
            @endif
            <tr>
                <td>Tanggal Pinjaman</td>
                <td>: {{ $pinjaman->tanggal_pinjaman->format('d/m/Y') }}</td>
            </tr>
        </table>
    </div>

    <table class="table-simulasi">
        <thead>
            <tr>
                <th>Periode</th>
                <th>Tanggal Jatuh Tempo</th>
                <th>Angsuran Pokok</th>
                <th>Angsuran Bunga</th>
                <th>Total Angsuran</th>
                <th>Sisa Pokok</th>
            </tr>
        </thead>
        <tbody>
            @foreach($angsuranList as $angsuran)
            <tr>
                <td>{{ $angsuran['periode'] }}</td>
                <td>{{ $angsuran['tanggal_jatuh_tempo'] }}</td>
                <td class="text-right">{{ number_format($angsuran['pokok'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($angsuran['bunga'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($angsuran['angsuran'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($angsuran['sisa_pokok'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-right"><strong>Total:</strong></td>
                <td class="text-right"><strong>{{ number_format(collect($angsuranList)->sum('pokok'), 0, ',', '.') }}</strong></td>
                <td class="text-right"><strong>{{ number_format(collect($angsuranList)->sum('bunga'), 0, ',', '.') }}</strong></td>
                <td class="text-right"><strong>{{ number_format(collect($angsuranList)->sum('angsuran'), 0, ',', '.') }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
