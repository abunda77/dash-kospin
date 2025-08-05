<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LoanReportExportService;
use Illuminate\Support\Facades\Storage;

class TestPdfGeneration extends Command
{
    protected $signature = 'test:pdf-generation';
    protected $description = 'Test PDF generation for loan reports';

    public function handle()
    {
        $this->info('Testing PDF generation...');
        
        try {
            $exportService = app(LoanReportExportService::class);
            
            // Test loan report
            $this->info('🔄 Generating loan report PDF...');
            $loanPdf = $exportService->exportLoanReport();
            $loanContent = $loanPdf->output();
            Storage::put('test-loan-report.pdf', $loanContent);
            $this->info('✅ Loan report PDF generated successfully!');
            $this->info('📊 Loan PDF size: ' . strlen($loanContent) . ' bytes');
            
            // Test transaction report
            $this->info('🔄 Generating transaction report PDF...');
            $transactionPdf = $exportService->exportTransactionReport();
            $transactionContent = $transactionPdf->output();
            Storage::put('test-transaction-report.pdf', $transactionContent);
            $this->info('✅ Transaction report PDF generated successfully!');
            $this->info('📊 Transaction PDF size: ' . strlen($transactionContent) . ' bytes');
            
            $this->info('📁 Files saved to storage/app/');
            
        } catch (\Exception $e) {
            $this->error('❌ PDF generation failed:');
            $this->error($e->getMessage());
            $this->error('Line: ' . $e->getLine());
            $this->error('File: ' . $e->getFile());
            
            if ($e->getPrevious()) {
                $this->error('Previous error: ' . $e->getPrevious()->getMessage());
            }
        }
        
        return 0;
    }
}
