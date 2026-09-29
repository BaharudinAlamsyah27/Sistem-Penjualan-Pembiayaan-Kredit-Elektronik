<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PelangganRequest extends FormRequest
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
        $row = $this->route('pelanggan');
        $branch = $this->input('cabang_id');

        return ['cabang_id' => 'required|exists:cabangs,id', 'kode' => ['required', 'string', 'max:30', Rule::unique('pelanggans', 'kode')->where('cabang_id', $branch)->ignore($row?->id)], 'nama' => 'required|string|max:100', 'no_hp' => 'nullable|string|max:30', 'no_ktp' => 'nullable|string|max:30', 'alamat' => 'required|string', 'kabupaten' => 'required|string|max:100', 'kecamatan' => 'required|string|max:100', 'catatan' => 'nullable|string'];
    }
}
