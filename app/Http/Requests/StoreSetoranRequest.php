<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSetoranRequest extends FormRequest
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
                'min:'.(int) config('setoran.minimal_jumlah', 10000),
                'max:'.(int) config('setoran.maksimal_jumlah', 100000000),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'id_tabungan.required' => 'Rekening tabungan tujuan wajib dipilih.',
            'id_tabungan.exists' => 'Rekening tabungan tidak ditemukan.',
            'jumlah.required' => 'Nominal setoran wajib diisi.',
            'jumlah.integer' => 'Nominal setoran harus berupa bilangan bulat.',
            'jumlah.min' => 'Nominal setoran minimal Rp '.number_format((int) config('setoran.minimal_jumlah', 10000), 0, ',', '.').'.',
            'jumlah.max' => 'Nominal setoran maksimal Rp '.number_format((int) config('setoran.maksimal_jumlah', 100000000), 0, ',', '.').'.',
        ];
    }
}
