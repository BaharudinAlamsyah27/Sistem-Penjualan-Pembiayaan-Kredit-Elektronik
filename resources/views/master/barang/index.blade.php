<x-app-layout>
    @section('title', 'Data Barang')

    <div class="mb-5 flex items-center justify-between gap-3">
        <div><h2 class="text-xl font-semibold">Data Barang</h2><p class="text-sm text-gray-500">Master barang dan saldo stok masing-masing cabang.</p></div>
        <a class="rounded-lg bg-[#2557a7] px-4 py-2 text-sm text-white" href="{{ route('barang.create') }}">Tambah Barang</a>
    </div>

    <form class="mb-4 flex flex-wrap gap-3" method="GET">
        <input name="q" value="{{ request('q') }}" placeholder="Cari kode, nama, atau merek" class="rounded-lg border-gray-300">
        @if($cabangs->isNotEmpty())
            <select name="cabang" class="rounded-lg border-gray-300">
                <option value="">Semua cabang</option>
                @foreach($cabangs as $cabang)
                    <option value="{{ $cabang->id }}" @selected($cabangTerpilih == $cabang->id)>{{ $cabang->nama }}</option>
                @endforeach
            </select>
        @endif
        <button class="rounded-lg border bg-white px-4">Cari</button>
        <a href="{{ route('pembelian.create') }}" class="rounded-lg border bg-white px-4 py-2 text-sm">Catat Pembelian</a>
    </form>

    <div class="overflow-x-auto rounded-xl border bg-white">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500"><tr><th class="p-4">Kode / Barang</th><th class="p-4">Kategori</th><th class="p-4">Harga Beli</th><th class="p-4">Harga Jual</th><th class="p-4">Stok per Cabang</th><th class="p-4">Status / Aksi</th></tr></thead>
            <tbody class="divide-y">
                @forelse($barangs as $barang)
                    <tr>
                        <td class="p-4">{{ $barang->kode }}<span class="block font-medium">{{ $barang->nama }} — {{ $barang->merk }}</span></td>
                        <td class="p-4">{{ $barang->kategori }}</td>
                        <td class="p-4">Rp {{ number_format((float) $barang->harga_beli, 0, ',', '.') }}</td>
                        <td class="p-4">Rp {{ number_format((float) $barang->harga_jual, 0, ',', '.') }}</td>
                        <td class="p-4">
                            @forelse($barang->stokBarangs as $stok)
                                <div class="whitespace-nowrap">{{ $stok->cabang?->nama ?? 'Cabang' }}: <strong>{{ number_format($stok->qty, 0, ',', '.') }}</strong></div>
                            @empty
                                <span class="text-gray-500">Belum ada stok</span>
                            @endforelse
                        </td>
                        <td class="p-4">
                            {{ $barang->aktif ? 'Aktif' : 'Nonaktif' }}
                            <div><a class="text-blue-700" href="{{ route('barang.edit', $barang) }}">Ubah</a>
                                <form class="inline" method="POST" action="{{ route('barang.destroy', $barang) }}">@csrf @method('DELETE')<button class="ml-2 text-red-600">Hapus</button></form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="p-4" colspan="6">Data barang belum tersedia.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $barangs->links() }}</div>
</x-app-layout>
