<?php

namespace App\Services;

use App\Models\TransaksiTabungan;
use App\Helpers\PdfHelper;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SavingsReportExportService
{
    protected SavingsReportService $savingsReportService;

    public function __construct()
    {
        // Service will be created with specific filters when needed
    }

    public function generateSavingsReportFile($productFilter, array $dateRange, string $filename): string
    {
        $service = new SavingsReportService($productFilter, $dateRange);
        $stats = $service->getSavingsStats();
        $savingsData = $service->getActiveSavingsQuery()->get();
        
        $html = $this->generateSavingsReportHtml($stats, $savingsData, $dateRange, $productFilter);
        
        return PdfHelper::generatePdf($html, $filename);
    }

    public function generateTransactionReportFile($productFilter, array $dateRange, string $filename): string
    {
        $service = new SavingsReportService($productFilter, $dateRange);
        
        // Get transaction data
        $transaksiQuery = TransaksiTabungan::query()
            ->with(['tabungan.profile.user', 'tabungan.produkTabungan', 'admin'])
            ->whereHas('tabungan', function ($q) use ($productFilter) {
                $q->where('status_rekening', 'aktif');
                if ($productFilter) {
                    $q->where('produk_tabungan', $productFilter);
                }
            });
            
        if (!empty($dateRange['start_date']) && !empty($dateRange['end_date'])) {
            $transaksiQuery->whereBetween('tanggal_transaksi', [
                $dateRange['start_date'],
                $dateRange['end_date']
            ]);
        }
        
        $transactionData = $transaksiQuery->orderBy('tanggal_transaksi', 'desc')->get();
        $stats = $service->getSavingsStats();
        
        $html = $this->generateTransactionReportHtml($stats, $transactionData, $dateRange, $productFilter);
        
        return PdfHelper::generatePdf($html, $filename);
    }

    public function exportBulkSavingsReport(Collection $records): string
    {
        $filename = 'bulk-savings-report-' . Carbon::now()->format('Y-m-d-H-i-s') . '.pdf';
        $html = $this->generateBulkSavingsReportHtml($records);
        
        return PdfHelper::generatePdf($html, $filename);
    }

    private function generateSavingsReportHtml(array $stats, Collection $savingsData, array $dateRange, $productFilter): string
    {
        $startDate = Carbon::parse($dateRange['start_date'])->format('d M Y');
        $endDate = Carbon::parse($dateRange['end_date'])->format('d M Y');
        $productName = $productFilter ? \App\Models\ProdukTabungan::find($productFilter)?->nama_produk : 'Semua Produk';
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Laporan Tabungan</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .header h1 { margin: 0; font-size: 18px; }
                .header p { margin: 5px 0; color: #666; }
                .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 30px; }
                .stat-card { border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
                .stat-value { font-size: 16px; font-weight: bold; color: #2563eb; }
                .stat-label { color: #666; margin-top: 5px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f8f9fa; font-weight: bold; }
                .text-right { text-align: right; }
                .footer { margin-top: 30px; text-align: center; color: #666; font-size: 10px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>LAPORAN TABUNGAN</h1>
                <p>Periode: ' . $startDate . ' - ' . $endDate . '</p>
                <p>Produk: ' . $productName . '</p>
                <p>Dicetak pada: ' . Carbon::now()->format('d M Y H:i:s') . '</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">' . number_format($stats['total_accounts']) . '</div>
                    <div class="stat-label">Total Rekening Aktif</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">Rp ' . number_format($stats['total_balance'], 0, ',', '.') . '</div>
                    <div class="stat-label">Total Saldo</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">Rp ' . number_format($stats['avg_balance'], 0, ',', '.') . '</div>
                    <div class="stat-label">Rata-rata Saldo</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">' . number_format($stats['transaction_count']) . '</div>
                    <div class="stat-label">Total Transaksi</div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>No. Tabungan</th>
                        <th>Nama Nasabah</th>
                        <th>Produk</th>
                        <th>Saldo</th>
                        <th>Tanggal Buka</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($savingsData as $tabungan) {
            $html .= '
                    <tr>
                        <td>' . $tabungan->no_tabungan . '</td>
                        <td>' . ($tabungan->profile?->user?->name ?? 'N/A') . '</td>
                        <td>' . ($tabungan->produkTabungan?->nama_produk ?? 'N/A') . '</td>
                        <td class="text-right">Rp ' . number_format($tabungan->saldo, 0, ',', '.') . '</td>
                        <td>' . Carbon::parse($tabungan->tanggal_buka_rekening)->format('d M Y') . '</td>
                        <td>' . ucfirst($tabungan->status_rekening) . '</td>
                    </tr>';
        }
        
        $html .= '
                </tbody>
            </table>

            <div class="footer">
                <p>Laporan ini digenerate secara otomatis oleh sistem</p>
            </div>
        </body>
        </html>';
        
        return $html;
    }

    private function generateTransactionReportHtml(array $stats, Collection $transactionData, array $dateRange, $productFilter): string
    {
        $startDate = Carbon::parse($dateRange['start_date'])->format('d M Y');
        $endDate = Carbon::parse($dateRange['end_date'])->format('d M Y');
        $productName = $productFilter ? \App\Models\ProdukTabungan::find($productFilter)?->nama_produk : 'Semua Produk';
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Laporan Transaksi Tabungan</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .header h1 { margin: 0; font-size: 18px; }
                .header p { margin: 5px 0; color: #666; }
                .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
                .stat-card { border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
                .stat-value { font-size: 16px; font-weight: bold; color: #2563eb; }
                .stat-label { color: #666; margin-top: 5px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 11px; }
                th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
                th { background-color: #f8f9fa; font-weight: bold; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .debit { color: #10b981; }
                .kredit { color: #f59e0b; }
                .footer { margin-top: 30px; text-align: center; color: #666; font-size: 10px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>LAPORAN TRANSAKSI TABUNGAN</h1>
                <p>Periode: ' . $startDate . ' - ' . $endDate . '</p>
                <p>Produk: ' . $productName . '</p>
                <p>Dicetak pada: ' . Carbon::now()->format('d M Y H:i:s') . '</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">Rp ' . number_format($stats['total_deposits'], 0, ',', '.') . '</div>
                    <div class="stat-label">Total Setoran (' . number_format($stats['deposit_count']) . ' transaksi)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">Rp ' . number_format($stats['total_withdrawals'], 0, ',', '.') . '</div>
                    <div class="stat-label">Total Penarikan (' . number_format($stats['withdrawal_count']) . ' transaksi)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">Rp ' . number_format($stats['total_deposits'] - $stats['total_withdrawals'], 0, ',', '.') . '</div>
                    <div class="stat-label">Net Flow</div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No. Tabungan</th>
                        <th>Nasabah</th>
                        <th>Jenis</th>
                        <th>Jumlah</th>
                        <th>Keterangan</th>
                        <th>Teller</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($transactionData as $transaksi) {
            $jenisClass = $transaksi->jenis_transaksi === TransaksiTabungan::JENIS_SETORAN ? 'debit' : 'kredit';
            $jenisText = $transaksi->jenis_transaksi === TransaksiTabungan::JENIS_SETORAN ? 'Setoran' : 'Penarikan';
            
            $html .= '
                    <tr>
                        <td>' . Carbon::parse($transaksi->tanggal_transaksi)->format('d M Y H:i') . '</td>
                        <td>' . $transaksi->tabungan->no_tabungan . '</td>
                        <td>' . ($transaksi->tabungan->profile?->user?->name ?? 'N/A') . '</td>
                        <td class="' . $jenisClass . '">' . $jenisText . '</td>
                        <td class="text-right">Rp ' . number_format($transaksi->jumlah, 0, ',', '.') . '</td>
                        <td>' . ($transaksi->keterangan ?? '-') . '</td>
                        <td>' . ($transaksi->admin?->name ?? 'N/A') . '</td>
                    </tr>';
        }
        
        $html .= '
                </tbody>
            </table>

            <div class="footer">
                <p>Laporan ini digenerate secara otomatis oleh sistem</p>
            </div>
        </body>
        </html>';
        
        return $html;
    }

    private function generateBulkSavingsReportHtml(Collection $records): string
    {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Laporan Tabungan Terpilih</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .header h1 { margin: 0; font-size: 18px; }
                .header p { margin: 5px 0; color: #666; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f8f9fa; font-weight: bold; }
                .text-right { text-align: right; }
                .footer { margin-top: 30px; text-align: center; color: #666; font-size: 10px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>LAPORAN TABUNGAN TERPILIH</h1>
                <p>Total Rekening: ' . $records->count() . '</p>
                <p>Dicetak pada: ' . Carbon::now()->format('d M Y H:i:s') . '</p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>No. Tabungan</th>
                        <th>Nama Nasabah</th>
                        <th>Produk</th>
                        <th>Saldo</th>
                        <th>Tanggal Buka</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($records as $tabungan) {
            $html .= '
                    <tr>
                        <td>' . $tabungan->no_tabungan . '</td>
                        <td>' . ($tabungan->profile?->user?->name ?? 'N/A') . '</td>
                        <td>' . ($tabungan->produkTabungan?->nama_produk ?? 'N/A') . '</td>
                        <td class="text-right">Rp ' . number_format($tabungan->saldo, 0, ',', '.') . '</td>
                        <td>' . Carbon::parse($tabungan->tanggal_buka_rekening)->format('d M Y') . '</td>
                        <td>' . ucfirst($tabungan->status_rekening) . '</td>
                    </tr>';
        }
        
        $totalSaldo = $records->sum('saldo');
        $html .= '
                    <tr style="background-color: #f8f9fa; font-weight: bold;">
                        <td colspan="3">TOTAL</td>
                        <td class="text-right">Rp ' . number_format($totalSaldo, 0, ',', '.') . '</td>
                        <td colspan="2"></td>
                    </tr>
                </tbody>
            </table>

            <div class="footer">
                <p>Laporan ini digenerate secara otomatis oleh sistem</p>
            </div>
        </body>
        </html>';
        
        return $html;
    }
}