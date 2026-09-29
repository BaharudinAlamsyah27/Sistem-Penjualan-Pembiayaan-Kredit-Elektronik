<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PenerbitanFakturRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $salesOrder = $this->route('sales_order');

        return [
            'tgl_dp' => 'required|date',
            'driver_id' => [
                'required',
                Rule::exists('karyawans', 'id')->where('cabang_id', $salesOrder?->cabang_id)->where('jabatan', 'driver')->where('aktif', true),
            ],
            'tgl_kirim' => 'required|date',
            'nota' => 'nullable|string|max:100',
        ];
    }
}
