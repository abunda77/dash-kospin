<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KlaimSetoranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'waktu_klaim_bayar' => ['required', 'date'],
            'nama_pembayar' => ['required', 'string', 'max:255'],
            'referensi_pembayaran' => ['nullable', 'string', 'max:100'],
            'bukti_pembayaran' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'catatan_pengguna' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'waktu_klaim_bayar.required' => 'Waktu bayar wajib diisi.',
            'waktu_klaim_bayar.date' => 'Format waktu bayar tidak valid.',
            'nama_pembayar.required' => 'Nama pengirim / pembayar wajib diisi.',
            'bukti_pembayaran.file' => 'Bukti pembayaran tidak valid.',
            'bukti_pembayaran.mimes' => 'Bukti pembayaran harus berformat JPG, PNG, atau PDF.',
            'bukti_pembayaran.max' => 'Ukuran bukti pembayaran maksimal 4 MB.',
        ];
    }
}
