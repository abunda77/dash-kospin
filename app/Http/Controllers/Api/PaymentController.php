<?php

namespace App\Http\Controllers\Api;

use App\Helpers\QrisHelper;
use App\Http\Controllers\Controller;
use App\Models\QrisStatic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function listQris(Request $request): JsonResponse
    {
        $query = QrisStatic::query();

        if ($request->boolean('active_only', true)) {
            $query->where('is_active', true);
        }

        $qrisList = $query->select('id', 'name', 'merchant_name', 'description', 'is_active', 'created_at')
            ->orderBy('name')
            ->get()
            ->map(function ($item) {
                return [
                    'id'            => $item->id,
                    'name'          => $item->name,
                    'merchant_name' => $item->merchant_name,
                    'description'   => $item->description,
                    'is_active'     => $item->is_active,
                    'image_url'     => $item->qris_image
                        ? Storage::disk('public')->url($item->qris_image)
                        : null,
                    'created_at'    => $item->created_at->toISOString(),
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $qrisList,
        ]);
    }

    public function showQris(int $id): JsonResponse
    {
        $qris = QrisStatic::find($id);

        if (! $qris) {
            return response()->json([
                'success' => false,
                'message' => 'QRIS tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'            => $qris->id,
                'name'          => $qris->name,
                'merchant_name' => $qris->merchant_name,
                'description'   => $qris->description,
                'qris_string'   => $qris->qris_string,
                'is_active'     => $qris->is_active,
                'image_url'     => $qris->qris_image
                    ? Storage::disk('public')->url($qris->qris_image)
                    : null,
                'created_at'    => $qris->created_at->toISOString(),
                'updated_at'    => $qris->updated_at->toISOString(),
            ],
        ]);
    }

    public function generateDynamic(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'qris_id'     => 'nullable|integer|exists:qris_statics,id',
            'qris_string' => 'required_without:qris_id|string|min:20',
            'amount'      => 'required|numeric|min:1',
            'fee_type'    => 'nullable|in:Rupiah,Persentase',
            'fee_value'   => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $staticQris = $request->input('qris_string');

        if ($request->filled('qris_id')) {
            $saved = QrisStatic::where('id', $request->qris_id)
                ->where('is_active', true)
                ->first();

            if (! $saved) {
                return response()->json([
                    'success' => false,
                    'message' => 'QRIS statis tidak ditemukan atau tidak aktif.',
                ], 404);
            }

            $staticQris = $saved->qris_string;
        }

        if (! QrisHelper::isValidQris($staticQris)) {
            return response()->json([
                'success' => false,
                'message' => 'String QRIS tidak valid.',
            ], 422);
        }

        try {
            $amount    = $request->input('amount');
            $feeType   = $request->input('fee_type', 'Rupiah');
            $feeValue  = $request->input('fee_value', 0);

            $merchantName = QrisHelper::parseMerchantName($staticQris);
            $dynamicQris  = $this->buildDynamicQris($staticQris, $amount, $feeType, $feeValue);

            $filename  = $this->saveQrImage($dynamicQris);
            $imageUrl  = $filename
                ? Storage::disk('public')->url('qris-generated/'.$filename)
                : null;

            return response()->json([
                'success' => true,
                'message' => 'QRIS dinamis berhasil dibuat.',
                'data'    => [
                    'merchant_name' => $merchantName,
                    'amount'        => (int) $amount,
                    'fee_type'      => $feeType,
                    'fee_value'     => (float) $feeValue,
                    'dynamic_qris'  => $dynamicQris,
                    'image_url'     => $imageUrl,
                    'filename'      => $filename,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat QRIS dinamis: '.$e->getMessage(),
            ], 500);
        }
    }

    public function validateQris(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'qris_string' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $qrisString = $request->input('qris_string');
        $isValid    = QrisHelper::isValidQris($qrisString);

        $data = ['is_valid' => $isValid];

        if ($isValid) {
            $data['merchant_name'] = QrisHelper::parseMerchantName($qrisString);
            $data['is_static']     = str_contains($qrisString, '010211');
            $data['is_dynamic']    = str_contains($qrisString, '010212');
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    private function buildDynamicQris(
        string $staticQris,
        string $amount,
        string $feeType,
        string $feeValue
    ): string {
        if (strlen($staticQris) < 4) {
            throw new \Exception('Data QRIS statis tidak valid.');
        }

        $qrisWithoutCrc = substr($staticQris, 0, -4);
        $step1          = str_replace('010211', '010212', $qrisWithoutCrc);
        $parts          = explode('5802ID', $step1);

        if (count($parts) !== 2) {
            throw new \Exception("Format QRIS tidak sesuai (tidak ditemukan '5802ID').");
        }

        $amountStr = strval(intval($amount));
        $amountTag = '54'.str_pad(strlen($amountStr), 2, '0', STR_PAD_LEFT).$amountStr;

        $feeTag = '';
        if ($feeValue && floatval($feeValue) > 0) {
            if ($feeType === 'Rupiah') {
                $feeValueStr = strval(intval($feeValue));
                $feeTag      = '55020256'.str_pad(strlen($feeValueStr), 2, '0', STR_PAD_LEFT).$feeValueStr;
            } else {
                $feeTag = '55020357'.str_pad(strlen($feeValue), 2, '0', STR_PAD_LEFT).$feeValue;
            }
        }

        $payload = $parts[0].$amountTag.$feeTag.'5802ID'.$parts[1];

        return $payload.$this->crc16($payload);
    }

    private function crc16(string $str): string
    {
        $crc    = 0xFFFF;
        $strlen = strlen($str);

        for ($c = 0; $c < $strlen; $c++) {
            $crc ^= ord($str[$c]) << 8;
            for ($i = 0; $i < 8; $i++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc = $crc << 1;
                }
            }
        }

        return str_pad(strtoupper(dechex($crc & 0xFFFF)), 4, '0', STR_PAD_LEFT);
    }

    private function saveQrImage(string $qrisData): ?string
    {
        try {
            $builder = new \Endroid\QrCode\Builder\Builder(
                writer: new \Endroid\QrCode\Writer\PngWriter,
                writerOptions: [],
                validateResult: false,
                data: $qrisData,
                encoding: new \Endroid\QrCode\Encoding\Encoding('UTF-8'),
                size: 400,
                margin: 10,
            );

            $result   = $builder->build();
            $filename = 'qris-dynamic-'.now()->format('YmdHis').'-'.uniqid().'.png';

            Storage::disk('public')->put('qris-generated/'.$filename, $result->getString());

            return $filename;
        } catch (\Exception $e) {
            \Log::error('Gagal menyimpan QR image: '.$e->getMessage());

            return null;
        }
    }
}
