<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PembelianRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->user()?->hasRole('admin_pusat')) {
            $this->merge(['cabang_id' => $this->user()?->cabang_id]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['cabang_id' => 'required|exists:cabangs,id', 'tanggal' => 'required|date', 'supplier' => 'required|string|max:100', 'metode' => 'required|in:cashmoney,tempo', 'items' => 'required|array|min:1', 'items.*.barang_id' => 'required|distinct|exists:barangs,id', 'items.*.qty' => 'required|integer|min:1|max:100000', 'items.*.harga' => ['required', 'numeric', 'min:0', 'max:9999999999999.99', 'regex:/^\d{1,13}(?:\.\d{1,2})?$/']];
    }
}
