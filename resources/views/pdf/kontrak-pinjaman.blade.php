<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Surat Perjanjian Pinjaman</title>
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
        <h1>SURAT PERJANJIAN PINJAMAN UANG</h1>
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
                    <td>{{ $pinjaman->profile->first_name }} {{ $pinjaman->profile->last_name }}</td>
                </tr>
                <tr>
                    <td>NIK</td>
                    <td>:</td>
                    <td>{{ $pinjaman->profile->no_identity }}</td>
                </tr>
                <tr>
                    <td>Pekerjaan</td>
                    <td>:</td>
                    <td>{{ $pinjaman->profile->job }}</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>{{ $pinjaman->profile->address }}</td>
                </tr>
                @php
                    $getRegionName = function($code) {
                        return DB::table('regions')->where('code', $code)->value('name') ?? '-';
                    };
                @endphp
                <tr>
                    <td>Provinsi</td>
                    <td>:</td>
                    <td>{{ $getRegionName($pinjaman->profile->province_id) }}</td>
                </tr>
                <tr>
                    <td>Kabupaten/Kota</td>
                    <td>:</td>
                    <td>{{ $getRegionName($pinjaman->profile->district_id) }}</td>
                </tr>
                <tr>
                    <td>Kecamatan</td>
                    <td>:</td>
                    <td>{{ $getRegionName($pinjaman->profile->city_id) }}</td>
                </tr>
                <tr>
                    <td>Desa/Kelurahan</td>
                    <td>:</td>
                    <td>{{ $getRegionName($pinjaman->profile->village_id) }}</td>
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

    <p>Kedua belah pihak dengan ini menyatakan setuju untuk mengadakan perjanjian pinjaman uang dengan ketentuan sebagai berikut:</p>

    <div class="article">
        <h3>Pasal 1<br>OBJEK PERJANJIAN</h3>
        <ol>
            <li>PIHAK PERTAMA telah menerima uang pinjaman dari PIHAK KEDUA sebesar Rp {{ number_format($pinjaman->jumlah_pinjaman, 0, ',', '.') }} ({{ ucwords(terbilang($pinjaman->jumlah_pinjaman)) }} Rupiah) dengan tenor: {{ $pinjaman->jangka_waktu }} {{ $pinjaman->jangka_waktu_satuan }}.</li>
            <li>Pinjaman ini diberikan oleh PIHAK KEDUA kepada PIHAK PERTAMA dengan ketentuan sebagaimana diatur dalam pasal-pasal berikutnya dalam perjanjian ini.</li>
            {{-- <li>Bunga pinjaman yang dikenakan adalah sebesar {{ $pinjaman->biayaBungaPinjaman->persentase_bunga }}% per tahun atau {{ number_format($pinjaman->biayaBungaPinjaman->persentase_bunga/12, 2) }}% per bulan.</li> --}}
        </ol>
    </div>

    <div class="article">
        <h3>Pasal 2<br>JANGKA WAKTU PEMBAYARAN</h3>
        <ol>
            <li>PIHAK PERTAMA wajib mengembalikan pinjaman dalam jangka waktu {{ $pinjaman->jangka_waktu }} {{ $pinjaman->jangka_waktu_satuan }}, terhitung sejak tanggal penandatanganan perjanjian ini.</li>
            @php
                if ($pinjaman->jangka_waktu_satuan == 'minggu') {
                    // Pendekatan baru berdasarkan contoh referensi
                    // Menghitung persentase bunga per bulan dari persentase bunga tahunan
                    $bunga_per_bulan_persen = $pinjaman->biayaBungaPinjaman->persentase_bunga / 12;

                    // Periode pinjaman dalam bulan
                    $periode_bulan = $pinjaman->jangka_waktu / 4; // konversi minggu ke bulan (1 bulan = 4 minggu)

                    // Total bunga dalam bulan
                    $total_bunga_bulanan = $pinjaman->jumlah_pinjaman * ($bunga_per_bulan_persen/100) * $periode_bulan;

                    // Total bunga pada pinjaman
                    $total_bunga = $total_bunga_bulanan;

                    // Angsuran pokok per minggu
                    $angsuran_pokok = $pinjaman->jumlah_pinjaman / $pinjaman->jangka_waktu;

                    // Bunga per minggu
                    $bunga_per_minggu = $total_bunga / $pinjaman->jangka_waktu;

                    // Total angsuran per minggu (pokok + bunga)
                    $angsuran_per_bulan = $angsuran_pokok + $bunga_per_minggu;
                } else {
                    $angsuran_pokok = $pinjaman->jumlah_pinjaman / $pinjaman->jangka_waktu;
                    $bunga_per_bulan = ($pinjaman->jumlah_pinjaman * ($pinjaman->biayaBungaPinjaman->persentase_bunga/100)) / $pinjaman->jangka_waktu;
                    $angsuran_per_bulan = $angsuran_pokok + $bunga_per_bulan;
                }
            @endphp
            <li>Pembayaran dapat dilakukan secara angsuran sebesar Rp {{ number_format($angsuran_per_bulan, 0, ',', '.') }} per {{ $pinjaman->jangka_waktu_satuan }}
                @if($pinjaman->jangka_waktu_satuan !== 'minggu')
                pada tanggal {{ $pinjaman->tanggal_pinjaman->format('d') }}
                @endif
                setiap {{ $pinjaman->jangka_waktu_satuan }}.</li>
            <li>PIHAK PERTAMA menyetujui bahwa dana sebesar Rp {{ number_format($angsuran_per_bulan, 0, ',', '.') }} (setara dengan 1 {{ $pinjaman->jangka_waktu_satuan }} angsuran) akan ditahan <b>(hold)</b> di dalam sistem KOPERASI SINARA ARTHA sebagai jaminan pembayaran angsuran ( kecuali <b>PINJAMAN INSTANT</b>).</li>

        </ol>
    </div>

    <div class="article">
        <h3>Pasal 3<br>CARA PEMBAYARAN</h3>
        <ol>
            <li>Pembayaran angsuran dilakukan melalui transfer bank ke rekening PIHAK KEDUA di Bank BCA No. Rekening : 0889333288 atau secara tunai di kantor KOPERASI SINARA ARTHA.</li>
            <li>PIHAK PERTAMA harus menyertakan bukti pembayaran setiap kali melakukan angsuran sebagai tanda bukti pelunasan sebagian.</li>
            <li>PIHAK PERTAMA wajib melakukan konfirmasi pembayaran kepada PIHAK KEDUA setiap kali melakukan pembayaran angsuran melalui media komunikasi yang telah ditentukan oleh PIHAK KEDUA.</li>
            <li>Kelalaian dalam melakukan konfirmasi pembayaran dapat mengakibatkan pembayaran dianggap belum diterima oleh PIHAK KEDUA dan dapat dikenakan sanksi keterlambatan sebagaimana diatur dalam Pasal 4 perjanjian ini.</li>
            <li>PIHAK KEDUA tidak bertanggung jawab atas segala konsekuensi yang timbul akibat kelalaian PIHAK PERTAMA dalam melakukan konfirmasi pembayaran.</li>
        </ol>
    </div>

    <div class="article">
        <h3>Pasal 4<br>DENDA KETERLAMBATAN</h3>
        <ol>
            <li>Apabila PIHAK PERTAMA terlambat melakukan pembayaran angsuran, maka akan dikenakan denda sebesar 5% dari jumlah angsuran yang terlambat untuk setiap hari keterlambatan.</li>
            <li>Denda keterlambatan ini akan ditambahkan pada total kewajiban yang harus dibayar PIHAK PERTAMA.</li>
        </ol>
    </div>

    <div class="article">
        <h3>Pasal 5<br>PELUNASAN</h3>
        <ol>
            <li>Apabila PIHAK PERTAMA ingin melakukan pelunasan dipercepat sebelum jangka waktu pinjaman berakhir maka akan dikenakan denda sebesar 5% dari sisa pokok pinjaman.</li>
            <li>Jika PIHAK PERTAMA telah selesai menjalankan kewajiban pembayaran angsuran sesuai jadwal hingga akhir masa pinjaman, maka PIHAK PERTAMA berhak mendapatkan SURAT TANDA LUNAS dari PIHAK KEDUA. Dan PIHAK KEDUA akan mengembalikan JAMINAN (jika ada) kepada PIHAK PERTAMA setelah proses admininstrasi selesai.</li>
            <li>Segala beaya yang timbul dari proses pelunasan seperti DENDA, BUNGA, dan lain sebagainya ditanggung oleh PIHAK PERTAMA.</li>
        </ol>
    </div>

    <div class="article">
        <h3>Pasal 6<br>SANKSI WANPRESTASI</h3>
        <ol>
            <li>Apabila PIHAK PERTAMA lalai atau tidak memenuhi kewajibannya sebagaimana diatur dalam perjanjian ini, maka dianggap melakukan wanprestasi.</li>
            <li>Dalam hal terjadi wanprestasi, PIHAK KEDUA berhak untuk:
                <ul style="list-style-type: disc; padding-left: 20px; margin-top: 5px;">
                    <li>Menuntut pelunasan seluruh sisa pinjaman sekaligus beserta bunga dan denda yang berlaku.</li>
                    <li>Melakukan upaya hukum untuk menagih sisa pinjaman, termasuk namun tidak terbatas pada penyitaan aset PIHAK PERTAMA yang telah dijaminkan (jika ada).</li>
                </ul>
            </li>
            <li>Semua biaya yang timbul akibat tindakan hukum tersebut, termasuk biaya pengacara dan pengadilan, akan menjadi tanggung jawab PIHAK PERTAMA.</li>
        </ol>
    </div>

    <div class="article">
        <h3>Pasal 7<br>KEADAAN LUAR BIASA (FORCE MAJEURE)</h3>
        <ol>
            <li>Apabila terjadi keadaan kahar (force majeure) seperti bencana alam, kerusuhan, atau kejadian lain yang di luar kemampuan kedua belah pihak dan mengakibatkan PIHAK PERTAMA tidak dapat melaksanakan kewajibannya, maka PIHAK KEDUA akan memberikan toleransi sesuai kesepakatan bersama.</li>
            <li>PIHAK PERTAMA wajib segera memberitahukan secara tertulis kepada PIHAK KEDUA paling lambat dalam waktu 7 (tujuh) hari setelah terjadi keadaan kahar tersebut.</li>
        </ol>
    </div>

    <div class="article">
        <h3>Pasal 8<br>PENYELESAIAN PERSELISIHAN</h3>
        <ol>
            <li>Apabila terjadi perselisihan dalam pelaksanaan perjanjian ini, kedua belah pihak sepakat untuk menyelesaikannya melalui musyawarah mufakat.</li>
            <li>Jika musyawarah mufakat tidak tercapai, maka perselisihan akan diselesaikan melalui jalur hukum dan para pihak sepakat memilih domisili hukum di Pengadilan Negeri SURABAYA.</li>
        </ol>
    </div>

    <div class="article">
        <h3>Pasal 9<br>PENUTUP</h3>
        <ol>
            <li>Perjanjian ini berlaku sejak ditandatangani oleh kedua belah pihak.</li>
            <li>Segala perubahan perjanjian ini hanya sah apabila dibuat secara tertulis dan ditandatangani oleh kedua belah pihak.</li>
        </ol>
    </div>

    <p style="margin: 30px 0; text-align: justify;">
        Demikian perjanjian ini dibuat dan ditandatangani oleh kedua belah pihak dalam keadaan sadar, tanpa ada paksaan dari pihak manapun.
    </p>

    <div class="signatures">
        <div class="sign-column">
            <p>PIHAK PERTAMA</p>
            <div class="sign-box"></div>
            <p>({{ $pinjaman->profile->first_name }} {{ $pinjaman->profile->last_name }})</p>
        </div>
        <div class="sign-column">
            <p>PIHAK KEDUA</p>
            <div class="sign-box"></div>
            <p>KOPERASI SINARA ARTHA</p>
            <p>(ANDREAS WIDEA)</p>
        </div>
    </div>
</body>
</html>
