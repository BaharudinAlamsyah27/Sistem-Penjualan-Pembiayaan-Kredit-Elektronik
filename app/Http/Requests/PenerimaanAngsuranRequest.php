<?php

namespace App\Http\Requests;

use App\Models\Angsuran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PenerimaanAngsuranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(['admin_pusat', 'admin_cabang']) ?? false;
    }

    public function rules(): array
    {
        /** @var Angsuran|null $angsuran */
        $angsuran = $this->route('angsuran');

        return [
            'tgl_bayar' => ['required', 'date'],
            'metode' => ['required', 'string', 'max:50'],
            'kolektor_id' => [
                'nullable',
                Rule::exists('karyawans', 'id')->where(fn ($query) => $query
                    ->where('cabang_id', $angsuran?->cabang_id)
                    ->where('jabatan', 'kolektor')
                    ->where('aktif', true)),
            ],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'tgl_bayar.required' => 'Tanggal pembayaran wajib diisi.',
            'metode.required' => 'Metode pembayaran wajib dipilih.',
            'kolektor_id.exists' => 'Kolektor tidak aktif atau bukan dari cabang terkait.',
        ];
    }
}
