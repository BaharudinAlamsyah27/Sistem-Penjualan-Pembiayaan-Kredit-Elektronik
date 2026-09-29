<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CabangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin_pusat') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('cabang')?->id;

        return ['kode' => ['required', 'string', 'max:20', Rule::unique('cabangs', 'kode')->ignore($id)], 'nama' => 'required|string|max:100', 'alamat' => 'nullable|string', 'no_hp' => 'nullable|string|max:30', 'aktif' => 'sometimes|boolean'];
    }

    public function messages(): array
    {
        return ['required' => ':attribute wajib diisi.', 'unique' => ':attribute sudah digunakan.'];
    }

    public function attributes(): array
    {
        return ['kode' => 'kode cabang', 'nama' => 'nama cabang', 'no_hp' => 'nomor telepon'];
    }
}
