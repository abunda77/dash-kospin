<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rincian Angsuran</title>
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
        .table-angsuran {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table-angsuran th, .table-angsuran td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }
        .table-angsuran th {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Rincian Angsuran Pinjaman</h2>
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
                <td>Tanggal Pinjaman</td>
                <td>: {{ $pinjaman->tanggal_pinjaman->format('d/m/Y') }}</td>
            </tr>
        </table>
    </div>

    <table class="table-angsuran">
        <thead>
            <tr>
                <th>Angsuran Ke</th>
                <th>Tanggal Bayar</th>
                <th>Pokok</th>
                <th>Bunga</th>
                <th>Denda</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaksi as $t)
            <tr>
                <td>{{ $t->angsuran_ke }}</td>
                <td>{{ $t->tanggal_pembayaran ? $t->tanggal_pembayaran->format('d/m/Y') : '-' }}</td>
                <td style="text-align: right">{{ number_format($t->angsuran_pokok, 0, ',', '.') }}</td>
                <td style="text-align: right">{{ number_format($t->angsuran_bunga, 0, ',', '.') }}</td>
                <td style="text-align: right">{{ number_format($t->denda, 0, ',', '.') }}</td>
                <td style="text-align: right">{{ number_format($t->total_pembayaran, 0, ',', '.') }}</td>
                <td>{{ $t->status_pembayaran }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align: right"><strong>Total:</strong></td>
                <td style="text-align: right"><strong>{{ number_format($transaksi->sum('angsuran_pokok'), 0, ',', '.') }}</strong></td>
                <td style="text-align: right"><strong>{{ number_format($transaksi->sum('angsuran_bunga'), 0, ',', '.') }}</strong></td>
                <td style="text-align: right"><strong>{{ number_format($transaksi->sum('denda'), 0, ',', '.') }}</strong></td>
                <td style="text-align: right"><strong>{{ number_format($transaksi->sum('total_pembayaran'), 0, ',', '.') }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
