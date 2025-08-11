# PDF Export Console Commands

This document explains how to use the console commands for exporting PDF reports with progress tracking and public download links.

## 🚀 Quick Start

### Basic Export Commands

```bash
# Export loan report (all data)
php artisan report:export-loan

# Export savings report (all data)
php artisan report:export-savings

# Export deposit report (all data)
php artisan report:export-deposit

# Export transaction reports
php artisan report:export-loan --type=transaction
php artisan report:export-savings --type=transaction

# Export with date range
php artisan report:export-loan --start-date=2024-01-01 --end-date=2024-12-31
php artisan report:export-savings --start-date=2024-01-01 --end-date=2024-12-31
php artisan report:export-deposit --start-date=2024-01-01 --end-date=2024-12-31

# Export with product filter
php artisan report:export-loan --product=1
php artisan report:export-savings --product=1

# Export with status/term filter
php artisan report:export-deposit --status=active --jangka-waktu=12

# Export to public storage with download link
php artisan report:export-loan --public
php artisan report:export-savings --public
php artisan report:export-deposit --public
```

## 📊 Available Commands

### 1. Export Loan Reports (`report:export-loan`)

**Syntax:**

```bash
php artisan report:export-loan [options]
```

**Options:**

-   `--type=TYPE` - Report type: `loan`, `transaction`, `bulk` (default: `loan`)
-   `--product=ID` - Filter by product ID
-   `--start-date=DATE` - Start date in Y-m-d format (e.g., 2024-01-01)
-   `--end-date=DATE` - End date in Y-m-d format (e.g., 2024-12-31)
-   `--chunk-size=SIZE` - Processing chunk size (default: 100)
-   `--memory-limit=MB` - Memory limit in MB (default: 1024)
-   `--public` - Save to public storage for web download

**Examples:**

```bash
# Basic loan report
php artisan report:export-loan

# Transaction report with date range
php artisan report:export-loan --type=transaction --start-date=2024-01-01 --end-date=2024-03-31

# Loan report for specific product with public access
php artisan report:export-loan --product=2 --public

# Large dataset with custom chunk size and memory limit
php artisan report:export-loan --chunk-size=500 --memory-limit=2048
```

### 2. Export Savings Reports (`report:export-savings`)

**Syntax:**

```bash
php artisan report:export-savings [options]
```

**Options:**

-   `--type=TYPE` - Report type: `savings`, `transaction`, `bulk` (default: `savings`)
-   `--product=ID` - Filter by savings product ID
-   `--start-date=DATE` - Start date in Y-m-d format (e.g., 2024-01-01)
-   `--end-date=DATE` - End date in Y-m-d format (e.g., 2024-12-31)
-   `--chunk-size=SIZE` - Processing chunk size (default: 100)
-   `--memory-limit=MB` - Memory limit in MB (default: 1024)
-   `--public` - Save to public storage for web download

**Examples:**

```bash
# Basic savings report
php artisan report:export-savings

# Transaction report with date range
php artisan report:export-savings --type=transaction --start-date=2024-01-01 --end-date=2024-03-31

# Savings report for specific product with public access
php artisan report:export-savings --product=3 --public

# Large dataset with custom chunk size and memory limit
php artisan report:export-savings --chunk-size=500 --memory-limit=2048
```

### 3. Export Deposit Reports (`report:export-deposit`)

**Syntax:**

```bash
php artisan report:export-deposit [options]
```

**Options:**

-   `--status=STATUS` - Filter by status: `all`, `active`, `ended`, `cancelled` (default: `all`)
-   `--jangka-waktu=TERM` - Filter by term: `all`, `1`, `3`, `6`, `12`, `24` (default: `all`)
-   `--start-date=DATE` - Start date in Y-m-d format (e.g., 2024-01-01)
-   `--end-date=DATE` - End date in Y-m-d format (e.g., 2024-12-31)
-   `--chunk-size=SIZE` - Processing chunk size (default: 100)
-   `--memory-limit=MB` - Memory limit in MB (default: 1024)
-   `--public` - Save to public storage for web download

**Examples:**

```bash
# Basic deposit report (current month)
php artisan report:export-deposit

# Active deposits only
php artisan report:export-deposit --status=active

# 12-month term deposits
php artisan report:export-deposit --jangka-waktu=12

# Deposit report with date range
php artisan report:export-deposit --start-date=2024-01-01 --end-date=2024-03-31

# Active 6-month deposits with public access
php artisan report:export-deposit --status=active --jangka-waktu=6 --public

# Large dataset with custom chunk size and memory limit
php artisan report:export-deposit --chunk-size=500 --memory-limit=2048
```

### 4. Check Export Progress (`report:check-progress`)

**Syntax:**

```bash
php artisan report:check-progress [key]
```

**Examples:**

```bash
# Check specific progress (key shown when starting export)
php artisan report:check-progress pdf_export_progress_abc123

# List all active exports
php artisan report:check-progress
```

### 5. Cleanup Old Files (`report:cleanup`)

**Syntax:**

```bash
php artisan report:cleanup [options]
```

**Options:**

-   `--hours=HOURS` - Delete files older than specified hours (default: 24)
-   `--dry-run` - Show what would be deleted without actually deleting
-   `--force` - Skip confirmation prompt

**Examples:**

```bash
# Clean files older than 24 hours (with confirmation)
php artisan report:cleanup

# Clean files older than 48 hours
php artisan report:cleanup --hours=48

# Preview what would be deleted
php artisan report:cleanup --dry-run

# Clean without confirmation
php artisan report:cleanup --force
```

## 🌐 Web Interface

### Progress Monitor

Access the web-based progress monitor at:

```
http://your-domain.com/export-monitor
```

**Features:**

-   Real-time progress tracking
-   Visual progress bar
-   Time estimates
-   Error reporting
-   Auto-refresh every 2 seconds

**Usage:**

1. Start an export command in terminal
2. Copy the progress key from console output
3. Open the monitor page in browser
4. Paste the key and click "Monitor"

### Direct Progress API

Check progress programmatically:

```bash
curl http://your-domain.com/export-progress/pdf_export_progress_abc123
```

Response:

```json
{
    "processed": 150,
    "total": 1000,
    "percent": 15.0,
    "status": "processing",
    "started_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-15T10:32:15Z"
}
```

## 📁 File Storage

### Storage Locations

**Temporary Files** (default):

-   Location: `storage/app/temp/`
-   Access: Local file system only
-   Cleanup: Manual or via cleanup command

**Public Files** (with `--public` flag):

-   Location: `storage/app/public/reports/`
-   Access: Web accessible via download URLs
-   Cleanup: Via cleanup command or manual

### Download URLs

When using `--public` flag, files are accessible via:

```
http://your-domain.com/download-report/filename.pdf
```

Example output:

```
✅ PDF export completed successfully!

📄 File: laporan-pinjaman-semua-produk-2024-01-15-14-30-45.pdf
📏 Size: 2.5 MB
📂 Path: /path/to/storage/app/public/reports/laporan-pinjaman-semua-produk-2024-01-15-14-30-45.pdf

🔗 Public Download URL:
   http://localhost/download-report/laporan-pinjaman-semua-produk-2024-01-15-14-30-45.pdf

💡 Note: The file will be accessible via web browser
```

## ⚡ Performance Tips

### Memory Management

-   Use `--chunk-size` to control memory usage
-   Increase `--memory-limit` for large datasets
-   Monitor memory usage in logs

### Large Datasets

```bash
# For very large datasets (>10k records)
php artisan report:export-loan --chunk-size=1000 --memory-limit=2048

# For smaller datasets (faster processing)
php artisan report:export-loan --chunk-size=50 --memory-limit=512
```

### Background Processing

```bash
# Run in background (Linux/Mac)
nohup php artisan report:export-loan --public > export.log 2>&1 &

# Windows (using start)
start /B php artisan report:export-loan --public
```

## 🔧 Troubleshooting

### Common Issues

**1. Memory Limit Exceeded**

```bash
# Solution: Increase memory limit and reduce chunk size
php artisan report:export-loan --memory-limit=2048 --chunk-size=50
```

**2. File Not Found (404)**

-   Check if file exists in storage
-   Verify file permissions
-   Ensure storage link is created: `php artisan storage:link`

**3. Progress Not Found**

-   Verify the progress key is correct
-   Check if export process is still running
-   Progress data expires after 5 minutes

**4. PDF Generation Fails**

-   Check Laravel logs: `storage/logs/laravel.log`
-   Verify database connections
-   Ensure required models exist

### Debug Mode

Enable detailed logging by setting in `.env`:

```env
LOG_LEVEL=debug
```

Check logs:

```bash
tail -f storage/logs/laravel.log
```

## 📋 Scheduled Cleanup

Add to `app/Console/Kernel.php` for automatic cleanup:

```php
protected function schedule(Schedule $schedule)
{
    // Clean up old report files daily at 2 AM
    $schedule->command('report:cleanup --force')
             ->dailyAt('02:00')
             ->withoutOverlapping();
}
```

## 🔒 Security Notes

-   PDF files are only accessible via secure download routes
-   File type validation prevents non-PDF downloads
-   Filename sanitization prevents directory traversal
-   Public files should be cleaned up regularly
-   Consider adding authentication to download routes for sensitive data

## 📊 Monitoring & Logging

All export operations are logged with:

-   Start/completion times
-   File sizes and locations
-   Error details
-   Memory usage statistics
-   Progress tracking data

Check logs for detailed information:

```bash
grep "PDF generation" storage/logs/laravel.log
grep "Export Progress" storage/logs/laravel.log
```

## 🎯 Best Practices

1. **Use `--public` for web downloads**: When you need shareable links
2. **Regular cleanup**: Schedule cleanup command to prevent disk space issues
3. **Monitor progress**: Use web interface for long-running exports
4. **Chunk size tuning**: Adjust based on available memory and data size
5. **Background processing**: For large exports, run in background
6. **Error handling**: Always check logs if exports fail
7. **Security**: Add authentication for sensitive reports

## 📞 Support

If you encounter issues:

1. Check the troubleshooting section above
2. Review Laravel logs for detailed error messages
3. Verify all dependencies are installed
4. Ensure database connectivity
5. Check file permissions on storage directories
