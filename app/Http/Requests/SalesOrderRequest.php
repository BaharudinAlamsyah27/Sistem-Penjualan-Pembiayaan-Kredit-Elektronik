<?php

namespace App\Http\Requests;

use App\Models\Barang;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SalesOrderRequest extends FormRequest
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
        $cabangId = $this->input('cabang_id');

        return [
            'cabang_id' => 'required|exists:cabangs,id,aktif,1',
            'tanggal' => 'required|date',
            'pelanggan_id' => ['required', Rule::exists('pelanggans', 'id')->where('cabang_id', $cabangId)],
            'barang_id' => 'required|exists:barangs,id,aktif,1',
            'qty' => 'required|integer|min:1|max:1000',
            'sales_id' => ['required', Rule::exists('karyawans', 'id')->where('cabang_id', $cabangId)->where('jabatan', 'sales')->where('aktif', true)],
            'dp' => 'required|numeric|min:0|max:9999999999999.99',
            'tenor' => 'required|integer|min:1|max:60',
            'bunga_persen' => 'required|numeric|min:0|max:100',
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->has('barang_id') || $validator->errors()->has('qty') || $validator->errors()->has('dp')) {
                return;
            }

            $barang = Barang::find($this->integer('barang_id'));
            $hargaTotal = (float) ($barang?->harga_jual ?? 0) * $this->integer('qty');
            if ($this->input('dp') >= $hargaTotal) {
                $validator->errors()->add('dp', 'Uang muka harus lebih kecil dari total harga barang.');
            }
        }];
    }
}
