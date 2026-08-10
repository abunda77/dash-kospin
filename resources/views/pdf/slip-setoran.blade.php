<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Setoran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 6px;
        }
        .header .koperasi {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header .judul {
            font-size: 11px;
            font-weight: bold;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 3px 0;
            margin-top: 4px;
        }
        table.detail {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        table.detail td {
            padding: 2px 0;
            vertical-align: top;
        }
        table.detail td.label {
            width: 42%;
        }
        .separator {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        .total {
            font-size: 12px;
            font-weight: bold;
        }
        .jumlah-huruf {
            font-style: italic;
            margin-top: 4px;
        }
        .footer {
            text-align: center;
            margin-top: 12px;
            font-size: 9px;
        }
        .status {
            text-transform: uppercase;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="koperasi">{{ config('app.name') }}</div>
        <div>Slip Setoran Simpanan</div>
        <div class="judul">SLIP SETORAN</div>
    </div>

    <table class="detail">
        <tr>
            <td class="label">Nomor Setoran</td>
            <td>: {{ $setoran->nomor_setoran }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Setoran</td>
            <td>: {{ $setoran->created_at ? $setoran->created_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Nama Anggota</td>
            <td>: {{ $setoran->user?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Nomor Rekening</td>
            <td>: {{ $setoran->tabungan?->no_tabungan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Produk Simpanan</td>
            <td>: {{ $setoran->jenis_simpanan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Metode Pembayaran</td>
            <td>: {{ $setoran->metode_pembayaran?->label() ?? '-' }}</td>
        </tr>
    </table>

    <div class="separator"></div>

    <table class="detail">
        <tr>
            <td class="label">Nominal Setoran</td>
            <td>: Rp {{ number_format($setoran->jumlah, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Kode Unik</td>
            <td>: {{ $setoran->kode_unik ?? 0 }}</td>
        </tr>
        <tr class="total">
            <td class="label">Total Bayar</td>
            <td>: Rp {{ number_format($setoran->jumlah_bayar, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="jumlah-huruf">
        Terbilang: {{ $setoran->jumlah_bayar ? ucwords(terbilang($setoran->jumlah_bayar)) . ' Rupiah' : '-' }}
    </div>

    <div class="separator"></div>

    <table class="detail">
        <tr>
            <td class="label">Status</td>
            <td>: <span class="status">{{ $setoran->status?->label() ?? '-' }}</span></td>
        </tr>
        <tr>
            <td class="label">Waktu Selesai</td>
            <td>: {{ $setoran->selesai_at?->format('d/m/Y H:i') ?? $setoran->disetujui_at?->format('d/m/Y H:i') ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Admin Pemeriksa</td>
            <td>: {{ $setoran->pemeriksa?->name ?? '-' }}</td>
        </tr>
        @if ($setoran->transaksiTabungan)
            <tr>
                <td class="label">No. Transaksi</td>
                <td>: {{ $setoran->transaksiTabungan->kode_transaksi ?? '-' }}</td>
            </tr>
        @endif
        @if ($setoran->nama_pembayar)
            <tr>
                <td class="label">Nama Pembayar</td>
                <td>: {{ $setoran->nama_pembayar }}</td>
            </tr>
        @endif
    </table>

    <div class="separator"></div>

    <div class="footer">
        Diterima oleh,<br>
        {{ $setoran->pemeriksa?->name ?? auth()->user()->name ?? '__________________' }}
    </div>
</body>
</html>
