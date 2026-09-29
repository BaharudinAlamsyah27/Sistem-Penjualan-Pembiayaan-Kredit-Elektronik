<?php

namespace App\Http\Requests;

use App\Models\Cabang;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BarangRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['aktif' => $this->boolean('aktif')]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $row = $this->route('barang');

        return [
            'kode' => ['required', 'string', 'max:30', Rule::unique('barangs', 'kode')->ignore($row?->id)],
            'nama' => ['required', 'string', 'max:150'],
            'merk' => ['nullable', 'string', 'max:100'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'harga_beli' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'harga_jual' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'aktif' => ['boolean'],
            'stok' => ['required', 'array', 'min:1', function ($attribute, $value, $fail) {
                $cabangIds = array_values(array_unique(array_map('intval', array_keys($value))));
                if (Cabang::whereIn('id', $cabangIds)->count() !== count($cabangIds)) {
                    $fail('Cabang untuk saldo stok tidak valid.');
                    return;
                }

                $user = $this->user();
                if (! $user->hasRole('admin_pusat') && $cabangIds !== [(int) $user->cabang_id]) {
                    $fail('Anda hanya dapat mengubah stok cabang sendiri.');
                }
            }],
            'stok.*' => ['required', 'integer', 'min:0', 'max:99999999'],
        ];
    }
}
