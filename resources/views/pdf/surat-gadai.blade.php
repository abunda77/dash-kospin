<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Surat Perjanjian Gadai</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
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
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin: 5px 0;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            text-align: center;
        }
        .party-info {
            margin-bottom: 20px;
            padding-left: 20px;
        }
        .party-info p {
            margin: 5px 0;
        }
        .article {
            margin-bottom: 20px;
            text-align: justify;
        }
        .article h3 {
            font-size: 13px;
            margin-bottom: 10px;
            text-align: center;
            font-weight: bold;
        }
        .article ol {
            margin: 0;
            padding-left: 20px;
        }
        .article li {
            margin-bottom: 10px;
        }
        .signatures {
            margin-top: 50px;
            display: table;
            width: 100%;
        }
        .sign-column {
            display: table-cell;
            width: 50%;
            text-align: center;
        }
        .sign-box {
            border: 1px solid #999;
            height: 80px;
            margin: 10px auto;
            width: 200px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo_koperasi.jpg') }}" alt="Logo Koperasi">
        <h1>SURAT PERJANJIAN GADAI BARANG ELEKTRONIK</h1>
        <h1>KOPERASI SINARA ARTHA</h1>
    </div>

    <p>Pada hari ini {{ now()->locale('id')->isoFormat('dddd') }}, {{ now()->locale('id')->isoFormat('D MMMM Y') }} di SURABAYA, kami yang bertanda tangan di bawah ini:</p>

    <div class="section">
        <div class="section-title">PIHAK PERTAMA</div>
        <div class="party-info">
            <table border="0" style="width: 100%">
                <tr>
                    <td style="width: 150px">Nama</td>
                    <td style="width: 10px">:</td>
                    <td>{{ $gadai->pinjaman->profile->first_name }} {{ $gadai->pinjaman->profile->last_name }}</td>
                </tr>
                <tr>
                    <td>NIK</td>
                    <td>:</td>
                    <td>{{ $gadai->pinjaman->profile->no_identity }}</td>
                </tr>
                <tr>
                    <td>Pekerjaan</td>
                    <td>:</td>
                    <td>{{ $gadai->pinjaman->profile->job }}</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>{{ $gadai->pinjaman->profile->address }}</td>
                </tr>
                @php
                    $getRegionName = function($code) {
                        return DB::table('regions')->where('code', $code)->value('name') ?? '-';
                    };
                @endphp
                <tr>
                    <td>Provinsi</td>
                    <td>:</td>
                    <td>{{ $getRegionName($gadai->pinjaman->profile->province_id) }}</td>
                </tr>
                <tr>
                    <td>Kabupaten/Kota</td>
                    <td>:</td>
                    <td>{{ $getRegionName($gadai->pinjaman->profile->district_id) }}</td>
                </tr>
                <tr>
                    <td>Kecamatan</td>
                    <td>:</td>
                    <td>{{ $getRegionName($gadai->pinjaman->profile->city_id) }}</td>
                </tr>
                <tr>
                    <td>Desa/Kelurahan</td>
                    <td>:</td>
                    <td>{{ $getRegionName($gadai->pinjaman->profile->village_id) }}</td>
                </tr>
            </table>
            <p>Dalam hal ini bertindak untuk dan atas nama diri sendiri, selanjutnya disebut sebagai PIHAK PERTAMA.</p>
        </div>

        <div class="section-title">PIHAK KEDUA</div>
        <div class="party-info">
            <table border="0" style="width: 100%">
                <tr>
                    <td style="width: 150px">Nama</td>
                    <td style="width: 10px">:</td>
                    <td>KOPERASI SINARA ARTHA</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>Eastern Park Residence, Blok B No.7, Jl. Taman Timur Sukolilo 1, Kel keputih Kec Sukolilo, Surabaya, Jawa Timur 60111</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="article">
        <h3>Pasal 1<br>OBJEK GADAI</h3>
        <ol>
            <li>PIHAK PERTAMA dengan ini menyerahkan barang elektronik dengan spesifikasi sebagai berikut:
                <table border="0" style="width: 100%; margin-top: 10px;">
                    <tr>
                        <td style="width: 150px">Nama Barang</td>
                        <td style="width: 10px">:</td>
                        <td>{{ $gadai->nama_barang }}</td>
                    </tr>
                    <tr>
                        <td>Kode/IMEI</td>
                        <td>:</td>
                        <td>{{ $gadai->kode_barang }}</td>
                    </tr>
                    <tr>
                        <td>Jenis</td>
                        <td>:</td>
                        <td>{{ ucfirst($gadai->jenis_barang) }}</td>
                    </tr>
                    <tr>
                        <td>Merk</td>
                        <td>:</td>
                        <td>{{ ucfirst($gadai->merk) }}</td>
                    </tr>
                    <tr>
                        <td>Tipe</td>
                        <td>:</td>
                        <td>{{ ucfirst($gadai->tipe) }}</td>
                    </tr>
                    <tr>
                        <td>Tahun Pembuatan</td>
                        <td>:</td>
                        <td>{{ $gadai->tahun_pembuatan }}</td>
                    </tr>
                    <tr>
                        <td>Kondisi</td>
                        <td>:</td>
                        <td>{{ ucfirst($gadai->kondisi) }}</td>
                    </tr>
                    <tr>
                        <td>Kelengkapan</td>
                        <td>:</td>
                        <td>{{ $gadai->kelengkapan }}</td>
                    </tr>
                </table>
            </li>
            <li>Barang tersebut digadaikan sebagai jaminan atas pinjaman sebesar Rp {{ number_format($gadai->nilai_hutang, 0, ',', '.') }} ({{ ucwords(terbilang($gadai->nilai_hutang)) }} Rupiah).</li>
        </ol>
    </div>

    <div class="article">
        <h3>Pasal 2<br>NILAI GADAI</h3>
        <ol>
            <li>Nilai taksiran barang adalah sebesar Rp {{ number_format($gadai->harga_barang, 0, ',', '.') }} dengan nilai taksasi {{ $gadai->nilai_taksasi }}%.</li>
            <li>PIHAK KEDUA memberikan pinjaman kepada PIHAK PERTAMA sebesar Rp {{ number_format($gadai->nilai_hutang, 0, ',', '.') }}.</li>
            <li>Jangka waktu pinjaman adalah {{ $gadai->pinjaman->jangka_waktu }} bulan terhitung sejak tanggal {{ $gadai->pinjaman->tanggal_pinjaman->translatedFormat('d F Y') }}.</li>
        </ol>
    </div>

    <div class="article">
        <h3>Pasal 3<br>JANGKA WAKTU PEMBAYARAN</h3>
        <ol>
            <li>PIHAK PERTAMA wajib mengembalikan pinjaman dalam jangka waktu {{ $gadai->pinjaman->jangka_waktu }} bulan, terhitung sejak tanggal penandatanganan perjanjian ini.</li>
            @php
                $angsuran_pokok = $gadai->pinjaman->jumlah_pinjaman / $gadai->pinjaman->jangka_waktu;
                $bunga_per_bulan = ($gadai->pinjaman->jumlah_pinjaman * ($gadai->pinjaman->biayaBungaPinjaman->persentase_bunga/100)) / $gadai->pinjaman->jangka_waktu;
                $angsuran_per_bulan = $angsuran_pokok + $bunga_per_bulan;
            @endphp
            <li>Pembayaran dapat dilakukan secara angsuran sebesar Rp {{ number_format($angsuran_per_bulan, 0, ',', '.') }} per bulan pada tanggal {{ $gadai->pinjaman->tanggal_pinjaman->format('d') }} setiap bulan.</li>
            <li>PIHAK PERTAMA menyetujui bahwa dana sebesar Rp {{ number_format($angsuran_per_bulan, 0, ',', '.') }} (setara dengan 1 bulan angsuran) akan ditahan <b>(hold)</b> di dalam sistem KOPERASI SINARA ARTHA sebagai jaminan pembayaran angsuran.</li>
        </ol>
    </div>

    <div class="article">
        <h3>Pasal 4<br>CARA PEMBAYARAN</h3>
        <ol>
            <li>Pembayaran angsuran dilakukan melalui transfer bank ke rekening PIHAK KEDUA di Bank BCA No. Rekening : 0889333288 atau secara tunai di kantor KOPERASI SINARA ARTHA.</li>
            <li>PIHAK PERTAMA harus menyertakan bukti pembayaran setiap kali melakukan angsuran sebagai tanda bukti pelunasan sebagian.</li>
            <li>PIHAK PERTAMA wajib melakukan konfirmasi pembayaran kepada PIHAK KEDUA setiap kali melakukan pembayaran angsuran melalui media komunikasi yang telah ditentukan oleh PIHAK KEDUA.</li>
            <li>Kelalaian dalam melakukan konfirmasi pembayaran dapat mengakibatkan pembayaran dianggap belum diterima oleh PIHAK KEDUA dan dapat dikenakan sanksi keterlambatan sebagaimana diatur dalam Pasal 5 perjanjian ini.</li>
            <li>PIHAK KEDUA tidak bertanggung jawab atas segala konsekuensi yang timbul akibat kelalaian PIHAK PERTAMA dalam melakukan konfirmasi pembayaran.</li>
        </ol>
    </div>

    <div class="article">
        <h3>Pasal 5<br>DENDA KETERLAMBATAN</h3>
        <ol>
            <li>Apabila PIHAK PERTAMA terlambat melakukan pembayaran angsuran, maka akan dikenakan denda sebesar 5% dari jumlah angsuran yang terlambat untuk setiap hari keterlambatan.</li>
            <li>Denda keterlambatan ini akan ditambahkan pada total kewajiban yang harus dibayar PIHAK PERTAMA.</li>
        </ol>
    </div>

    <div class="article">
        <h3>Pasal 6<br>PELUNASAN</h3>
        <ol>
            <li>Apabila PIHAK PERTAMA ingin melakukan pelunasan dipercepat sebelum jangka waktu pinjaman berakhir maka akan dikenakan denda sebesar 5% dari sisa pokok pinjaman.</li>
            <li>Jika PIHAK PERTAMA telah selesai menjalankan kewajiban pembayaran angsuran sesuai jadwal hingga akhir masa pinjaman, maka PIHAK PERTAMA berhak mendapatkan SURAT TANDA LUNAS dari PIHAK KEDUA. Dan PIHAK KEDUA akan mengembalikan BARANG GADAI kepada PIHAK PERTAMA setelah proses admininstrasi selesai.</li>
            <li>Segala beaya yang timbul dari proses pelunasan seperti DENDA, BUNGA, dan lain sebagainya ditanggung oleh PIHAK PERTAMA.</li>
        </ol>
    </div>

    <div class="article">
        <h3>Pasal 7<br>HAK DAN KEWAJIBAN</h3>
        <ol>
            <li>PIHAK PERTAMA berkewajiban:
                <ul style="list-style-type: disc; padding-left: 20px; margin-top: 5px;">
                    <li>Menyerahkan barang dalam kondisi baik dan lengkap.</li>
                    <li>Melunasi pinjaman sesuai dengan kesepakatan.</li>
                    <li>Menerima kondisi barang apa adanya saat pengembalian setelah pelunasan.</li>
                </ul>
            </li>
            <li>PIHAK KEDUA berkewajiban:
                <ul style="list-style-type: disc; padding-left: 20px; margin-top: 5px;">
                    <li>Menjaga dan merawat barang gadai dengan baik.</li>
                    <li>Mengembalikan barang setelah pinjaman dilunasi.</li>
                    <li>Memberikan bukti pelunasan setelah pinjaman selesai.</li>
                </ul>
            </li>
        </ol>
    </div>

    <div class="article">
        <h3>Pasal 8<br>SANKSI WANPRESTASI</h3>
        <ol>
            <li>Apabila PIHAK PERTAMA lalai atau tidak memenuhi kewajibannya sebagaimana diatur dalam perjanjian ini, maka dianggap melakukan wanprestasi.</li>
            <li>Dalam hal terjadi wanprestasi, PIHAK KEDUA berhak untuk:
                <ul style="list-style-type: disc; padding-left: 20px; margin-top: 5px;">
                    <li>Menuntut pelunasan seluruh sisa pinjaman sekaligus beserta bunga dan denda yang berlaku.</li>
                    <li>Melakukan lelang atas barang gadai untuk melunasi seluruh kewajiban PIHAK PERTAMA.</li>
                </ul>
            </li>
            <li>Semua biaya yang timbul akibat tindakan hukum tersebut, termasuk biaya pengacara dan pengadilan, akan menjadi tanggung jawab PIHAK PERTAMA.</li>
        </ol>
    </div>

    <div class="article">
        <h3>Pasal 9<br>KEADAAN LUAR BIASA (FORCE MAJEURE)</h3>
        <ol>
            <li>Apabila terjadi keadaan kahar (force majeure) seperti bencana alam, kerusuhan, atau kejadian lain yang di luar kemampuan kedua belah pihak dan mengakibatkan PIHAK PERTAMA tidak dapat melaksanakan kewajibannya, maka PIHAK KEDUA akan memberikan toleransi sesuai kesepakatan bersama.</li>
            <li>PIHAK PERTAMA wajib segera memberitahukan secara tertulis kepada PIHAK KEDUA paling lambat dalam waktu 7 (tujuh) hari setelah terjadi keadaan kahar tersebut.</li>
        </ol>
    </div>

    <div class="article">
        <h3>Pasal 10<br>PENYELESAIAN PERSELISIHAN</h3>
        <ol>
            <li>Apabila terjadi perselisihan dalam pelaksanaan perjanjian ini, kedua belah pihak sepakat untuk menyelesaikannya melalui musyawarah mufakat.</li>
            <li>Jika musyawarah mufakat tidak tercapai, maka perselisihan akan diselesaikan melalui jalur hukum dan para pihak sepakat memilih domisili hukum di Pengadilan Negeri SURABAYA.</li>
        </ol>
    </div>

    <div class="article">
        <h3>Pasal 11<br>PENUTUP</h3>
        <ol>
            <li>Perjanjian ini berlaku sejak ditandatangani oleh kedua belah pihak.</li>
            <li>Segala perubahan perjanjian ini hanya sah apabila dibuat secara tertulis dan ditandatangani oleh kedua belah pihak.</li>
        </ol>
    </div>

    <p style="margin: 30px 0; text-align: justify;">
        Demikian perjanjian ini dibuat dan ditandatangani oleh kedua belah pihak dalam keadaan sadar, sehat jasmani dan rohani, tanpa ada paksaan dari pihak manapun.
    </p>

    <div class="signatures">
        <div class="sign-column">
            <p>PIHAK PERTAMA</p>
            <div class="sign-box"></div>
            <p>({{ $gadai->pinjaman->profile->first_name }} {{ $gadai->pinjaman->profile->last_name }})</p>
        </div>
        <div class="sign-column">
            <p>PIHAK KEDUA</p>
            <div class="sign-box"></div>
            <p>KOPERASI SINARA ARTHA</p>
            <p>(ANDESTA RULLY)</p>
        </div>
    </div>
</body>
</html> 