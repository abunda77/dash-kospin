<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MakanBergizisGratis;
use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use App\Http\Resources\MakanBergizisGratisResource;
use App\Helpers\HashidsHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MakanBergizisGratisController extends Controller
{
    /**
     * Get list of records with optional filters
     */
    public function index(Request $request)
    {
        $query = MakanBergizisGratis::with(['tabungan', 'profile']);

        // Filter by date
        if ($request->has('tanggal')) {
            $query->whereDate('tanggal_pemberian', $request->tanggal);
        }

        // Filter by date range
        if ($request->has('dari') && $request->has('sampai')) {
            $query->whereBetween('tanggal_pemberian', [$request->dari, $request->sampai]);
        }

        // Filter by no_tabungan
        if ($request->has('no_tabungan')) {
            $query->where('no_tabungan', $request->no_tabungan);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $records = $query->latest('tanggal_pemberian')->paginate($perPage);

        return MakanBergizisGratisResource::collection($records);
    }

    /**
     * Get single record by ID (supports both numeric ID and Hashids)
     */
    public function show($id)
    {
        // Try to decode if it's a Hashids format
        $decodedId = is_numeric($id) ? $id : HashidsHelper::decode($id);
        
        if (!$decodedId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid ID format',
                'error' => 'ID tidak valid atau tidak dapat didecode'
            ], 400);
        }
        
        $record = MakanBergizisGratis::with(['tabungan', 'profile'])->find($decodedId);
        
        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found',
                'error' => 'Data tidak ditemukan dengan ID: ' . $id
            ], 404);
        }
        
        return new MakanBergizisGratisResource($record);
    }

    /**
     * Check if record exists for today
     */
    public function checkToday(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_tabungan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $exists = MakanBergizisGratis::existsForToday($request->no_tabungan);

        return response()->json([
            'success' => true,
            'data' => [
                'no_tabungan' => $request->no_tabungan,
                'tanggal' => today()->format('d/m/Y'),
                'exists' => $exists,
                'status' => $exists ? 'already_recorded' : 'available'
            ]
        ]);
    }

    /**
     * Store new record
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_tabungan' => 'required|string|exists:tabungans,no_tabungan',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $noTabungan = $request->no_tabungan;

        // Check if already recorded today
        if (MakanBergizisGratis::existsForToday($noTabungan)) {
            return response()->json([
                'success' => false,
                'message' => 'Data untuk nomor tabungan ini sudah tercatat hari ini',
                'data' => [
                    'no_tabungan' => $noTabungan,
                    'tanggal' => today()->format('d/m/Y'),
                    'status' => 'already_recorded'
                ]
            ], 409);
        }

        try {
            // Get tabungan data with relations
            $tabungan = Tabungan::with(['profile', 'produkTabungan'])
                ->where('no_tabungan', $noTabungan)
                ->firstOrFail();

            // Get last transaction
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
                'profile_id' => $tabungan->profile->id_user, // Use id_user from profile
                'no_tabungan' => $tabungan->no_tabungan,
                'tanggal_pemberian' => today(),
                'data_rekening' => $dataRekening,
                'data_nasabah' => $dataNasabah,
                'data_produk' => $dataProduk,
                'data_transaksi_terakhir' => $dataTransaksiTerakhir,
                'scanned_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data Makan Bergizi Gratis berhasil dicatat',
                'data' => [
                    'id' => $record->id,
                    'no_tabungan' => $record->no_tabungan,
                    'tanggal_pemberian' => $record->tanggal_pemberian->format('d/m/Y'),
                    'rekening' => $dataRekening,
                    'nasabah' => $dataNasabah,
                    'produk_detail' => $dataProduk,
                    'transaksi_terakhir' => $dataTransaksiTerakhir,
                    'metadata' => [
                        'scanned_at' => $record->scanned_at->toISOString(),
                        'scanned_at_formatted' => $record->scanned_at->format('d/m/Y H:i:s'),
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
