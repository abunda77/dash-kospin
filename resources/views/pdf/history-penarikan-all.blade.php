<!DOCTYPE html>
<html>
<head>
    <title>History Penarikan</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
        }
    </style>
</head>
<body>
    <h2>History Penarikan</h2>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Nama Anggota</th>
                <th>Nilai Withdrawal</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaksi as $t)
            <tr>
                <td>{{ $t->tanggal_transaksi }}</td>
                <td>{{ $t->anggotaReferral->nama }}</td>
                <td>{{ number_format($t->nilai_withdrawal, 0, ',', '.') }}</td>
                <td>{{ $t->status_komisi }}</td>
                <td>{{ $t->keterangan }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
