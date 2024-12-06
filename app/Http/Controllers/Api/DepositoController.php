<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deposito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DepositoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getDepositoByProfile(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id_profile' => 'required|numeric'
            ]);

            $deposito = Deposito::with('profile')
                ->whereHas('profile', function($query) use ($validatedData) {
                    $query->where('id_user', $validatedData['id_profile']);
                })
                ->get();

            $firstDeposito = $deposito->first();

            if (!$firstDeposito) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data deposito tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Data deposito berhasil diambil',
                'data' => [
                    'info_profile' => [
                        'id_profile' => $validatedData['id_profile'],
                        'nama_lengkap' => "{$firstDeposito->profile->first_name} {$firstDeposito->profile->last_name}",
                        'no_identity' => $firstDeposito->profile->no_identity,
                        'phone' => $firstDeposito->profile->phone
                    ],
                    'deposito' => $deposito
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in getDepositoByProfile: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data deposito',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getDetailByNoRekening(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nomor_rekening' => 'required|string'
            ]);

            $deposito = Deposito::with('profile')
                ->where('nomor_rekening', $validatedData['nomor_rekening'])
                ->first();

            if (!$deposito) {
                return response()->json([
                    'status' => false,
                    'message' => 'Rekening deposito tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Data deposito berhasil diambil',
                'data' => [
                    'info_rekening' => [
                        'nomor_rekening' => $deposito->nomor_rekening,
                        'nama_nasabah' => "{$deposito->profile->first_name} {$deposito->profile->last_name}",
                        'nominal_penempatan' => $deposito->nominal_penempatan,
                        'jangka_waktu' => $deposito->jangka_waktu,
                        'tanggal_pembukaan' => $deposito->tanggal_pembukaan,
                        'tanggal_jatuh_tempo' => $deposito->tanggal_jatuh_tempo,
                        'rate_bunga' => $deposito->rate_bunga,
                        'nominal_bunga' => $deposito->nominal_bunga,
                        'status' => $deposito->status,
                        'perpanjangan_otomatis' => $deposito->perpanjangan_otomatis,
                        'total_penerimaan' => $deposito->nominal_penempatan + $deposito->nominal_bunga
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in getDetailByNoRekening: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil detail deposito',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
