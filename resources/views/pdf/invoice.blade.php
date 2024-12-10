<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 10px; text-align: left; }
        .title { text-align: center; font-size: 24px; margin-bottom: 20px; }
        .signature { text-align: right; margin-top: 10px; }
        .date { text-align: right; margin-top: 20px; }
        .billing { text-align: center; margin-top: 100px; }
    </style>
</head>
<body>
    <h1 class="title">Invoice Pembayaran Angsuran</h1>
    <table>
        <tr>
            <th>Nama</th>
            <td>{{ $nama }}</td>
        </tr>
        <tr>
            <th>No Pinjaman</th>
            <td>{{ $no_pinjaman }}</td>
        </tr>
        <tr>
            <th>Angsuran Ke</th>
            <td>{{ $angsuran_ke }}</td>
        </tr>
        <tr>
            <th>Pokok</th>
            <td>Rp. {{ number_format($angsuran_pokok, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Denda</th>
            <td>Rp. {{ number_format($denda, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Total Pembayaran</th>
            <td>Rp. {{ number_format($total_pembayaran, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Tanggal Bayar</th>
            <td>{{ $tanggal_pembayaran }}</td>
        </tr>
    </table>
    <p>Terima kasih atas pembayaran Anda.</p>

    <div class="date">
        Surabaya, {{ date('d/m/Y') }}
    </div>

    <div class="signature">
        Hormat kami,<br><br><br><br>

         (Koperasi Siantar Artha)
    </div>
{{--
    <div class="billing">
        Billing Service
    </div> --}}
</body>
</html>
