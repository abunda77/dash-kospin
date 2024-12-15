<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Reminder Pembayaran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Reminder Pembayaran</h2>
        <p>Tanggal: {{ $today->format('d F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>No Pinjaman</th>
                <th>Angsuran Pokok</th>
                <th>Sisa Hari</th>
                <th>WhatsApp</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $item)
            @php
                // Mengambil tanggal dari tanggal_jatuh_tempo
                $tanggalPembayaran = Carbon\Carbon::parse($item->tanggal_jatuh_tempo)->day;

                // Membuat tanggal jatuh tempo untuk bulan ini
                $jatuhTempoBulanIni = Carbon\Carbon::create(
                    $today->year,
                    $today->month,
                    $tanggalPembayaran
                )->startOfDay();

                // Jika hari ini sudah melewati tanggal pembayaran, hitung ke bulan depan
                if ($today->day > $tanggalPembayaran) {
                    $jatuhTempoBulanIni->addMonth();
                }

                // Hitung sisa hari
                $sisaHari = $today->diffInDays($jatuhTempoBulanIni, false);
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->profile->first_name }} {{ $item->profile->last_name }}</td>
                <td>{{ $item->no_pinjaman }}</td>
                <td>Rp. {{ number_format($item->jumlah_pinjaman / $item->jangka_waktu, 2, ',', '.') }}</td>
                <td>{{ $sisaHari }} hari</td>
                <td>{{ $item->profile->whatsapp }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
