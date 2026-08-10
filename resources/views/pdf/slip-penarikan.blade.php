<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Penarikan</title>
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
        <div>Slip Penarikan Simpanan</div>
        <div class="judul">SLIP PENARIKAN</div>
    </div>

    <table class="detail">
        <tr>
            <td class="label">Nomor Penarikan</td>
            <td>: {{ $penarikan->nomor_penarikan }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Pengajuan</td>
            <td>: {{ $penarikan->dikirim_at ? $penarikan->dikirim_at->format('d/m/Y H:i') : ($penarikan->created_at ? $penarikan->created_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i')) }}</td>
        </tr>
        <tr>
            <td class="label">Nama Anggota</td>
            <td>: {{ $penarikan->user?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Nomor Rekening</td>
            <td>: {{ $penarikan->tabungan?->no_tabungan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Produk Simpanan</td>
            <td>: {{ $penarikan->jenis_simpanan ?? '-' }}</td>
        </tr>
    </table>

    <div class="separator"></div>

    <table class="detail">
        <tr>
            <td class="label">Nominal Penarikan</td>
            <td>: Rp {{ number_format($penarikan->jumlah, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="jumlah-huruf">
        Terbilang: {{ $penarikan->jumlah ? ucwords(terbilang($penarikan->jumlah)) . ' Rupiah' : '-' }}
    </div>

    <div class="separator"></div>

    <table class="detail">
        <tr>
            <td class="label">Bank Tujuan</td>
            <td>: {{ $penarikan->nama_bank ?? $penarikan->bank ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Nama Nasabah</td>
            <td>: {{ $penarikan->nama_nasabah ?? '-' }}</td>
        </tr>
    </table>

    <div class="separator"></div>

    <table class="detail">
        <tr>
            <td class="label">Status</td>
            <td>: <span class="status">{{ $penarikan->status?->label() ?? '-' }}</span></td>
        </tr>
        <tr>
            <td class="label">Waktu Selesai</td>
            <td>: {{ $penarikan->selesai_at?->format('d/m/Y H:i') ?? $penarikan->disetujui_at?->format('d/m/Y H:i') ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Admin Pemeriksa</td>
            <td>: {{ $penarikan->pemeriksa?->name ?? '-' }}</td>
        </tr>
        @if ($penarikan->transaksiTabungan)
            <tr>
                <td class="label">No. Transaksi</td>
                <td>: {{ $penarikan->transaksiTabungan->kode_transaksi ?? '-' }}</td>
            </tr>
        @endif
        @if ($penarikan->referensi_transfer)
            <tr>
                <td class="label">Referensi Transfer</td>
                <td>: {{ $penarikan->referensi_transfer }}</td>
            </tr>
        @endif
    </table>

    <div class="separator"></div>

    <div class="footer">
        Diterima oleh,<br>
        {{ $penarikan->pemeriksa?->name ?? auth()->user()->name ?? '__________________' }}
    </div>
</body>
</html>
