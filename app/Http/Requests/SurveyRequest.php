<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $survey = $this->route('survey');

        return [
            'surveyor_id' => [
                'nullable',
                Rule::exists('karyawans', 'id')->where('cabang_id', $survey?->cabang_id)->where('jabatan', 'surveyor')->where('aktif', true),
            ],
            'tanggal' => 'nullable|date',
            'hasil' => ['required', Rule::in(['menunggu', 'layak', 'tidak_layak'])],
            'catatan' => 'nullable|string|max:5000',
        ];
    }
}
