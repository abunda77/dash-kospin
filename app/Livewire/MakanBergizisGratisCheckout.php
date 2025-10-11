<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use App\Models\MakanBergizisGratis;
use App\Helpers\HashidsHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class MakanBergizisGratisCheckout extends Component
{
    public $hash = null;
    public $noTabungan = '';
    public $tabunganData = null;
    public $loading = false;
    public $error = null;
    public $success = null;
    public $checkoutLoading = false;
    public $alreadyCheckedOut = false;

    protected $rules = [
        'noTabungan' => 'required|string',
    ];

    public function mount($hash = null)
    {
        $this->hash = $hash;
        
        // Auto load jika ada hash dari QR scan
        if ($this->hash) {
            $this->loadFromHash();
        }
    }

    public function loadFromHash()
    {
        $this->loading = true;
        $this->error = null;
        $this->success = null;
        $this->tabunganData = null;

        try {
            // Decode hash
            $id = HashidsHelper::decode($this->hash);

            if ($id === null) {
                $this->error = 'QR Code tidak valid atau sudah kadaluarsa';
                $this->loading = false;
                return;
            }

            // Load tabungan data
            $tabungan = Tabungan::with(['profile', 'produkTabungan'])->find($id);

            if (!$tabungan) {
                $this->error = 'Data tabungan tidak ditemukan';
                $this->loading = false;
                return;
            }

            $this->noTabungan = $tabungan->no_tabungan;
            $this->loadTabunganData($tabungan);

        } catch (\Exception $e) {
            Log::error('Error loading from hash', [
                'hash' => $this->hash,
                'error' => $e->getMessage()
            ]);
            $this->error = 'Terjadi kesalahan saat memuat data';
        }

        $this->loading = false;
    }

    public function searchTabungan()
    {
        $this->validate();

        $this->loading = true;
        $this->error = null;
        $this->success = null;
        $this->tabunganData = null;

        try {
            $tabungan = Tabungan::with(['profile', 'produkTabungan'])
                ->where('no_tabungan', $this->noTabungan)
                ->first();

            if (!$tabungan) {
                $this->error = 'Nomor tabungan tidak ditemukan';
                $this->loading = false;
                return;
            }

            $this->loadTabunganData($tabungan);

        } catch (\Exception $e) {
            Log::error('Error searching tabungan', [
                'no_tabungan' => $this->noTabungan,
                'error' => $e->getMessage()
            ]);
            $this->error = 'Terjadi kesalahan saat mencari data';
        }

        $this->loading = false;
    }

    private function loadTabunganData($tabungan)
    {
        // Get last transaction
        $transaksiTerakhir = TransaksiTabungan::where('id_tabungan', $tabungan->id)
            ->with('admin')
            ->latest('tanggal_transaksi')
            ->first();

        // Check if already checked out today
        $this->alreadyCheckedOut = MakanBergizisGratis::existsForToday($tabungan->no_tabungan);

        // Calculate account age
        $accountAge = $this->calculateAccountAge($tabungan->tanggal_buka_rekening);

        $this->tabunganData = [
            'id' => $tabungan->id,
            'rekening' => [
                'no_tabungan' => $tabungan->no_tabungan,
                'produk' => $tabungan->produkTabungan->nama_produk ?? 'N/A',
                'saldo' => $tabungan->saldo,
                'saldo_formatted' => format_rupiah($tabungan->saldo),
                'status' => $tabungan->status_rekening,
                'tanggal_buka' => $tabungan->tanggal_buka_rekening?->format('d/m/Y'),
                'usia_rekening' => $accountAge,
            ],
            'nasabah' => [
                'nama_lengkap' => $tabungan->profile->first_name . ' ' . $tabungan->profile->last_name,
                'first_name' => $tabungan->profile->first_name,
                'last_name' => $tabungan->profile->last_name,
                'phone' => $tabungan->profile->phone,
                'email' => $tabungan->profile->email,
                'whatsapp' => $tabungan->profile->whatsapp,
                'address' => $tabungan->profile->address,
            ],
            'produk_detail' => [
                'id' => $tabungan->produkTabungan->id ?? null,
                'nama' => $tabungan->produkTabungan->nama_produk ?? 'N/A',
                'keterangan' => $tabungan->produkTabungan->keterangan ?? null,
            ],
            'transaksi_terakhir' => $transaksiTerakhir ? [
                'kode_transaksi' => $transaksiTerakhir->kode_transaksi,
                'jenis_transaksi' => $transaksiTerakhir->jenis_transaksi,
                'jenis_transaksi_label' => $transaksiTerakhir->jenis_transaksi === TransaksiTabungan::JENIS_SETORAN ? 'Setoran' : 'Penarikan',
                'jumlah' => $transaksiTerakhir->jumlah,
                'jumlah_formatted' => format_rupiah($transaksiTerakhir->jumlah),
                'tanggal_transaksi' => $transaksiTerakhir->tanggal_transaksi->format('d/m/Y H:i:s'),
                'keterangan' => $transaksiTerakhir->keterangan,
                'teller' => $transaksiTerakhir->admin?->name ?? 'N/A',
                'mbg_eligibility' => $this->checkMbgEligibility($transaksiTerakhir),
            ] : null,
        ];
    }

    public function checkout()
    {
        if (!$this->tabunganData) {
            $this->error = 'Data tabungan tidak tersedia';
            return;
        }

        if ($this->alreadyCheckedOut) {
            $this->error = 'Nomor tabungan ini sudah melakukan checkout hari ini';
            return;
        }

        $this->checkoutLoading = true;
        $this->error = null;
        $this->success = null;

        try {
            $tabungan = Tabungan::with(['profile', 'produkTabungan'])
                ->where('no_tabungan', $this->noTabungan)
                ->firstOrFail();

            $transaksiTerakhir = TransaksiTabungan::where('id_tabungan', $tabungan->id)
                ->with('admin')
                ->latest('tanggal_transaksi')
                ->first();

            // Prepare data structures
            $dataRekening = [
                'no_tabungan' => $tabungan->no_tabungan,
                'produk' => $tabungan->produkTabungan->nama_produk ?? 'N/A',
                'saldo' => $tabungan->saldo,
                'saldo_formatted' => format_rupiah($tabungan->saldo),
                'status' => $tabungan->status_rekening,
                'tanggal_buka' => $tabungan->tanggal_buka_rekening?->format('d/m/Y'),
                'tanggal_buka_iso' => $tabungan->tanggal_buka_rekening?->toISOString(),
            ];

            $dataNasabah = [
                'nama_lengkap' => $tabungan->profile->first_name . ' ' . $tabungan->profile->last_name,
                'first_name' => $tabungan->profile->first_name,
                'last_name' => $tabungan->profile->last_name,
                'phone' => $tabungan->profile->phone,
                'email' => $tabungan->profile->email,
                'whatsapp' => $tabungan->profile->whatsapp,
                'address' => $tabungan->profile->address,
            ];

            $dataProduk = [
                'id' => $tabungan->produkTabungan->id ?? null,
                'nama' => $tabungan->produkTabungan->nama_produk ?? 'N/A',
                'keterangan' => $tabungan->produkTabungan->keterangan ?? null,
            ];

            $dataTransaksiTerakhir = $transaksiTerakhir ? [
                'kode_transaksi' => $transaksiTerakhir->kode_transaksi,
                'jenis_transaksi' => $transaksiTerakhir->jenis_transaksi,
                'jenis_transaksi_label' => $transaksiTerakhir->jenis_transaksi === TransaksiTabungan::JENIS_SETORAN ? 'Setoran' : 'Penarikan',
                'jumlah' => $transaksiTerakhir->jumlah,
                'jumlah_formatted' => format_rupiah($transaksiTerakhir->jumlah),
                'tanggal_transaksi' => $transaksiTerakhir->tanggal_transaksi->format('d/m/Y H:i:s'),
                'tanggal_transaksi_iso' => $transaksiTerakhir->tanggal_transaksi->toISOString(),
                'keterangan' => $transaksiTerakhir->keterangan,
                'teller' => $transaksiTerakhir->admin?->name ?? 'N/A',
            ] : null;

            // Create record
            $record = MakanBergizisGratis::create([
                'tabungan_id' => $tabungan->id,
                'profile_id' => $tabungan->profile->id_user,
                'no_tabungan' => $tabungan->no_tabungan,
                'tanggal_pemberian' => today(),
                'data_rekening' => $dataRekening,
                'data_nasabah' => $dataNasabah,
                'data_produk' => $dataProduk,
                'data_transaksi_terakhir' => $dataTransaksiTerakhir,
                'scanned_at' => now(),
            ]);

            $this->success = 'Checkout berhasil! Data telah tersimpan.';
            $this->alreadyCheckedOut = true;

            Log::info('Makan Bergizi Gratis checkout success', [
                'record_id' => $record->id,
                'no_tabungan' => $this->noTabungan,
                'tanggal' => today()->format('Y-m-d')
            ]);

            // Send webhook notification
            $this->sendWebhookNotification($record, $dataRekening, $dataNasabah, $dataProduk, $dataTransaksiTerakhir);

        } catch (\Exception $e) {
            Log::error('Error during checkout', [
                'no_tabungan' => $this->noTabungan,
                'error' => $e->getMessage()
            ]);
            $this->error = 'Terjadi kesalahan saat checkout: ' . $e->getMessage();
        }

        $this->checkoutLoading = false;
    }

    private function sendWebhookNotification($record, $dataRekening, $dataNasabah, $dataProduk, $dataTransaksiTerakhir)
    {
        $webhookUrl = config('services.webhook.barcode_tabungan_url');

        if (empty($webhookUrl)) {
            Log::info('Webhook URL not configured, skipping webhook notification');
            return;
        }

        try {
            $payload = [
                'event' => 'makan_bergizi_gratis.checkout',
                'timestamp' => now()->toISOString(),
                'data' => [
                    'id' => $record->id,
                    'no_tabungan' => $record->no_tabungan,
                    'tanggal_pemberian' => $record->tanggal_pemberian->format('Y-m-d'),
                    'scanned_at' => $record->scanned_at->toISOString(),
                    'rekening' => $dataRekening,
                    'nasabah' => $dataNasabah,
                    'produk' => $dataProduk,
                    'transaksi_terakhir' => $dataTransaksiTerakhir,
                ]
            ];

            $response = Http::timeout(10)
                ->retry(2, 100)
                ->post($webhookUrl, $payload);

            if ($response->successful()) {
                Log::info('Webhook notification sent successfully', [
                    'record_id' => $record->id,
                    'webhook_url' => $webhookUrl,
                    'status_code' => $response->status()
                ]);
            } else {
                Log::warning('Webhook notification failed', [
                    'record_id' => $record->id,
                    'webhook_url' => $webhookUrl,
                    'status_code' => $response->status(),
                    'response_body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error sending webhook notification', [
                'record_id' => $record->id,
                'webhook_url' => $webhookUrl,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function calculateAccountAge($tanggalBuka)
    {
        if (!$tanggalBuka) {
            return null;
        }

        $now = now();
        $diff = $tanggalBuka->diff($now);

        $months = ($diff->y * 12) + $diff->m;
        $days = $diff->d;

        // Determine contract status (3 months = contract period)
        $isInContract = $months < 3;
        $contractStatus = $isInContract ? 'Masih dalam kontrak' : 'Lewat masa kontrak';

        return [
            'months' => $months,
            'days' => $days,
            'formatted' => $months > 0 
                ? ($days > 0 ? "{$months} bulan {$days} hari" : "{$months} bulan")
                : "{$days} hari",
            'is_in_contract' => $isInContract,
            'contract_status' => $contractStatus,
        ];
    }

    private function checkMbgEligibility($transaksiTerakhir)
    {
        if (!$transaksiTerakhir) {
            return [
                'eligible' => false,
                'status' => 'Tidak ada transaksi',
                'days_ago' => null,
            ];
        }

        $now = now();
        $transactionDate = $transaksiTerakhir->tanggal_transaksi;
        $daysAgo = $transactionDate->diffInDays($now);

        // Check if last transaction is setoran (deposit) and within 7 days
        $isSetoran = $transaksiTerakhir->jenis_transaksi === TransaksiTabungan::JENIS_SETORAN;
        $isWithin7Days = $daysAgo <= 7;
        $eligible = $isSetoran && $isWithin7Days;

        $status = $eligible 
            ? 'Berhak mendapatkan MBG' 
            : 'Tidak berhak MBG / Silakan lakukan setoran mingguan';

        return [
            'eligible' => $eligible,
            'status' => $status,
            'days_ago' => $daysAgo,
            'is_setoran' => $isSetoran,
        ];
    }

    public function resetForm()
    {
        $this->noTabungan = '';
        $this->tabunganData = null;
        $this->error = null;
        $this->success = null;
        $this->alreadyCheckedOut = false;
    }

    public function render()
    {
        return view('livewire.makan-bergizis-gratis-checkout');
    }
}
