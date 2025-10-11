# 🔌 Panduan Integrasi QR Barcode dengan Aplikasi External

## 📋 Overview

Dokumentasi ini menjelaskan cara mengintegrasikan sistem QR Barcode Tabungan dengan aplikasi external (mobile app, web app, atau scanner device).

---

## 🎯 Use Case

### **Skenario 1: Mobile App Scanner**
Aplikasi mobile yang scan QR code dan langsung submit ke API.

### **Skenario 2: Web-based Scanner**
Web application yang menggunakan kamera device untuk scan QR code.

### **Skenario 3: Hardware Scanner**
Barcode scanner hardware yang terhubung ke sistem POS/kasir.

---

## 🔐 API Endpoints

### **Base URL**
```
Production: https://your-domain.com/api
Development: http://localhost:8000/api
```

### **Authentication**
Saat ini API tidak memerlukan authentication. Untuk production, disarankan menambahkan:
- API Token (Laravel Sanctum)
- Rate Limiting
- IP Whitelist

---

## 📡 API Reference

### **1. Check Today**
Cek apakah nomor tabungan sudah tercatat hari ini.

**Endpoint:**
```
POST /api/makan-bergizi-gratis/check-today
```

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "no_tabungan": "TAB-001"
}
```

**Response (200 - Available):**
```json
{
  "success": true,
  "data": {
    "no_tabungan": "TAB-001",
    "tanggal": "10/10/2025",
    "exists": false,
    "status": "available"
  }
}
```

**Response (200 - Already Recorded):**
```json
{
  "success": true,
  "data": {
    "no_tabungan": "TAB-001",
    "tanggal": "10/10/2025",
    "exists": true,
    "status": "already_recorded"
  }
}
```

**Response (422 - Validation Error):**
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "no_tabungan": [
      "The no tabungan field is required."
    ]
  }
}
```

---

### **2. Store Record**
Mencatat data baru untuk Makan Bergizi Gratis.

**Endpoint:**
```
POST /api/makan-bergizi-gratis
```

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "no_tabungan": "TAB-001"
}
```

**Response (201 - Success):**
```json
{
  "success": true,
  "message": "Data Makan Bergizi Gratis berhasil dicatat",
  "data": {
    "id": 1,
    "no_tabungan": "TAB-001",
    "tanggal_pemberian": "10/10/2025",
    "rekening": {
      "no_tabungan": "TAB-001",
      "produk": "Mitra Sinara",
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
    "produk_detail": {
      "id": 1,
      "nama": "Mitra Sinara",
      "keterangan": "Tabungan reguler"
    },
    "transaksi_terakhir": {
      "kode_transaksi": "TRX-001",
      "jenis_transaksi": "setoran",
      "jenis_transaksi_label": "Setoran",
      "jumlah": 100000,
      "jumlah_formatted": "Rp 100.000",
      "tanggal_transaksi": "09/10/2025 10:30:00",
      "tanggal_transaksi_iso": "2025-10-09T10:30:00.000000Z",
      "keterangan": "Setoran tunai",
      "teller": "Admin User"
    },
    "metadata": {
      "scanned_at": "2025-10-10T08:00:00.000000Z",
      "scanned_at_formatted": "10/10/2025 08:00:00"
    }
  }
}
```

**Response (409 - Already Exists):**
```json
{
  "success": false,
  "message": "Data untuk nomor tabungan ini sudah tercatat hari ini",
  "data": {
    "no_tabungan": "TAB-001",
    "tanggal": "10/10/2025",
    "status": "already_recorded"
  }
}
```

**Response (422 - Invalid Account):**
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "no_tabungan": [
      "The selected no tabungan is invalid."
    ]
  }
}
```

**Response (500 - Server Error):**
```json
{
  "success": false,
  "message": "Terjadi kesalahan saat menyimpan data",
  "error": "Error details..."
}
```

---

### **3. Get Records**
Mendapatkan list records dengan filter.

**Endpoint:**
```
GET /api/makan-bergizi-gratis
```

**Query Parameters:**
```
per_page      : Number of records per page (default: 15)
tanggal       : Filter by specific date (format: Y-m-d)
dari          : Filter from date (format: Y-m-d)
sampai        : Filter to date (format: Y-m-d)
no_tabungan   : Filter by account number
```

**Example:**
```
GET /api/makan-bergizi-gratis?per_page=10&tanggal=2025-10-10
GET /api/makan-bergizi-gratis?dari=2025-10-01&sampai=2025-10-31
GET /api/makan-bergizi-gratis?no_tabungan=TAB-001
```

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "no_tabungan": "TAB-001",
      "tanggal_pemberian": "10/10/2025",
      "rekening": { ... },
      "nasabah": { ... },
      "produk_detail": { ... },
      "transaksi_terakhir": { ... },
      "metadata": { ... }
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/makan-bergizi-gratis?page=1",
    "last": "http://localhost:8000/api/makan-bergizi-gratis?page=5",
    "prev": null,
    "next": "http://localhost:8000/api/makan-bergizi-gratis?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "to": 15,
    "total": 75
  }
}
```

---

### **4. Get Single Record**
Mendapatkan detail single record.

**Endpoint:**
```
GET /api/makan-bergizi-gratis/{id}
```

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "no_tabungan": "TAB-001",
    "tanggal_pemberian": "10/10/2025",
    "rekening": { ... },
    "nasabah": { ... },
    "produk_detail": { ... },
    "transaksi_terakhir": { ... },
    "metadata": { ... }
  }
}
```

**Response (404):**
```json
{
  "message": "Record not found"
}
```

---

## 💻 Implementation Examples

### **JavaScript (Fetch API)**

```javascript
// Check Today
async function checkToday(noTabungan) {
  try {
    const response = await fetch('http://localhost:8000/api/makan-bergizi-gratis/check-today', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ no_tabungan: noTabungan })
    });
    
    const data = await response.json();
    
    if (response.ok) {
      console.log('Status:', data.data.status);
      return data.data.exists;
    } else {
      console.error('Error:', data.message);
      return null;
    }
  } catch (error) {
    console.error('Network error:', error);
    return null;
  }
}

// Store Record
async function storeRecord(noTabungan) {
  try {
    const response = await fetch('http://localhost:8000/api/makan-bergizi-gratis', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ no_tabungan: noTabungan })
    });
    
    const data = await response.json();
    
    if (response.ok) {
      console.log('Success:', data.message);
      console.log('Data:', data.data);
      return data.data;
    } else {
      console.error('Error:', data.message);
      return null;
    }
  } catch (error) {
    console.error('Network error:', error);
    return null;
  }
}

// Usage with QR Scanner
function onQRCodeScanned(qrData) {
  const noTabungan = qrData.trim();
  
  // Check if already recorded today
  checkToday(noTabungan).then(exists => {
    if (exists) {
      alert('Data sudah tercatat hari ini!');
    } else {
      // Store new record
      storeRecord(noTabungan).then(result => {
        if (result) {
          alert('Data berhasil dicatat!');
          console.log('Nasabah:', result.nasabah.nama_lengkap);
        }
      });
    }
  });
}
```

---

### **React Native**

```javascript
import React, { useState } from 'react';
import { View, Text, Button, Alert } from 'react-native';
import { BarCodeScanner } from 'expo-barcode-scanner';

const API_BASE_URL = 'http://your-domain.com/api';

export default function QRScannerScreen() {
  const [hasPermission, setHasPermission] = useState(null);
  const [scanned, setScanned] = useState(false);

  React.useEffect(() => {
    (async () => {
      const { status } = await BarCodeScanner.requestPermissionsAsync();
      setHasPermission(status === 'granted');
    })();
  }, []);

  const handleBarCodeScanned = async ({ type, data }) => {
    setScanned(true);
    
    const noTabungan = data.trim();
    
    try {
      // Check today
      const checkResponse = await fetch(`${API_BASE_URL}/makan-bergizi-gratis/check-today`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ no_tabungan: noTabungan })
      });
      
      const checkData = await checkResponse.json();
      
      if (checkData.data.exists) {
        Alert.alert('Info', 'Data sudah tercatat hari ini!');
        setScanned(false);
        return;
      }
      
      // Store record
      const storeResponse = await fetch(`${API_BASE_URL}/makan-bergizi-gratis`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ no_tabungan: noTabungan })
      });
      
      const storeData = await storeResponse.json();
      
      if (storeResponse.ok) {
        Alert.alert(
          'Berhasil!',
          `Data ${storeData.data.nasabah.nama_lengkap} berhasil dicatat`,
          [{ text: 'OK', onPress: () => setScanned(false) }]
        );
      } else {
        Alert.alert('Error', storeData.message);
        setScanned(false);
      }
      
    } catch (error) {
      Alert.alert('Error', 'Terjadi kesalahan koneksi');
      setScanned(false);
    }
  };

  if (hasPermission === null) {
    return <Text>Requesting camera permission...</Text>;
  }
  if (hasPermission === false) {
    return <Text>No access to camera</Text>;
  }

  return (
    <View style={{ flex: 1 }}>
      <BarCodeScanner
        onBarCodeScanned={scanned ? undefined : handleBarCodeScanned}
        style={{ flex: 1 }}
      />
      {scanned && (
        <Button title="Scan Lagi" onPress={() => setScanned(false)} />
      )}
    </View>
  );
}
```

---

### **Flutter (Dart)**

```dart
import 'package:flutter/material.dart';
import 'package:qr_code_scanner/qr_code_scanner.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class QRScannerScreen extends StatefulWidget {
  @override
  _QRScannerScreenState createState() => _QRScannerScreenState();
}

class _QRScannerScreenState extends State<QRScannerScreen> {
  final GlobalKey qrKey = GlobalKey(debugLabel: 'QR');
  QRViewController? controller;
  String apiBaseUrl = 'http://your-domain.com/api';
  bool isProcessing = false;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Scan QR Code')),
      body: QRView(
        key: qrKey,
        onQRViewCreated: _onQRViewCreated,
      ),
    );
  }

  void _onQRViewCreated(QRViewController controller) {
    this.controller = controller;
    controller.scannedDataStream.listen((scanData) {
      if (!isProcessing) {
        _handleQRCode(scanData.code ?? '');
      }
    });
  }

  Future<void> _handleQRCode(String qrData) async {
    setState(() => isProcessing = true);
    
    final noTabungan = qrData.trim();
    
    try {
      // Check today
      final checkResponse = await http.post(
        Uri.parse('$apiBaseUrl/makan-bergizi-gratis/check-today'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({'no_tabungan': noTabungan}),
      );
      
      final checkData = jsonDecode(checkResponse.body);
      
      if (checkData['data']['exists']) {
        _showDialog('Info', 'Data sudah tercatat hari ini!');
        setState(() => isProcessing = false);
        return;
      }
      
      // Store record
      final storeResponse = await http.post(
        Uri.parse('$apiBaseUrl/makan-bergizi-gratis'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({'no_tabungan': noTabungan}),
      );
      
      final storeData = jsonDecode(storeResponse.body);
      
      if (storeResponse.statusCode == 201) {
        _showDialog(
          'Berhasil!',
          'Data ${storeData['data']['nasabah']['nama_lengkap']} berhasil dicatat',
        );
      } else {
        _showDialog('Error', storeData['message']);
      }
      
    } catch (e) {
      _showDialog('Error', 'Terjadi kesalahan koneksi');
    }
    
    setState(() => isProcessing = false);
  }

  void _showDialog(String title, String message) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(title),
        content: Text(message),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text('OK'),
          ),
        ],
      ),
    );
  }

  @override
  void dispose() {
    controller?.dispose();
    super.dispose();
  }
}
```

---

### **PHP (cURL)**

```php
<?php

class MakanBergizisGratisAPI {
    private $baseUrl;
    
    public function __construct($baseUrl = 'http://localhost:8000/api') {
        $this->baseUrl = $baseUrl;
    }
    
    public function checkToday($noTabungan) {
        $url = $this->baseUrl . '/makan-bergizi-gratis/check-today';
        $data = ['no_tabungan' => $noTabungan];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'status_code' => $httpCode,
            'data' => json_decode($response, true)
        ];
    }
    
    public function storeRecord($noTabungan) {
        $url = $this->baseUrl . '/makan-bergizi-gratis';
        $data = ['no_tabungan' => $noTabungan];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'status_code' => $httpCode,
            'data' => json_decode($response, true)
        ];
    }
    
    public function handleQRScan($qrData) {
        $noTabungan = trim($qrData);
        
        // Check if already recorded today
        $checkResult = $this->checkToday($noTabungan);
        
        if ($checkResult['status_code'] !== 200) {
            return [
                'success' => false,
                'message' => 'Error checking data'
            ];
        }
        
        if ($checkResult['data']['data']['exists']) {
            return [
                'success' => false,
                'message' => 'Data sudah tercatat hari ini'
            ];
        }
        
        // Store new record
        $storeResult = $this->storeRecord($noTabungan);
        
        if ($storeResult['status_code'] === 201) {
            return [
                'success' => true,
                'message' => 'Data berhasil dicatat',
                'data' => $storeResult['data']['data']
            ];
        }
        
        return [
            'success' => false,
            'message' => $storeResult['data']['message'] ?? 'Error storing data'
        ];
    }
}

// Usage
$api = new MakanBergizisGratisAPI();

// Simulate QR scan
$qrData = 'TAB-001';
$result = $api->handleQRScan($qrData);

if ($result['success']) {
    echo "Berhasil: " . $result['message'] . "\n";
    echo "Nasabah: " . $result['data']['nasabah']['nama_lengkap'] . "\n";
} else {
    echo "Gagal: " . $result['message'] . "\n";
}
```

---

## 🔒 Security Recommendations

### **For Production:**

1. **Add Authentication**
```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/makan-bergizi-gratis', [MakanBergizisGratisController::class, 'store']);
    // ... other routes
});
```

2. **Rate Limiting**
```php
// app/Http/Kernel.php
'api' => [
    'throttle:60,1', // 60 requests per minute
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

3. **IP Whitelist**
```php
// middleware
if (!in_array($request->ip(), config('app.allowed_ips'))) {
    abort(403, 'Unauthorized IP');
}
```

4. **HTTPS Only**
```php
// AppServiceProvider
if (app()->environment('production')) {
    URL::forceScheme('https');
}
```

---

## 📊 Error Handling Best Practices

```javascript
async function handleQRScan(qrData) {
  try {
    // Validate QR data format
    if (!qrData || !qrData.startsWith('TAB-')) {
      throw new Error('Invalid QR code format');
    }
    
    // Check today
    const checkResponse = await fetch(API_URL + '/check-today', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ no_tabungan: qrData })
    });
    
    if (!checkResponse.ok) {
      throw new Error(`HTTP ${checkResponse.status}`);
    }
    
    const checkData = await checkResponse.json();
    
    if (checkData.data.exists) {
      return {
        status: 'already_recorded',
        message: 'Data sudah tercatat hari ini'
      };
    }
    
    // Store record
    const storeResponse = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ no_tabungan: qrData })
    });
    
    if (!storeResponse.ok) {
      const errorData = await storeResponse.json();
      throw new Error(errorData.message || 'Failed to store record');
    }
    
    const storeData = await storeResponse.json();
    
    return {
      status: 'success',
      message: 'Data berhasil dicatat',
      data: storeData.data
    };
    
  } catch (error) {
    console.error('QR Scan Error:', error);
    
    return {
      status: 'error',
      message: error.message || 'Terjadi kesalahan'
    };
  }
}
```

---

## 🎓 Best Practices

1. **Always validate QR data before sending to API**
2. **Handle network errors gracefully**
3. **Show clear feedback to users**
4. **Implement retry mechanism for failed requests**
5. **Cache API responses when appropriate**
6. **Log errors for debugging**
7. **Test with various network conditions**
8. **Implement offline mode if needed**

---

**Last Updated:** 10 Oktober 2025
**Version:** 1.0.0
