# Autentikasi Fingerprint & PIN pada Aplikasi Mobile

Dokumentasi ini menjelaskan arsitektur dan alur autentikasi biometrik (fingerprint/Face ID) dan PIN untuk aplikasi mobile React Native yang terhubung ke backend Dash-Kospin (Laravel Sanctum).

---

## Konsep Dasar

Aplikasi mobile banking menggunakan **dua layer autentikasi** yang terpisah:

| Layer | Nama | Umur | Disimpan di | Tujuan |
|---|---|---|---|---|
| **Layer 1** | Auth Session (Sanctum Token) | 30 hari | Secure Storage device | Autentikasi ke server |
| **Layer 2** | App Session | Sampai idle 1 menit | In-memory (RAM) | Keamanan lokal di device |

> **Penting:** Fingerprint/PIN adalah mekanisme **client-side** sepenuhnya. Backend Laravel tidak tahu dan tidak perlu tahu apakah user membuka app dengan password, fingerprint, atau PIN.

---

## Arsitektur Storage di Device

```
┌─────────────────────────────────────────────────────────────┐
│                     STORAGE STRATEGY                         │
│                                                              │
│  ┌─────────────────────────────────────────┐                │
│  │  SECURE STORAGE (Keychain / Keystore)    │                │
│  │  ─────────────────────────────────────── │                │
│  │  • sanctum_token    : "abc123..."        │  ◄── Dienkripsi│
│  │  • pin_hash         : "hashed_pin"       │      & dilindungi
│  │  • biometric_enabled: true               │      biometrik │
│  └─────────────────────────────────────────┘                │
│                                                              │
│  ┌─────────────────────────────────────────┐                │
│  │  IN-MEMORY STATE (React State/Context)   │                │
│  │  ─────────────────────────────────────── │                │
│  │  • isUnlocked         : true/false       │  ◄── Volatile  │
│  │  • lastActivityTimestamp: 1723456789     │      (hilang   │
│  │  • userData           : {...}            │       saat app │
│  └─────────────────────────────────────────┘       ditutup) │
└─────────────────────────────────────────────────────────────┘
```

---

## Library yang Digunakan (React Native)

| Library | Fungsi |
|---|---|
| `react-native-biometrics` atau `expo-local-authentication` | Memverifikasi sidik jari / Face ID di device |
| `react-native-keychain` atau `expo-secure-store` | Menyimpan Sanctum token yang terenkripsi dan dikunci biometrik |

---

## Alur Lengkap

### Skenario 1: Login Pertama Kali (Email + Password)

Terjadi saat: app pertama kali diinstall, token expired, atau setelah logout manual.

```
User → Input email + password
     → POST /api/login {email, password}
     → Laravel return {token, user}
     → Simpan token ke Secure Storage (encrypted + biometric-protected)
     → Set isUnlocked = true, lastActivity = now()
     → Tampilkan opsi "Aktifkan login sidik jari?"
     → Masuk ke dashboard ✅
```

**Endpoint yang digunakan:**
```
POST /api/login
Body: { "email": "user@email.com", "password": "password123" }

Response 200:
{
  "status": true,
  "message": "Login successful",
  "data": {
    "user": { ... },
    "token": "1|abc123..."
  }
}
```

---

### Skenario 2: Idle Timeout (1 Menit Tanpa Aktivitas)

Terjadi saat: user tidak menyentuh layar selama 60 detik.

```
Timer berjalan di background React Native...
60 detik tanpa interaksi
→ Set isUnlocked = false
→ Tampilkan LOCK SCREEN (bukan login page!)
→ Token TETAP tersimpan di Secure Storage
→ Token TIDAK dihapus dari server
```

> **Kunci Perbedaan:** Idle timeout hanya mengunci tampilan app secara lokal. Token Sanctum di Secure Storage dan di server tetap valid.

---

### Skenario 3: Unlock dengan Fingerprint

Terjadi saat: app terkunci setelah idle, user scan sidik jari.

```
User → Scan sidik jari 👆
     → Biometric verify OK oleh OS device
     → Secure Storage terbuka → ambil token
     → GET /api/user (Bearer: token)
     → Laravel return 200 OK
     → Set isUnlocked = true, lastActivity = now()
     → Langsung masuk dashboard ✅ (tanpa ketik apapun)
```

**Endpoint yang digunakan:**
```
GET /api/user
Header: Authorization: Bearer 1|abc123...

Response 200:
{
  "id": 1,
  "name": "User Name",
  "email": "user@email.com",
  ...
}
```

---

### Skenario 4: Unlock dengan PIN

Terjadi saat: fingerprint tidak tersedia atau user memilih input PIN.

```
User → Input PIN (misal: "123456")
     → React Native bandingkan hash PIN lokal → cocok ✅
     → Ambil token dari Secure Storage
     → GET /api/user (Bearer: token)
     → Laravel return 200 OK
     → Set isUnlocked = true, lastActivity = now()
     → Masuk dashboard ✅
```

> **Catatan:** PIN di-hash dan disimpan di Secure Storage device. PIN **tidak dikirim** ke server.

---

### Skenario 5: Token Expired / Tidak Valid

Terjadi saat: token sudah melewati batas expiration (30 hari).

```
User → Buka app / scan fingerprint
     → Ambil token dari Secure Storage
     → GET /api/user (Bearer: token)
     → Laravel return 401 Unauthorized
     → Hapus token dari Secure Storage
     → Redirect ke LOGIN PAGE
     → Harus input email + password kembali
```

---

### Skenario 6: Logout Manual

Terjadi saat: user menekan tombol "Keluar".

```
User → Tekan "Keluar"
     → POST /api/logout (Bearer: token)
     → Laravel hapus token dari database
     → Hapus token dari Secure Storage device
     → Hapus PIN hash dari Secure Storage
     → Set isUnlocked = false
     → Redirect ke LOGIN PAGE
```

**Endpoint yang digunakan:**
```
POST /api/logout
Header: Authorization: Bearer 1|abc123...

Response 200:
{
  "status": true,
  "message": "Logout berhasil"
}
```

---

## State Machine Aplikasi

```
                    ┌──────────────┐
                    │   LOGGED     │
                    │    OUT       │◄──── Token expired / logout manual
                    └──────────────┘
                          │
                    email + password
                    POST /api/login
                          │
                          ▼
                    ┌──────────────┐
          ┌────────►│   UNLOCKED   │◄──────────────────┐
          │         │  (Dashboard) │                   │
          │         └──────────────┘                   │
          │               │                            │
     fingerprint/         │ idle 1 menit               │
     PIN berhasil         ▼                       fingerprint/
          │         ┌──────────────┐              PIN berhasil
          │         │    LOCKED    │──────────────┘
          └─────────│ (Lock Screen)│
                    └──────────────┘
                          │
                    3x fingerprint gagal
                    atau 3x PIN salah
                          │
                          ▼
                    ┌──────────────┐
                    │   LOGGED     │
                    │    OUT       │
                    └──────────────┘
```

---

## Perbedaan Lock Screen vs Login Page

| Aspek | Login Page | Lock Screen |
|---|---|---|
| **Kapan muncul** | Pertama kali / token expired / logout manual | Idle timeout 1 menit / app ditutup |
| **Token di device** | Tidak ada | Masih ada, tersimpan aman |
| **Input yang diminta** | Email + Password | Fingerprint ATAU PIN |
| **Request ke server** | `POST /api/login` | `GET /api/user` (verifikasi token) |
| **Buat token baru?** | Ya | Tidak, pakai token lama |
| **Koneksi internet** | Wajib ada | Wajib ada (untuk verifikasi token) |

---

## Timeline Satu Siklus Penggunaan

```
WAKTU     EVENT                              STATE
────────  ─────────────────────────────────  ─────────────────────────
00:00     Buka app pertama kali              Token: ❌  Unlocked: ❌
          → Tampilkan LOGIN PAGE

00:01     Login email + password
          POST /api/login → dapat token      Token: ✅  Unlocked: ✅
          → Simpan token ke SecureStore
          → Masuk dashboard

00:30     User browsing, ketuk layar         Token: ✅  Unlocked: ✅
          → Reset idle timer                 (timer reset ke 0)

01:31     User taruh HP
          → 60 detik tanpa aktivitas         Token: ✅  Unlocked: ❌
          → LOCK SCREEN muncul               (token TIDAK dihapus)

01:32     User scan jari 👆
          → Biometric OK
          → GET /api/user → 200 OK           Token: ✅  Unlocked: ✅
          → Langsung ke dashboard

01:45     User tutup app (kill app)
          → In-memory state hilang           Token: ✅  Unlocked: ❌

01:46     User buka app lagi
          → Cek SecureStore → ada token
          → Tampilkan LOCK SCREEN
          → Scan jari → langsung masuk       Token: ✅  Unlocked: ✅

Hari 31   Token expired (30 hari)
          → GET /api/user → 401              Token: ❌  Unlocked: ❌
          → Hapus token dari SecureStore
          → Tampilkan LOGIN PAGE
          → Harus input email + password
```

---

## API Endpoints yang Digunakan

Seluruh endpoint sudah tersedia di backend Dash-Kospin. Tidak diperlukan perubahan backend untuk mendukung fingerprint/PIN.

| Endpoint | Method | Auth | Dipakai saat |
|---|---|---|---|
| `/api/login` | POST | - | Login pertama kali (email + password) |
| `/api/logout` | POST | Bearer Token | Logout manual |
| `/api/user` | GET | Bearer Token | Verifikasi token setelah unlock fingerprint/PIN |
| `/api/update-password` | PATCH | Bearer Token | Ganti password |
| `/api/forgot-password` | POST | - | Lupa password |
| `/api/reset-password` | POST | - | Reset password via email |

---

## Konfigurasi Sanctum (Backend)

Saat ini `config/sanctum.php` dikonfigurasi dengan `expiration => null` (token tidak pernah expired). Disarankan mengubah ke nilai yang sesuai untuk keamanan:

```php
// config/sanctum.php
'expiration' => 43200,  // 30 hari dalam menit (30 * 24 * 60)
```

Dengan expiration 30 hari, user harus login ulang menggunakan email + password setiap 30 hari — sementara dalam 30 hari tersebut, fingerprint/PIN dapat digunakan untuk unlock setelah idle timeout.

---

## Keamanan

- **Token** tidak pernah disimpan di AsyncStorage biasa — selalu di Secure Storage (iOS Keychain / Android Keystore) yang dienkripsi oleh OS.
- **PIN** di-hash sebelum disimpan, tidak pernah disimpan plaintext, dan tidak pernah dikirim ke server.
- **Fingerprint** diverifikasi sepenuhnya oleh OS device (tidak ada data biometrik yang meninggalkan device).
- **3x kegagalan** fingerprint atau PIN secara berturut-turut harus me-redirect ke login page (email + password) sebagai fallback keamanan.
- **Logout manual** harus menghapus token dari Secure Storage device DAN dari server (via `POST /api/logout`).

---

## Permission Android & Kebijakan Google Play Store

### Permission yang Dibutuhkan

Fingerprint/biometrik pada Android hanya membutuhkan deklarasi di `AndroidManifest.xml`:

```xml
<!-- Untuk Android 9 (API 28) ke atas -->
<uses-permission android:name="android.permission.USE_BIOMETRIC" />

<!-- Untuk kompatibilitas Android di bawah API 28 -->
<uses-permission android:name="android.permission.USE_FINGERPRINT" />
```

### Klasifikasi Permission

| Kategori | Contoh | Perlu Popup ke User? | Perlu Approval Google? |
|---|---|---|---|
| **Normal Permission** | `USE_BIOMETRIC`, `USE_FINGERPRINT`, `INTERNET` | ❌ Tidak | ❌ Tidak |
| **Dangerous Permission** | `CAMERA`, `LOCATION`, `CONTACTS`, `SMS`, `MICROPHONE` | ✅ Ya | ⚠️ Tergantung penggunaan |
| **Special Permission** | `SYSTEM_ALERT_WINDOW`, `MANAGE_EXTERNAL_STORAGE` | ✅ Ya | ✅ Ya (harus ada justifikasi) |

`USE_BIOMETRIC` termasuk **Normal Permission** — otomatis diberikan saat install, tidak muncul popup izin ke user, dan tidak memerlukan review khusus dari Google Play Store.

### Mengapa Tidak Butuh Approval Khusus?

```
┌───────────────────────────────────────────────────┐
│                   ANDROID OS                       │
│                                                    │
│  ┌─────────────┐    ┌──────────────────────────┐  │
│  │ App (React  │───►│ BiometricPrompt API      │  │
│  │ Native)     │    │ (System UI — bawaan OS)  │  │
│  │             │    └──────────┬───────────────┘  │
│  │             │               │                   │
│  │             │    ┌──────────▼───────────────┐  │
│  │             │    │ TEE / Secure Enclave     │  │
│  │             │    │ (Hardware-level)         │  │
│  │             │◄───│ Return: ✅ atau ❌        │  │
│  │             │    │ (bukan data biometrik)   │  │
│  └─────────────┘    └──────────────────────────┘  │
│                                                    │
│  App TIDAK bisa mengakses data sidik jari mentah   │
└───────────────────────────────────────────────────┘
```

1. **Data biometrik tidak pernah meninggalkan device** — Aplikasi hanya menerima hasil (`berhasil` / `gagal`), tidak pernah mengakses data sidik jari mentah.
2. **Menggunakan System UI** — BiometricPrompt API menampilkan dialog bawaan Android, bukan UI custom dari app.
3. **Tidak ada data sensitif yang dikumpulkan** — Karena app tidak mengumpulkan data biometrik, tidak ada pelanggaran kebijakan privasi Google Play.

### Bagaimana Permission Ditambahkan di React Native

| Library | Permission Otomatis? | Keterangan |
|---|---|---|
| **`expo-local-authentication`** | ✅ Ya | Plugin Expo menambahkan permission ke manifest secara otomatis melalui config plugin |
| **`react-native-biometrics`** | ⚠️ Manual | Perlu tambahkan sendiri `USE_BIOMETRIC` dan `USE_FINGERPRINT` di `AndroidManifest.xml` |

### Hal yang Perlu Dipenuhi di Google Play Store

Meskipun biometrik sendiri tidak butuh approval khusus, beberapa hal berikut tetap wajib dipenuhi:

| Aspek | Kewajiban |
|---|---|
| **Privacy Policy** | Wajib ada — menjelaskan bahwa app menggunakan autentikasi biometrik lokal dan tidak mengumpulkan data biometrik |
| **Data Safety** di Play Console | Deklarasikan bahwa app **tidak mengumpulkan** data biometrik. Pilih "No" pada bagian *Biometric data collection* |
| **Target API Level** | Gunakan `USE_BIOMETRIC` (API 28+) dan `USE_FINGERPRINT` sebagai fallback untuk Android lama |
| **Dangerous Permission lain** | Jika app juga pakai `CAMERA`, `LOCATION`, dll — permission tersebut yang butuh justifikasi, bukan biometriknya |

### Ringkasan Compliance Google Play

| Pertanyaan | Jawaban |
|---|---|
| Apakah fingerprint butuh permission Android? | Ya, tapi hanya **Normal Permission** (`USE_BIOMETRIC`) |
| Apakah muncul popup izin ke user? | ❌ Tidak, otomatis diberikan saat install |
| Apakah butuh review/approval Google Play? | ❌ Tidak ada review khusus untuk fitur biometrik |
| Apakah data sidik jari dikirim ke server? | ❌ Tidak pernah — semua proses di device |
| Apakah perlu Privacy Policy? | ✅ Ya — wajib untuk semua app di Play Store |
| Apakah termasuk Dangerous Permission? | ❌ Tidak — berbeda dengan CAMERA, LOCATION, SMS |
