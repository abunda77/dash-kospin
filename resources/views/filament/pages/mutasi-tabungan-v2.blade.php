@php
    use App\Models\TransaksiTabungan;
@endphp

<x-filament-panels::page>
    {{ $this->form }}

    @if($isSearchSubmitted)
        <div class="mt-4">
            <div class="mb-4">
                @if($tabungan)
                    <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Nama</p>
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    {{ $tabungan->profile?->first_name }} {{ $tabungan->profile?->last_name }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Nomor Rekening</p>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $tabungan->no_tabungan }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Jenis Tabungan</p>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $tabungan->produkTabungan?->nama_produk }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Saldo Awal</p>
                                {{-- <p class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($saldo_berjalan, 0, ',', '.') }}</p> --}}
                                <p class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($tabungan->saldo ?? 0, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex justify-end gap-4 mt-4 mb-4 space-x-2">
                <x-filament::button wire:click="print" color="success">
                    Cetak Buku
                </x-filament::button>
                <x-filament::button wire:click="printTable" color="warning">
                    Cetak Tabel
                </x-filament::button>
            </div>

            {{ $this->table }}

            @php
                $query = TransaksiTabungan::where('id_tabungan', $tabungan->id);

                if ($this->periode && $this->periode !== 'all') {
                    $startDate = $this->calculateStartDate();
                    if ($startDate) {
                        $query->where('tanggal_transaksi', '>=', $startDate);
                    }
                }

                $totalDebit = (clone $query)
                    ->where('jenis_transaksi', 'debit')
                    ->sum('jumlah');

                $totalKredit = (clone $query)
                    ->where('jenis_transaksi', 'kredit')
                    ->sum('jumlah');

                $saldoAkhir = $this->calculateFinalBalance();
            @endphp

            <div class="p-4 mt-4 bg-white rounded-lg shadow dark:bg-gray-800">
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Debit</p>
                        <p class="font-semibold text-gray-900 dark:text-white">
                            Rp {{ number_format($totalDebit, 0, ',', '.') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Kredit</p>
                        <p class="font-semibold text-gray-900 dark:text-white">
                            Rp {{ number_format($totalKredit, 0, ',', '.') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Saldo Akhir</p>
                        <p class="font-semibold text-gray-900 dark:text-white">
                            Rp {{ number_format($saldoAkhir, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

            </div>
            <div class="p-4 mt-8 bg-white rounded-lg shadow dark:bg-gray-900">
                <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">Keterangan Kode Transaksi:</p>
                <div class="grid grid-cols-2 gap-2 text-sm dark:text-gray-400">
                    <div>
                        <p>000 - DB Bunga Deposito</p>
                        <p>001 - Penyetoran</p>
                        <p>002 - Penarikan di Teller</p>
                        <p>003 - Pengambilan di ATM</p>
                        <p>004 - Pemindahbukuan (DK), Biaya Adm</p>
                    </div>
                    <div>
                        <p>005 - Setoran/Tolakan Kliring</p>
                        <p>006 - Bunga</p>
                        <p>00K - Koreksi</p>
                        <p>00S - Saldo Penutupan</p>
                        <p>00P - Pajak</p>
                        <p>00G - Gabungan Transaksi</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
