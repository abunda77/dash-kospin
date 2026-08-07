<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KirimRevisiPenarikanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'referensi_penarikan' => ['nullable', 'string', 'max:100'],
            'bukti_penarikan' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'catatan_pengguna' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'bukti_penarikan.file' => 'Dokumen pendukung tidak valid.',
            'bukti_penarikan.mimes' => 'Dokumen pendukung harus berformat JPG, PNG, atau PDF.',
            'bukti_penarikan.max' => 'Ukuran dokumen pendukung maksimal 4 MB.',
        ];
    }
}
