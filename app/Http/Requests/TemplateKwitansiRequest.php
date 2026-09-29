<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TemplateKwitansiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin_pusat') ?? false;
    }

    public function rules(): array
    {
        return [
            'template' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'template.required' => 'Pilih foto atau scan template kwitansi terlebih dahulu.',
            'template.image' => 'File harus berupa gambar.',
            'template.mimes' => 'Format yang didukung: JPG, JPEG, atau PNG.',
            'template.max' => 'Ukuran file maksimal 10 MB.',
        ];
    }
}
