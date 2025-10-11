# Webhook Barcode Tabungan - Makan Bergizi Gratis

## Overview

The system sends webhook notifications to an external URL whenever a successful checkout occurs in the Makan Bergizi Gratis program. This allows external systems to receive real-time notifications about member checkouts.

## Configuration

### Environment Variable

Add the webhook URL to your `.env` file:

```env
WEBHOOK_URL_BARCODE_TABUNGAN=https://your-external-system.com/webhook/endpoint
```

If this variable is not set or empty, webhook notifications will be skipped (no errors will be thrown).

### Configuration File

The webhook URL is configured in `config/services.php`:

```php
'webhook' => [
    'barcode_tabungan_url' => env('WEBHOOK_URL_BARCODE_TABUNGAN'),
],
```

## Webhook Payload

### Request Details

- **Method**: POST
- **Content-Type**: application/json
- **Timeout**: 10 seconds
- **Retry**: 2 attempts with 100ms delay

### Payload Structure

```json
{
  "event": "makan_bergizi_gratis.checkout",
  "timestamp": "2025-10-10T10:30:45.000000Z",
  "data": {
    "id": 123,
    "no_tabungan": "TAB001",
    "tanggal_pemberian": "2025-10-10",
    "scanned_at": "2025-10-10T10:30:45.000000Z",
    "rekening": {
      "no_tabungan": "TAB001",
      "produk": "Tabungan Mitra Sinara",
      "saldo": 1000000,
      "saldo_formatted": "Rp 1.000.000",
      "status": "aktif",
      "tanggal_buka": "01/01/2025",
      "tanggal_buka_iso": "2025-01-01T00:00:00.000000Z"
    },
    "nasabah": {
      "nama_lengkap": "John Doe",
      "first_name": "John",
      "last_name": "Doe",
      "phone": "081234567890",
      "email": "john@example.com",
      "whatsapp": "081234567890",
      "address": "Jl. Example No. 123"
    },
    "produk": {
      "id": 1,
      "nama": "Tabungan Mitra Sinara",
      "keterangan": "Produk tabungan reguler"
    },
    "transaksi_terakhir": {
      "kode_transaksi": "TRX001",
      "jenis_transaksi": "setoran",
      "jenis_transaksi_label": "Setoran",
      "jumlah": 100000,
      "jumlah_formatted": "Rp 100.000",
      "tanggal_transaksi": "09/10/2025 14:30:00",
      "tanggal_transaksi_iso": "2025-10-09T14:30:00.000000Z",
      "keterangan": "Setoran tunai",
      "teller": "Admin User"
    }
  }
}
```

### Field Descriptions

#### Root Level
- `event`: Event type identifier (always "makan_bergizi_gratis.checkout")
- `timestamp`: ISO 8601 timestamp when the webhook was sent
- `data`: Main data object containing checkout information

#### Data Object
- `id`: Unique ID of the checkout record
- `no_tabungan`: Savings account number
- `tanggal_pemberian`: Date of food distribution (Y-m-d format)
- `scanned_at`: ISO 8601 timestamp when QR code was scanned

#### Rekening (Account) Object
- `no_tabungan`: Account number
- `produk`: Product name
- `saldo`: Current balance (numeric)
- `saldo_formatted`: Formatted balance in Rupiah
- `status`: Account status
- `tanggal_buka`: Account opening date (d/m/Y format)
- `tanggal_buka_iso`: Account opening date (ISO 8601)

#### Nasabah (Customer) Object
- `nama_lengkap`: Full name
- `first_name`: First name
- `last_name`: Last name
- `phone`: Phone number
- `email`: Email address
- `whatsapp`: WhatsApp number
- `address`: Full address

#### Produk (Product) Object
- `id`: Product ID
- `nama`: Product name
- `keterangan`: Product description

#### Transaksi Terakhir (Last Transaction) Object
- `kode_transaksi`: Transaction code
- `jenis_transaksi`: Transaction type (setoran/penarikan)
- `jenis_transaksi_label`: Transaction type label (Setoran/Penarikan)
- `jumlah`: Transaction amount (numeric)
- `jumlah_formatted`: Formatted amount in Rupiah
- `tanggal_transaksi`: Transaction date (d/m/Y H:i:s format)
- `tanggal_transaksi_iso`: Transaction date (ISO 8601)
- `keterangan`: Transaction notes
- `teller`: Teller/admin name

**Note**: `transaksi_terakhir` will be `null` if no transactions exist for the account.

## Response Handling

### Expected Response

Your webhook endpoint should return a 2xx status code (200-299) to indicate successful receipt.

### Error Handling

The system handles webhook failures gracefully:

- **Timeout**: 10 seconds timeout with 2 retry attempts
- **Failed Response**: Logged as warning, does not affect checkout process
- **Exception**: Logged as error, does not affect checkout process
- **Missing URL**: Silently skipped with info log

**Important**: Webhook failures will NOT prevent the checkout from completing. The checkout record is saved first, then the webhook is sent asynchronously.

## Logging

All webhook activities are logged in `storage/logs/laravel.log`:

### Success Log
```
[INFO] Webhook notification sent successfully
- record_id: 123
- webhook_url: https://your-external-system.com/webhook/endpoint
- status_code: 200
```

### Warning Log (Failed Response)
```
[WARNING] Webhook notification failed
- record_id: 123
- webhook_url: https://your-external-system.com/webhook/endpoint
- status_code: 500
- response_body: Error message from endpoint
```

### Error Log (Exception)
```
[ERROR] Error sending webhook notification
- record_id: 123
- webhook_url: https://your-external-system.com/webhook/endpoint
- error: Connection timeout
```

### Info Log (URL Not Configured)
```
[INFO] Webhook URL not configured, skipping webhook notification
```

## Testing

### Test Webhook Endpoint

You can use services like:
- **webhook.site**: https://webhook.site (free, instant test URLs)
- **RequestBin**: https://requestbin.com
- **ngrok**: For local development testing

### Example Test Setup

1. Get a test URL from webhook.site
2. Add to `.env`:
   ```env
   WEBHOOK_URL_BARCODE_TABUNGAN=https://webhook.site/your-unique-id
   ```
3. Perform a checkout in the system
4. Check webhook.site to see the received payload

### Manual Testing Script

Create a test endpoint receiver:

```php
<?php
// test-webhook-receiver.php

$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

file_put_contents('webhook-log.txt', date('Y-m-d H:i:s') . "\n" . $payload . "\n\n", FILE_APPEND);

http_response_code(200);
echo json_encode(['status' => 'received']);
```

## Security Considerations

### Recommendations

1. **Use HTTPS**: Always use HTTPS URLs for production webhooks
2. **Validate Source**: Implement IP whitelisting on your webhook endpoint
3. **Add Authentication**: Consider adding a secret token in headers
4. **Verify Payload**: Validate the payload structure before processing
5. **Idempotency**: Handle duplicate webhooks gracefully (same record_id)

### Future Enhancements

Consider implementing:
- Webhook signature verification (HMAC)
- Custom headers for authentication
- Webhook retry queue for failed deliveries
- Webhook delivery status tracking in database

## Implementation Details

### Code Location

The webhook functionality is implemented in:
- **Component**: `app/Livewire/MakanBergizisGratisCheckout.php`
- **Method**: `sendWebhookNotification()`
- **Trigger**: After successful checkout record creation

### Flow

1. User completes checkout
2. Record is saved to database
3. Success message is shown to user
4. Webhook notification is sent (non-blocking)
5. Result is logged

### Performance

- Webhook sending does not block the user interface
- 10-second timeout prevents long waits
- 2 retry attempts for transient failures
- Failed webhooks do not affect checkout completion

## Troubleshooting

### Webhook Not Sending

1. Check if `WEBHOOK_URL_BARCODE_TABUNGAN` is set in `.env`
2. Verify the URL is accessible from your server
3. Check `storage/logs/laravel.log` for error messages

### Webhook Timing Out

1. Ensure your endpoint responds within 10 seconds
2. Consider increasing timeout in code if needed
3. Implement async processing on receiving end

### Webhook Receiving Wrong Data

1. Verify payload structure matches documentation
2. Check Laravel logs for the actual payload sent
3. Ensure your endpoint handles JSON correctly

## Example Implementations

### PHP Endpoint

```php
<?php
// webhook-handler.php

header('Content-Type: application/json');

$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

if ($data['event'] === 'makan_bergizi_gratis.checkout') {
    $noTabungan = $data['data']['no_tabungan'];
    $namaLengkap = $data['data']['nasabah']['nama_lengkap'];
    
    // Process the checkout data
    // ... your business logic here
    
    http_response_code(200);
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Unknown event type']);
}
```

### Node.js Endpoint

```javascript
const express = require('express');
const app = express();

app.use(express.json());

app.post('/webhook/makan-bergizi', (req, res) => {
    const { event, data } = req.body;
    
    if (event === 'makan_bergizi_gratis.checkout') {
        const { no_tabungan, nasabah } = data;
        
        // Process the checkout data
        console.log(`Checkout: ${nasabah.nama_lengkap} - ${no_tabungan}`);
        
        res.json({ status: 'success' });
    } else {
        res.status(400).json({ error: 'Unknown event type' });
    }
});

app.listen(3000);
```

### Python Endpoint (Flask)

```python
from flask import Flask, request, jsonify

app = Flask(__name__)

@app.route('/webhook/makan-bergizi', methods=['POST'])
def webhook():
    data = request.json
    
    if data['event'] == 'makan_bergizi_gratis.checkout':
        no_tabungan = data['data']['no_tabungan']
        nama_lengkap = data['data']['nasabah']['nama_lengkap']
        
        # Process the checkout data
        print(f"Checkout: {nama_lengkap} - {no_tabungan}")
        
        return jsonify({'status': 'success'}), 200
    else:
        return jsonify({'error': 'Unknown event type'}), 400

if __name__ == '__main__':
    app.run(port=5000)
```

## Support

For issues or questions about webhook integration:
1. Check the Laravel logs: `storage/logs/laravel.log`
2. Review this documentation
3. Test with webhook.site to verify payload structure
4. Contact system administrator for configuration assistance
