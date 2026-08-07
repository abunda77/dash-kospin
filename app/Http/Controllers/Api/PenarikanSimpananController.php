<?php

namespace App\Http\Controllers\Api;

use App\Enums\StatusPenarikan;
use App\Http\Controllers\Controller;
use App\Http\Requests\KirimRevisiPenarikanRequest;
use App\Http\Requests\StorePenarikanRequest;
use App\Models\PenarikanTabungan;
use App\Models\Tabungan;
use App\Models\User;
use App\Services\BatalkanPenarikanService;
use App\Services\BuatPenarikanTabunganService;
use App\Services\KirimRevisiPenarikanService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class PenarikanSimpananController extends Controller
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

    public function store(StorePenarikanRequest $request): JsonResponse
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
            $penarikan = app(BuatPenarikanTabunganService::class)->execute(
                $user,
                $tabungan,
                (int) $request->validated('jumlah'),
                $request->validated('bank'),
                $request->validated('nama_bank'),
                $request->validated('nama_nasabah'),
                $request->validated('referensi_penarikan'),
                $request->validated('catatan_pengguna'),
                $request->file('bukti_penarikan')
            );

            return response()->json([
                'status' => true,
                'message' => 'Permohonan penarikan berhasil diajukan dan menunggu verifikasi.',
                'data' => $this->mapPenarikan($penarikan),
            ], 201);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            Log::error('Error in PenarikanSimpananController@store: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan pada sistem. Silakan hubungi admin.',
            ], 500);
        }
    }

    public function aktif(Request $request): JsonResponse
    {
        $penarikan = PenarikanTabungan::where('user_id', $request->user()->id)
            ->whereIn('status', [
                StatusPenarikan::MENUNGGU_VERIFIKASI,
                StatusPenarikan::SEDANG_DIPERIKSA,
                StatusPenarikan::PERLU_REVISI,
                StatusPenarikan::DISETUJUI,
            ])
            ->with('tabungan')
            ->first();

        return response()->json([
            'status' => true,
            'message' => 'Data penarikan aktif berhasil diambil',
            'data' => $penarikan ? $this->mapPenarikan($penarikan) : null,
        ], 200);
    }

    public function history(Request $request): JsonResponse
    {
        $penarikanList = PenarikanTabungan::where('user_id', $request->user()->id)
            ->with('tabungan')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Riwayat penarikan berhasil diambil',
            'data' => $penarikanList->map(fn (PenarikanTabungan $penarikan) => $this->mapPenarikan($penarikan))->values(),
        ], 200);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $penarikan = PenarikanTabungan::where('user_id', $request->user()->id)
            ->with('tabungan')
            ->find($id);

        if (! $penarikan) {
            return response()->json([
                'status' => false,
                'message' => 'Data penarikan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data penarikan berhasil diambil',
            'data' => $this->mapPenarikan($penarikan),
        ], 200);
    }

    public function kirimRevisi(KirimRevisiPenarikanRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        $penarikan = PenarikanTabungan::where('user_id', $user->id)->find($id);

        if (! $penarikan) {
            return response()->json([
                'status' => false,
                'message' => 'Data penarikan tidak ditemukan',
            ], 404);
        }

        try {
            $penarikan = app(KirimRevisiPenarikanService::class)->execute(
                $user,
                $penarikan,
                $request->validated('referensi_penarikan'),
                $request->validated('catatan_pengguna'),
                $request->file('bukti_penarikan')
            );

            return response()->json([
                'status' => true,
                'message' => 'Revisi penarikan berhasil dikirim.',
                'data' => $this->mapPenarikan($penarikan),
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
            Log::error('Error in PenarikanSimpananController@kirimRevisi: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Gagal memverifikasi berkas. Silakan periksa kembali berkas Anda.',
            ], 500);
        }
    }

    public function batalkan(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $penarikan = PenarikanTabungan::where('user_id', $user->id)->find($id);

        if (! $penarikan) {
            return response()->json([
                'status' => false,
                'message' => 'Data penarikan tidak ditemukan',
            ], 404);
        }

        try {
            $this->authorize('batalkan', $penarikan);
            $penarikan = app(BatalkanPenarikanService::class)->execute($user, $penarikan);

            return response()->json([
                'status' => true,
                'message' => 'Penarikan berhasil dibatalkan.',
                'data' => $this->mapPenarikan($penarikan),
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
            Log::error('Error in PenarikanSimpananController@batalkan: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Penarikan gagal dibatalkan. Silakan coba kembali.',
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

    private function mapPenarikan(PenarikanTabungan $penarikan): array
    {
        $penarikan->loadMissing('tabungan');

        return [
            'id' => $penarikan->id,
            'nomor_penarikan' => $penarikan->nomor_penarikan,
            'jenis_simpanan' => $penarikan->jenis_simpanan,
            'jumlah' => $penarikan->jumlah,
            'bank' => $penarikan->bank,
            'nama_bank' => $penarikan->nama_bank,
            'nama_nasabah' => $penarikan->nama_nasabah,
            'referensi_penarikan' => $penarikan->referensi_penarikan,
            'catatan_pengguna' => $penarikan->catatan_pengguna,
            'status' => $penarikan->status?->value,
            'status_label' => str_replace('_', ' ', strtoupper($penarikan->status?->value ?? '')),
            'catatan_verifikasi' => $penarikan->catatan_verifikasi,
            'alasan_penolakan' => $penarikan->alasan_penolakan,
            'referensi_transfer' => $penarikan->referensi_transfer,
            'waktu_transfer' => $penarikan->waktu_transfer?->toIso8601String(),
            'dikirim_at' => $penarikan->dikirim_at?->toIso8601String(),
            'disetujui_at' => $penarikan->disetujui_at?->toIso8601String(),
            'ditolak_at' => $penarikan->ditolak_at?->toIso8601String(),
            'selesai_at' => $penarikan->selesai_at?->toIso8601String(),
            'no_tabungan' => $penarikan->tabungan?->no_tabungan,
            'created_at' => $penarikan->created_at?->toIso8601String(),
        ];
    }
}
