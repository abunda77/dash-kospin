<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePenarikanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_tabungan' => ['required', 'integer', 'exists:tabungans,id'],
            'jumlah' => [
                'required',
                'integer',
                'min:'.(int) config('penarikan.minimal_jumlah', 10000),
                'max:'.(int) config('penarikan.maksimal_jumlah', 100000000),
            ],
            'bank' => ['required', 'string', 'in:BRI,BNI,BCA,MANDIRI,BSI,BTPN,LAINNYA'],
            'nama_bank' => ['required', 'string', 'max:255'],
            'nama_nasabah' => ['required', 'string', 'max:255'],
            'referensi_penarikan' => ['nullable', 'string', 'max:100'],
            'bukti_penarikan' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'catatan_pengguna' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_tabungan.required' => 'Rekening tabungan sumber wajib dipilih.',
            'id_tabungan.exists' => 'Rekening tabungan tidak ditemukan.',
            'jumlah.required' => 'Nominal penarikan wajib diisi.',
            'jumlah.integer' => 'Nominal penarikan harus berupa bilangan bulat.',
            'jumlah.min' => 'Nominal penarikan minimal Rp '.number_format((int) config('penarikan.minimal_jumlah', 10000), 0, ',', '.').'.',
            'jumlah.max' => 'Nominal penarikan maksimal Rp '.number_format((int) config('penarikan.maksimal_jumlah', 100000000), 0, ',', '.').'.',
            'bank.required' => 'Bank tujuan wajib dipilih.',
            'bank.in' => 'Bank yang dipilih tidak tersedia.',
            'nama_bank.required' => 'Nama bank wajib diisi.',
            'nama_nasabah.required' => 'Nama nasabah wajib diisi.',
            'bukti_penarikan.file' => 'Dokumen pendukung tidak valid.',
            'bukti_penarikan.mimes' => 'Dokumen pendukung harus berformat JPG, PNG, atau PDF.',
            'bukti_penarikan.max' => 'Ukuran dokumen pendukung maksimal 4 MB.',
        ];
    }
}
