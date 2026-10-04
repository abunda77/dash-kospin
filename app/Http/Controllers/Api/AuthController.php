<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendResetPasswordEmail;
use App\Models\User;
use App\Services\BlacklistService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
// use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRules;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 5;

    private const MAX_LOGIN_ATTEMPTS_PER_IP = 20;

    private const LOGIN_LOCKOUT_SECONDS = 300;

    /**
     * Tolak email yang domainnya masuk blacklist.
     *
     * Dicek di luar array rules agar Scramble tetap bisa membuat dokumentasi request.
     */
    private function ensureEmailDomainNotBlacklisted(string $email): void
    {
        if (app(BlacklistService::class)->isBlacklistedEmailDomain($email)) {
            throw ValidationException::withMessages([
                'email' => ['Unknown occurs'],
            ]);
        }
    }

    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'password_confirmation' => 'required|same:password',
            ]);

            $this->ensureEmailDomainNotBlacklisted($validatedData['email']);

            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
            ]);

            $user->assignRole('panel_user');

            $token = $user->createToken('API Token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            $emailKey = 'api-login:'.Str::lower($validatedData['email']).'|'.$request->ip();
            $ipKey = 'api-login:ip:'.$request->ip();

            if (RateLimiter::tooManyAttempts($emailKey, self::MAX_LOGIN_ATTEMPTS)
                || RateLimiter::tooManyAttempts($ipKey, self::MAX_LOGIN_ATTEMPTS_PER_IP)) {
                $seconds = max(1, RateLimiter::availableIn($emailKey), RateLimiter::availableIn($ipKey));

                return response()->json([
                    'status' => false,
                    'message' => 'Terlalu banyak percobaan login. Coba lagi dalam '.ceil($seconds / 60).' menit.',
                ], 429)->header('Retry-After', (string) $seconds);
            }

            $user = User::where('email', $validatedData['email'])->first();

            if (! $user || ! Hash::check($validatedData['password'], $user->password)) {
                RateLimiter::hit($emailKey, self::LOGIN_LOCKOUT_SECONDS);
                RateLimiter::hit($ipKey, self::LOGIN_LOCKOUT_SECONDS);

                return response()->json([
                    'status' => false,
                    'message' => 'Invalid credentials',
                ], 401);
            }

            RateLimiter::clear($emailKey);
            RateLimiter::clear($ipKey);

            $token = $user->createToken('API Token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Hapus token akses saat ini
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => true,
                'message' => 'Logout berhasil',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Logout gagal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'old_password' => ['required', 'string'],
                'new_password' => ['required', 'string', 'min:8', 'confirmed', PasswordRules::defaults()],
                'new_password_confirmation' => ['required', 'string'],
            ], [
                'old_password.required' => 'Password lama wajib diisi',
                'new_password.required' => 'Password baru wajib diisi',
                'new_password.min' => 'Password baru minimal 8 karakter',
                'new_password.confirmed' => 'Konfirmasi password tidak cocok',
                'new_password_confirmation.required' => 'Konfirmasi password wajib diisi',
            ]);

            $user = $request->user();

            if (! Hash::check($validatedData['old_password'], $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Password lama tidak sesuai',
                ], 422);
            }

            $user->update([
                'password' => Hash::make($validatedData['new_password']),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Password berhasil diperbarui',
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui password',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'email' => 'required|email',
            ], [
                'email.required' => 'Email wajib diisi',
                'email.email' => 'Format email tidak valid',
            ]);

            $this->ensureEmailDomainNotBlacklisted($validatedData['email']);

            // Selalu balas pesan generik agar keberadaan email tidak terbocorkan.
            if (User::where('email', $validatedData['email'])->exists()) {
                SendResetPasswordEmail::dispatch($validatedData['email']);
            }

            return response()->json([
                'status' => true,
                'message' => 'Jika email terdaftar, link reset password akan dikirim.',
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memproses permintaan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'token' => 'required',
                'email' => 'required|email|exists:users',
                'password' => ['required', 'confirmed', PasswordRules::min(8)
                    // ->mixedCase()
                    ->letters()
                    ->numbers()],
                // ->symbols()],
                'password_confirmation' => 'required',
            ], [
                'token.required' => 'Token tidak valid',
                'email.required' => 'Email wajib diisi',
                'email.email' => 'Format email tidak valid',
                'email.exists' => 'Email tidak terdaftar',
                'password.required' => 'Password baru wajib diisi',
                'password.confirmed' => 'Konfirmasi password tidak cocok',
                'password_confirmation.required' => 'Konfirmasi password wajib diisi',
            ]);

            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                    ])->setRememberToken(Str::random(60));

                    $user->save();

                    event(new PasswordReset($user));
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'status' => true,
                    'message' => 'Password berhasil direset',
                ], 200);
            }

            return response()->json([
                'status' => false,
                'message' => trans($status),
            ], 400);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan pada server',
                'error' => [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
            ], 500);
        }
    }
}
