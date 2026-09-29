<?php

namespace App\Http\Requests;

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

        return ['kode' => ['required', 'string', 'max:30', Rule::unique('barangs', 'kode')->ignore($row?->id)], 'nama' => 'required|string|max:150', 'merk' => 'nullable|string|max:100', 'kategori' => 'nullable|string|max:100', 'harga_beli' => 'required|numeric|min:0|max:9999999999999.99', 'harga_jual' => 'required|numeric|min:0|max:9999999999999.99', 'aktif' => 'boolean'];
    }
}
