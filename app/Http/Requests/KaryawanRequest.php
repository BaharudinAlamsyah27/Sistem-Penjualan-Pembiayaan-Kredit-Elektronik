<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KaryawanRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->user()?->hasRole('admin_pusat')) {
            $this->merge(['cabang_id' => $this->user()?->cabang_id]);
        }
        $this->merge(['aktif' => $this->boolean('aktif')]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $row = $this->route('karyawan');
        $branch = $this->input('cabang_id');

        return ['cabang_id' => 'required|exists:cabangs,id', 'kode' => ['required', 'string', 'max:30', Rule::unique('karyawans', 'kode')->where('cabang_id', $branch)->ignore($row?->id)], 'nama' => 'required|string|max:100', 'jabatan' => ['required', Rule::in(['sales', 'supervisor', 'kolektor', 'surveyor', 'driver'])], 'no_hp' => 'nullable|string|max:30', 'area' => 'nullable|string|max:100', 'aktif' => 'boolean'];
    }
}
