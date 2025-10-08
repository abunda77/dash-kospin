<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BarcodeScanLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BarcodeScanController extends Controller
{
    /**
     * Get barcode scan statistics
     */
    public function stats(): JsonResponse
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        $stats = [
            'total_scans' => BarcodeScanLog::count(),
            'today' => BarcodeScanLog::whereDate('scanned_at', $today)->count(),
            'this_week' => BarcodeScanLog::where('scanned_at', '>=', $thisWeek)->count(),
            'this_month' => BarcodeScanLog::where('scanned_at', '>=', $thisMonth)->count(),
            'unique_tabungan' => BarcodeScanLog::distinct('tabungan_id')->count('tabungan_id'),
            'mobile_scans' => BarcodeScanLog::where('is_mobile', true)->count(),
            'desktop_scans' => BarcodeScanLog::where('is_mobile', false)->count(),
            'mobile_percentage' => $this->calculatePercentage(
                BarcodeScanLog::where('is_mobile', true)->count(),
                BarcodeScanLog::count()
            ),
        ];

        // Top scanned tabungan
        $topScanned = BarcodeScanLog::select('tabungan_id')
            ->selectRaw('COUNT(*) as scan_count')
            ->with('tabungan:id,no_tabungan')
            ->groupBy('tabungan_id')
            ->orderByDesc('scan_count')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'tabungan_id' => $item->tabungan_id,
                    'no_tabungan' => $item->tabungan->no_tabungan ?? 'N/A',
                    'scan_count' => $item->scan_count,
                ];
            });

        // Scans by day (last 7 days)
        $scansByDay = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $scansByDay[] = [
                'date' => $date->format('Y-m-d'),
                'date_formatted' => $date->format('d M'),
                'count' => BarcodeScanLog::whereDate('scanned_at', $date)->count(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'top_scanned' => $topScanned,
                'scans_by_day' => $scansByDay,
            ],
        ]);
    }

    /**
     * Get recent scans
     */
    public function recentScans(Request $request): JsonResponse
    {
        $limit = min((int) $request->get('limit', 10), 50);

        $scans = BarcodeScanLog::with('tabungan:id,no_tabungan,id_profile')
            ->orderByDesc('scanned_at')
            ->limit($limit)
            ->get()
            ->map(function ($scan) {
                return [
                    'id' => $scan->id,
                    'tabungan_id' => $scan->tabungan_id,
                    'no_tabungan' => $scan->tabungan->no_tabungan ?? 'N/A',
                    'hash' => $scan->hash,
                    'ip_address' => $this->maskIp($scan->ip_address),
                    'is_mobile' => $scan->is_mobile,
                    'scanned_at' => $scan->scanned_at->toISOString(),
                    'scanned_at_formatted' => $scan->scanned_at->format('d/m/Y H:i:s'),
                    'scanned_at_human' => $scan->scanned_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $scans,
        ]);
    }

    /**
     * Get my scans (authenticated user)
     */
    public function myScans(Request $request): JsonResponse
    {
        $user = $request->user();
        $profileId = $user->profile->id ?? null;

        if (!$profileId) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found',
            ], 404);
        }

        $scans = BarcodeScanLog::whereHas('tabungan', function ($query) use ($profileId) {
            $query->where('id_profile', $profileId);
        })
            ->with('tabungan:id,no_tabungan')
            ->orderByDesc('scanned_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $scans,
        ]);
    }

    /**
     * Get scan history for specific tabungan
     */
    public function scanHistory(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $profileId = $user->profile->id ?? null;

        // Verify ownership
        $tabungan = \App\Models\Tabungan::where('id', $id)
            ->where('id_profile', $profileId)
            ->first();

        if (!$tabungan) {
            return response()->json([
                'success' => false,
                'message' => 'Tabungan not found or access denied',
            ], 404);
        }

        $scans = BarcodeScanLog::where('tabungan_id', $id)
            ->orderByDesc('scanned_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'tabungan' => [
                    'id' => $tabungan->id,
                    'no_tabungan' => $tabungan->no_tabungan,
                ],
                'scans' => $scans,
            ],
        ]);
    }

    /**
     * Calculate percentage
     */
    private function calculatePercentage(int $part, int $total): float
    {
        if ($total === 0) {
            return 0;
        }

        return round(($part / $total) * 100, 2);
    }

    /**
     * Mask IP address for privacy
     */
    private function maskIp(?string $ip): string
    {
        if (!$ip) {
            return 'N/A';
        }

        $parts = explode('.', $ip);
        if (count($parts) === 4) {
            return $parts[0] . '.' . $parts[1] . '.***.' . $parts[3];
        }

        return 'N/A';
    }
}
