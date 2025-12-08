<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permintaan Akses Aplikasi Mobile</title>
</head>
<body style="font-family: 'Inter', 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f6f9;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff;">
        <!-- Header -->
        <tr>
            <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;">
                <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700;">
                    📱 Permintaan Akses Aplikasi Mobile
                </h1>
                <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0; font-size: 14px;">
                    Uji Coba Tertutup - KOSPIN Sinara Artha
                </p>
            </td>
        </tr>

        <!-- Content -->
        <tr>
            <td style="padding: 40px 30px;">
                <p style="margin: 0 0 20px; font-size: 16px; color: #555;">
                    Halo Admin,
                </p>
                
                <p style="margin: 0 0 25px; font-size: 16px; color: #555;">
                    Ada pengguna yang mengajukan permintaan untuk mendapatkan akses uji coba aplikasi mobile KOSPIN Sinara Artha.
                </p>

                <!-- User Info Card -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: linear-gradient(135deg, #f6f8fb 0%, #f1f3f8 100%); border-radius: 12px; margin-bottom: 25px; border-left: 4px solid #667eea;">
                    <tr>
                        <td style="padding: 25px;">
                            <h3 style="color: #333; margin: 0 0 15px; font-size: 16px; font-weight: 600;">
                                📋 Detail Pengguna
                            </h3>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="padding: 8px 0; font-size: 14px; color: #666; width: 120px;">Nama</td>
                                    <td style="padding: 8px 0; font-size: 14px; color: #333; font-weight: 600;">{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; font-size: 14px; color: #666; width: 120px;">Email</td>
                                    <td style="padding: 8px 0; font-size: 14px; color: #333; font-weight: 600;">
                                        <a href="mailto:{{ $user->email }}" style="color: #667eea; text-decoration: none;">{{ $user->email }}</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; font-size: 14px; color: #666; width: 120px;">User ID</td>
                                    <td style="padding: 8px 0; font-size: 14px; color: #333; font-weight: 600;">#{{ $user->id }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; font-size: 14px; color: #666; width: 120px;">Waktu Permintaan</td>
                                    <td style="padding: 8px 0; font-size: 14px; color: #333; font-weight: 600;">{{ $requestedAt }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Action Section -->
                <div style="background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
                    <p style="margin: 0; font-size: 14px; color: #856404;">
                        ⚡ <strong>Tindakan yang Diperlukan:</strong><br>
                        Silakan hubungi pengguna ini untuk memberikan akses ke aplikasi mobile (APK atau link TestFlight/Firebase App Distribution).
                    </p>
                </div>

                <p style="margin: 0; font-size: 14px; color: #888;">
                    Email ini dikirim secara otomatis oleh sistem KOSPIN Sinara Artha.
                </p>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background-color: #f8fafc; padding: 25px 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                <p style="margin: 0 0 5px; font-size: 13px; color: #9ca3af;">
                    &copy; {{ date('Y') }} Koperasi Simpan Pinjam Sinara Artha
                </p>
                <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                    Sistem Administrasi & Manajemen Koperasi
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
