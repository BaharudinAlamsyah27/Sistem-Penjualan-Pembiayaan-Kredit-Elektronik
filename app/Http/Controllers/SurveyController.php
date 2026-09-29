<?php

namespace App\Http\Controllers;

use App\Http\Requests\SurveyRequest;
use App\Models\Karyawan;
use App\Models\SalesOrder;
use App\Models\Survey;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SurveyController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Survey::class, 'survey');
    }

    public function index(): View
    {
        return view('survey.index', [
            'surveys' => Survey::with(['salesOrder.pelanggan', 'salesOrder.barang', 'surveyor'])
                ->latest()->paginate(15),
        ]);
    }

    public function edit(Survey $survey): View
    {
        $survey->load(['salesOrder.pelanggan', 'salesOrder.barang', 'salesOrder.sales']);

        return view('survey.edit', [
            'survey' => $survey,
            'surveyors' => Karyawan::where('cabang_id', $survey->cabang_id)
                ->where('jabatan', 'surveyor')->where('aktif', true)->orderBy('nama')->get(),
        ]);
    }

    public function update(SurveyRequest $request, Survey $survey): RedirectResponse
    {
        if ($survey->hasil !== 'menunggu') {
            return redirect()->route('survey.index')->with('error', 'Hasil survei sudah dicatat dan tidak dapat diubah.');
        }

        $data = $request->validated();
        $data['tanggal'] = $data['tanggal'] ?? now()->toDateString();
        $survey->update($data);

        if ($survey->hasil === 'tidak_layak') {
            SalesOrder::whereKey($survey->sales_order_id)->update(['status' => 'ditolak']);
        }

        return redirect()->route('sales-order.show', $survey->sales_order_id)->with('success', 'Hasil survei berhasil disimpan.');
    }
}
