<?php

namespace App\Http\Controllers\Api;

use App\Enums\MetodePembayaranSetoran;
use App\Enums\StatusSetoran;
use App\Http\Controllers\Controller;
use App\Http\Requests\KlaimSetoranRequest;
use App\Http\Requests\StoreSetoranRequest;
use App\Models\SetoranTabungan;
use App\Models\Tabungan;
use App\Models\User;
use App\Services\BatalkanSetoranService;
use App\Services\BuatSetoranTabunganService;
use App\Services\KirimKlaimPembayaranService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class SetoranSimpananController extends Controller
{
    public function rekeningOptions(Request $request): JsonResponse
    {
        $user = $request->user();

        $options = Tabungan::query()
            ->whereHas('profile', fn ($query) => $query
                ->where('id_user', $user->getKey())
                ->where('is_active', true))
            ->where('status_rekening', 'aktif')
            ->with('produkTabungan')
            ->get()
            ->map(fn (Tabungan $tabungan) => [
                'id' => $tabungan->id,
                'no_tabungan' => $tabungan->no_tabungan,
                'nama_produk' => $tabungan->produkTabungan?->nama_produk ?? 'Tabungan',
                'saldo_akhir' => (int) floor($tabungan->saldo_akhir),
            ])
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Data rekening tabungan berhasil diambil',
            'data' => $options,
        ], 200);
    }

    public function store(StoreSetoranRequest $request): JsonResponse
    {
        $user = $request->user();

        $tabungan = $this->resolveTabunganMilikUser($user, (int) $request->validated('id_tabungan'));

        if (! $tabungan) {
            return response()->json([
                'status' => false,
                'message' => 'Rekening tidak valid atau tidak aktif.',
            ], 422);
        }

        try {
            $setoran = app(BuatSetoranTabunganService::class)->execute(
                $user,
                $tabungan,
                (int) $request->validated('jumlah'),
                MetodePembayaranSetoran::from($request->validated('metode_pembayaran', MetodePembayaranSetoran::Qris->value))
            );

            return response()->json([
                'status' => true,
                'message' => 'Instruksi pembayaran berhasil dibuat.',
                'data' => $this->mapSetoran($setoran),
            ], 201);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            Log::error('Error in SetoranSimpananController@store: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan pada sistem. Silakan hubungi admin.',
            ], 500);
        }
    }

    public function aktif(Request $request): JsonResponse
    {
        $setoran = SetoranTabungan::where('user_id', $request->user()->id)
            ->whereIn('status', [
                StatusSetoran::MENUNGGU_PEMBAYARAN,
                StatusSetoran::MENUNGGU_VERIFIKASI,
                StatusSetoran::SEDANG_DIPERIKSA,
                StatusSetoran::PERLU_REVISI,
                StatusSetoran::DISETUJUI,
            ])
            ->with('tabungan')
            ->first();

        return response()->json([
            'status' => true,
            'message' => 'Data setoran aktif berhasil diambil',
            'data' => $setoran ? $this->mapSetoran($setoran) : null,
        ], 200);
    }

    public function history(Request $request): JsonResponse
    {
        $setoranList = SetoranTabungan::where('user_id', $request->user()->id)
            ->with('tabungan')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Riwayat setoran berhasil diambil',
            'data' => $setoranList->map(fn (SetoranTabungan $setoran) => $this->mapSetoran($setoran))->values(),
        ], 200);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $setoran = SetoranTabungan::where('user_id', $request->user()->id)
            ->with('tabungan')
            ->find($id);

        if (! $setoran) {
            return response()->json([
                'status' => false,
                'message' => 'Data setoran tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data setoran berhasil diambil',
            'data' => $this->mapSetoran($setoran),
        ], 200);
    }

    public function klaimPembayaran(KlaimSetoranRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        $setoran = SetoranTabungan::where('user_id', $user->id)->find($id);

        if (! $setoran) {
            return response()->json([
                'status' => false,
                'message' => 'Data setoran tidak ditemukan',
            ], 404);
        }

        try {
            $setoran = app(KirimKlaimPembayaranService::class)->execute(
                $user,
                $setoran,
                Carbon::parse($request->validated('waktu_klaim_bayar')),
                $request->validated('nama_pembayar'),
                $request->validated('referensi_pembayaran'),
                $request->validated('catatan_pengguna'),
                $request->file('bukti_pembayaran')
            );

            return response()->json([
                'status' => true,
                'message' => 'Konfirmasi pembayaran berhasil dikirim.',
                'data' => $this->mapSetoran($setoran),
            ], 200);
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            Log::error('Error in SetoranSimpananController@klaimPembayaran: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Gagal memverifikasi bukti. Silakan periksa kembali berkas Anda.',
            ], 500);
        }
    }

    public function batalkan(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $setoran = SetoranTabungan::where('user_id', $user->id)->find($id);

        if (! $setoran) {
            return response()->json([
                'status' => false,
                'message' => 'Data setoran tidak ditemukan',
            ], 404);
        }

        try {
            $this->authorize('batalkan', $setoran);
            $setoran = app(BatalkanSetoranService::class)->execute($user, $setoran);

            return response()->json([
                'status' => true,
                'message' => 'Setoran berhasil dibatalkan.',
                'data' => $this->mapSetoran($setoran),
            ]);
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (RuntimeException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            Log::error('Error in SetoranSimpananController@batalkan: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Setoran gagal dibatalkan. Silakan coba kembali.',
            ], 500);
        }
    }

    private function resolveTabunganMilikUser(User $user, int $idTabungan): ?Tabungan
    {
        return Tabungan::query()
            ->whereKey($idTabungan)
            ->where('status_rekening', 'aktif')
            ->whereHas('profile', fn ($query) => $query
                ->where('id_user', $user->getKey())
                ->where('is_active', true))
            ->first();
    }

    private function mapSetoran(SetoranTabungan $setoran): array
    {
        $setoran->loadMissing('tabungan');

        return [
            'id' => $setoran->id,
            'nomor_setoran' => $setoran->nomor_setoran,
            'jenis_simpanan' => $setoran->jenis_simpanan,
            'jumlah' => $setoran->jumlah,
            'kode_unik' => $setoran->kode_unik,
            'jumlah_bayar' => $setoran->jumlah_bayar,
            'metode_pembayaran' => $setoran->metode_pembayaran->value,
            'metode_pembayaran_label' => $setoran->metode_pembayaran->label(),
            'rekening_transfer' => $setoran->metode_pembayaran === MetodePembayaranSetoran::TransferRekening
                ? config('setoran.rekening_transfer')
                : null,
            'qris_payload' => $setoran->qris_payload,
            'qris_image_url' => $setoran->qris_image_path ? Storage::disk('public')->url($setoran->qris_image_path) : null,
            'qris_dibuat_at' => $setoran->qris_dibuat_at?->toIso8601String(),
            'kedaluwarsa_at' => $setoran->kedaluwarsa_at?->toIso8601String(),
            'status' => $setoran->status?->value,
            'status_label' => str_replace('_', ' ', strtoupper($setoran->status?->value ?? '')),
            'waktu_klaim_bayar' => $setoran->waktu_klaim_bayar?->toIso8601String(),
            'nama_pembayar' => $setoran->nama_pembayar,
            'referensi_pembayaran' => $setoran->referensi_pembayaran,
            'catatan_pengguna' => $setoran->catatan_pengguna,
            'catatan_verifikasi' => $setoran->catatan_verifikasi,
            'alasan_penolakan' => $setoran->alasan_penolakan,
            'dikirim_at' => $setoran->dikirim_at?->toIso8601String(),
            'disetujui_at' => $setoran->disetujui_at?->toIso8601String(),
            'ditolak_at' => $setoran->ditolak_at?->toIso8601String(),
            'selesai_at' => $setoran->selesai_at?->toIso8601String(),
            'no_tabungan' => $setoran->tabungan?->no_tabungan,
            'created_at' => $setoran->created_at?->toIso8601String(),
        ];
    }
}
