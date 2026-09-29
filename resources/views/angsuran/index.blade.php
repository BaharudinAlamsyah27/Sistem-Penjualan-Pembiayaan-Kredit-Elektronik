<x-app-layout>
    @section('title', 'Penerimaan Angsuran')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div><h2 class="text-xl font-semibold">Penerimaan Angsuran</h2><p class="text-sm text-gray-500">Cari faktur atau pelanggan, lalu catat pembayaran penuh.</p></div>
        <a href="{{ route('laporan.tagihan') }}" class="rounded-lg border px-4 py-2 text-sm text-gray-700">Laporan Tagihan</a>
    </div>
    @if(session('success'))<div class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mb-4 rounded-lg bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>@endif
    <form method="GET" class="mb-4 flex flex-wrap gap-2 rounded-xl border bg-white p-4">
        <input name="q" value="{{ request('q') }}" placeholder="No. faktur / nama pelanggan" class="rounded-lg border-gray-300 text-sm">
        <select name="status" class="rounded-lg border-gray-300 text-sm"><option value="">Semua status</option>@foreach(['belum'=>'Belum dibayar','menunggu_pembayaran'=>'Menunggu pembayaran','lunas'=>'Lunas'] as $value=>$label)<option value="{{ $value }}" @selected(request('status')===$value)>{{ $label }}</option>@endforeach</select>
        @if(auth()->user()->hasRole('admin_pusat'))<select name="cabang" class="rounded-lg border-gray-300 text-sm"><option value="">Semua cabang</option>@foreach($cabangs as $cabang)<option value="{{ $cabang->id }}" @selected((string)request('cabang')===(string)$cabang->id)>{{ $cabang->nama }}</option>@endforeach</select>@endif
        <button class="rounded-lg bg-[#2557a7] px-4 py-2 text-sm text-white">Cari</button>
    </form>
    <div class="overflow-x-auto rounded-xl border bg-white"><table class="w-full text-left text-sm"><thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr><th class="p-3">Faktur / Pelanggan</th><th class="p-3">Angsuran</th><th class="p-3">Jatuh tempo</th><th class="p-3">Nominal</th><th class="p-3">Status / Pembayaran</th></tr></thead><tbody class="divide-y">
    @forelse($angsurans as $angsuran)
        @php($so=$angsuran->faktur?->salesOrder)
        <tr><td class="p-3"><span class="font-medium">{{ $angsuran->faktur?->no_faktur }}</span><span class="block text-gray-500">{{ $so?->pelanggan?->nama }}</span></td><td class="p-3">Ke-{{ $angsuran->angsuran_ke }} dari {{ $so?->tenor }}</td><td class="p-3">{{ $angsuran->jatuh_tempo->format('d/m/Y') }}</td><td class="p-3 font-medium">Rp {{ number_format((float)$angsuran->nominal,0,',','.') }}</td><td class="p-3">
            @if($angsuran->status==='lunas')
                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs text-emerald-800">Lunas {{ $angsuran->tgl_bayar?->format('d/m/Y') }}</span>
                @if(auth()->user()->hasRole('admin_pusat'))<form method="POST" action="{{ route('angsuran.update', $angsuran) }}" class="mt-2 grid min-w-[260px] grid-cols-2 gap-2">@csrf @method('PUT')
                    <input type="date" name="tgl_bayar" value="{{ $angsuran->tgl_bayar?->toDateString() }}" required class="rounded border-gray-300 text-xs">
                    <select name="metode" required class="rounded border-gray-300 text-xs"><option value="tunai" @selected($angsuran->metode==='tunai')>Tunai</option><option value="transfer" @selected($angsuran->metode==='transfer')>Transfer</option></select>
                    <select name="kolektor_id" class="col-span-2 rounded border-gray-300 text-xs"><option value="">Tanpa kolektor</option>@foreach($kolektors->where('cabang_id',$angsuran->cabang_id) as $kolektor)<option value="{{ $kolektor->id }}" @selected($angsuran->kolektor_id===$kolektor->id)>{{ $kolektor->nama }}</option>@endforeach</select>
                    <button class="col-span-2 rounded border px-3 py-1.5 text-xs">Koreksi pembayaran</button>
                </form>@endif
            @else
                <form method="POST" action="{{ route('angsuran.store', $angsuran) }}" class="grid min-w-[260px] grid-cols-2 gap-2">@csrf
                    <input type="date" name="tgl_bayar" value="{{ now()->toDateString() }}" required class="rounded border-gray-300 text-xs">
                    <select name="metode" required class="rounded border-gray-300 text-xs"><option value="">Metode</option><option value="tunai">Tunai</option><option value="transfer">Transfer</option></select>
                    <select name="kolektor_id" class="col-span-2 rounded border-gray-300 text-xs"><option value="">Pilih kolektor (opsional)</option>@foreach($kolektors->where('cabang_id',$angsuran->cabang_id) as $kolektor)<option value="{{ $kolektor->id }}">{{ $kolektor->nama }}</option>@endforeach</select>
                    <button class="col-span-2 rounded bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white">Catat pembayaran penuh</button>
                </form>
            @endif
        </td></tr>
    @empty<tr><td colspan="5" class="p-8 text-center text-gray-500">Tidak ada angsuran yang cocok.</td></tr>@endforelse
    </tbody></table></div><div class="mt-4">{{ $angsurans->links() }}</div>
</x-app-layout>
