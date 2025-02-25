<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function getApiBaseUrl()
    {
        try {
            $apiBaseUrl = env('APP_BASE_URL_MOBILE');

            if (!$apiBaseUrl) {
                return response()->json([
                    'status' => false,
                    'message' => 'APP_BASE_URL_MOBILE tidak ditemukan di .env'
                ], 500);
            }

            return response()->json([
                'status' => true,
                'message' => 'API base URL berhasil diambil',
                'data' => [
                    'api_base_url' => $apiBaseUrl
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil API base URL',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
