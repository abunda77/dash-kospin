<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        try {
            $profiles = Profile::where('id_user', Auth::id())->first();

            return response()->json([
                'status' => true,
                'message' => 'Data profile berhasil diambil',
                'data' => $profiles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'string|max:255',
                'address' => 'required|string',
                'phone' => 'required|string',
                'email' => 'required|email',
                'whatsapp' => 'string',
                'gender' => 'required|in:male,female',
                'birthday' => 'required|date',
                'mariage' => 'required|string',
                'job' => 'required|string',
                'monthly_income' => 'required|numeric',
                'province_id' => 'required|exists:provinces,id',
                'district_id' => 'required|exists:districts,id',
                'city_id' => 'required|exists:cities,id',
                'village_id' => 'required|exists:villages,id',
            ]);

            $validatedData['id_user'] = Auth::id();
            $validatedData['is_active'] = true;

            $profile = Profile::create($validatedData);

            return response()->json([
                'status' => true,
                'message' => 'Profile berhasil dibuat',
                'data' => $profile
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $profile = Profile::where('id_user', $id)
                            ->where('id_user', Auth::id())
                            ->firstOrFail();

            return response()->json([
                'status' => true,
                'message' => 'Data profile ditemukan',
                'data' => $profile
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Profile tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $profile = Profile::where('id_user', $id)
                            ->where('id_user', Auth::id())
                            ->firstOrFail();

            $validatedData = $request->validate([
                'first_name' => 'string|max:255',
                'last_name' => 'string|max:255',
                'address' => 'string',
                'phone' => 'string',
                'email' => 'email',
                'whatsapp' => 'string',
                'gender' => 'in:male,female',
                'birthday' => 'date',
                'mariage' => 'string',
                'job' => 'string',
                'monthly_income' => 'numeric',
                'province_id' => 'exists:provinces,id',
                'district_id' => 'exists:districts,id',
                'city_id' => 'exists:cities,id',
                'village_id' => 'exists:villages,id',
            ]);

            $profile->update($validatedData);

            return response()->json([
                'status' => true,
                'message' => 'Profile berhasil diperbarui',
                'data' => $profile
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $profile = Profile::where('id_user', $id)
                            ->where('id_user', Auth::id())
                            ->firstOrFail();

            $profile->delete();

            return response()->json([
                'status' => true,
                'message' => 'Profile berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
