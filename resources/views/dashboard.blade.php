<x-app-layout>
    @section('title', 'Dashboard Analytics')

    <div class="space-y-6">
        @if($isAdminPusat)
            <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-2">
                <label for="cabang" class="text-xs font-medium text-gray-500">Filter cabang</label>
                <select id="cabang" name="cabang" onchange="this.form.submit()"
                        class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white text-gray-800 shadow-xs focus:outline-none focus:ring-2 focus:ring-[#2557a7]/30">
                    <option value="" @selected($cabangTerpilih === null)>Semua Cabang</option>
                    @foreach($cabangs as $cabang)
                        <option value="{{ $cabang->id }}" @selected($cabangTerpilih === $cabang->id)>{{ $cabang->nama }}</option>
                    @endforeach
                </select>
            </form>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-xs flex flex-col justify-between min-h-[132px]">
                <div>
                    <span class="text-xs font-medium text-gray-500">Laba Bersih (Bulan Ini)</span>
                    <div class="mt-2 text-2xl font-bold text-gray-900 tracking-tight">
                        {{ $labaBersihNominal }}
                    </div>
                </div>
                <div class="mt-3 text-xs font-medium {{ $labaKeteranganClass }}">
                    {{ $labaBersihKeterangan }}
                </div>
            </div>

            <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-xs flex flex-col justify-between min-h-[132px]">
                <div>
                    <span class="text-xs font-medium text-gray-500">Total Piutang Berjalan</span>
                    <div class="mt-2 text-2xl font-bold text-gray-900 tracking-tight">
                        {{ $totalPiutang }}
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-1.5 text-xs text-rose-500 font-medium">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ $pelangganJatuhTempoLabel }}</span>
                </div>
            </div>

            <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-xs flex flex-col justify-between min-h-[132px]">
                <div>
                    <span class="text-xs font-medium text-gray-500">Angsuran Masuk Hari Ini</span>
                    <div class="mt-2 text-2xl font-bold text-gray-900 tracking-tight">
                        {{ $angsuranHariIni }}
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-1.5 text-xs text-emerald-600 font-medium">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ $angsuranHariIniKeterangan }}</span>
                </div>
            </div>

            <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-xs flex flex-col justify-between min-h-[132px]">
                <div>
                    <span class="text-xs font-medium text-gray-500">Sales Order Menunggu Survey</span>
                    <div class="mt-2 text-2xl font-bold text-gray-900 tracking-tight">
                        {{ $soMenungguSurvey }}
                    </div>
                </div>
                <div class="mt-3 text-xs text-gray-400 font-medium">
                    Tugaskan Surveyor Segera
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h2 class="text-sm md:text-base font-semibold text-gray-800">
                    Tagihan Angsuran Jatuh Tempo Hari Ini
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/60 text-gray-400 text-[11px] font-semibold tracking-wider uppercase">
                            <th class="py-3 px-5">No. Faktur</th>
                            <th class="py-3 px-5">Nama Pelanggan</th>
                            <th class="py-3 px-5">Nominal Tagihan</th>
                            <th class="py-3 px-5">Kolektor (Sales)</th>
                            <th class="py-3 px-5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        @forelse($tabelTagihan as $row)
                            <tr>
                                <td class="py-4 px-5 font-medium text-gray-700 whitespace-nowrap">
                                    {{ $row['no_faktur'] }}
                                </td>
                                <td class="py-4 px-5">
                                    <div class="font-semibold text-gray-900">{{ $row['nama_pelanggan'] }}</div>
                                    <div class="text-[11px] text-gray-400 font-normal mt-0.5">{{ $row['keterangan_angsuran'] }}</div>
                                </td>
                                <td class="py-4 px-5 font-semibold text-gray-900 whitespace-nowrap">
                                    {{ $row['nominal'] }}
                                </td>
                                <td class="py-4 px-5 text-gray-700">
                                    <span class="font-medium text-gray-800">{{ $row['kolektor_nama'] }}</span>
                                    <span class="text-gray-400 text-[11px] ml-1">{{ $row['kolektor_area'] }}</span>
                                </td>
                                <td class="py-4 px-5 text-center whitespace-nowrap">
                                    @if($row['status_class'] === 'lunas')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-medium bg-emerald-100 text-emerald-800">
                                            {{ $row['status'] }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-medium bg-amber-100/80 text-amber-800">
                                            {{ $row['status'] }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-400 text-xs">
                                    Tidak ada tagihan jatuh tempo hari ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
