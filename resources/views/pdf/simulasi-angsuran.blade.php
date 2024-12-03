<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Simulasi Angsuran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
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
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }
        .table-simulasi th {
            background-color: #f0f0f0;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
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
                <td>: {{ $pinjaman->jangka_waktu }} bulan</td>
            </tr>
            <tr>
                <td>Bunga</td>
                <td>: {{ $pinjaman->biayaBungaPinjaman->persentase_bunga }}% per tahun</td>
            </tr>
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
