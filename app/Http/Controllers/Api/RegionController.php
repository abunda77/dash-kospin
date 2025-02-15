<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RegionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     try {
    //         $regions = Region::all();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Daftar region berhasil diambil',
    //             'data' => $regions
    //         ], Response::HTTP_OK);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Gagal mengambil daftar region',
    //             'error' => $e->getMessage()
    //         ], Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     try {
    //         $validated = $request->validate([
    //             'code' => 'required|string|unique:regions,code',
    //             'name' => 'required|string',
    //             'level' => 'required|string|in:' . implode(',', [
    //                 Region::LEVEL_PROVINCE,
    //                 Region::LEVEL_DISTRICT,
    //                 Region::LEVEL_CITY,
    //                 Region::LEVEL_VILLAGE,
    //             ]),
    //         ]);

    //         $region = Region::create($validated);

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Region berhasil dibuat',
    //             'data' => $region
    //         ], Response::HTTP_CREATED);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Gagal membuat region',
    //             'error' => $e->getMessage()
    //         ], Response::HTTP_BAD_REQUEST);
    //     }
    // }

    /**
     * Display the specified resource.
     */
    public function show(string $code)
    {
        try {
            $region = Region::with(['parent', 'children'])->findOrFail($code);

            return response()->json([
                'status' => true,
                'message' => 'Region berhasil ditemukan',
                'data' => $region
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Region tidak ditemukan',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, string $code)
    // {
    //     try {
    //         $region = Region::findOrFail($code);

    //         $validated = $request->validate([
    //             'name' => 'required|string',
    //             'level' => 'required|string|in:' . implode(',', [
    //                 Region::LEVEL_PROVINCE,
    //                 Region::LEVEL_DISTRICT,
    //                 Region::LEVEL_CITY,
    //                 Region::LEVEL_VILLAGE,
    //             ]),
    //         ]);

    //         $region->update($validated);

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Region berhasil diperbarui',
    //             'data' => $region
    //         ], Response::HTTP_OK);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Gagal memperbarui region',
    //             'error' => $e->getMessage()
    //         ], Response::HTTP_BAD_REQUEST);
    //     }
    // }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(string $code)
    // {
    //     try {
    //         $region = Region::findOrFail($code);
    //         $region->delete();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Region berhasil dihapus'
    //         ], Response::HTTP_OK);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Gagal menghapus region',
    //             'error' => $e->getMessage()
    //         ], Response::HTTP_BAD_REQUEST);
    //     }
    // }
}
