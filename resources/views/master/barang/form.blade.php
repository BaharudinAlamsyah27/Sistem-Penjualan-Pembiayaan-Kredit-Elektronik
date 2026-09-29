<x-app-layout>
    @section('title', $barang->exists ? 'Ubah Barang' : 'Tambah Barang')

    <form class="grid max-w-3xl gap-5 rounded-xl border bg-white p-6 md:grid-cols-2" method="POST"
          action="{{ $barang->exists ? route('barang.update', $barang) : route('barang.store') }}">
        @csrf
        @if($barang->exists)
            @method('PUT')
        @endif

        <h2 class="text-lg font-semibold md:col-span-2">{{ $barang->exists ? 'Ubah Barang' : 'Tambah Barang' }}</h2>

        @foreach(['kode' => 'Kode', 'nama' => 'Nama Barang', 'merk' => 'Merek', 'kategori' => 'Kategori', 'harga_beli' => 'Harga Beli', 'harga_jual' => 'Harga Jual'] as $field => $label)
            <label class="text-sm font-medium">{{ $label }}
                <input name="{{ $field }}" value="{{ old($field, $barang->$field) }}"
                       type="{{ str_starts_with($field, 'harga_') ? 'number' : 'text' }}"
                       @if(str_starts_with($field, 'harga_')) min="0" step="0.01" @endif
                       class="mt-1 w-full rounded-lg border-gray-300"
                       @required(in_array($field, ['kode', 'nama', 'harga_beli', 'harga_jual']))>
            </label>
            @if($errors->has($field))
                <p class="text-sm text-red-600 md:col-span-2">{{ $errors->first($field) }}</p>
            @endif
        @endforeach

        <label class="flex items-center gap-2 md:col-span-2">
            <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $barang->exists ? $barang->aktif : true))>
            Barang aktif
        </label>

        <section class="rounded-lg border bg-gray-50 p-4 md:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="font-semibold">Stok per Cabang</h3>
                    <p class="text-xs text-gray-500">Koreksi saldo dicatat sebagai mutasi penyesuaian. Stok masuk dari supplier tetap dicatat melalui pembelian.</p>
                </div>
                <a href="{{ route('pembelian.create') }}" class="rounded-lg border bg-white px-3 py-2 text-sm text-[#2557a7]">Catat Pembelian</a>
            </div>

            @if($cabangsStok->isNotEmpty())
                <div class="mt-3 divide-y rounded-lg border bg-white">
                    @foreach($cabangsStok as $cabang)
                        <label class="flex flex-wrap items-center justify-between gap-3 px-3 py-2 text-sm">
                            <span>{{ $cabang->nama }} <span class="text-gray-500">(saldo saat ini: {{ number_format((int) ($saldoStok[$cabang->id] ?? 0), 0, ',', '.') }} unit)</span></span>
                            <span class="flex items-center gap-2">Saldo baru
                                <input type="number" name="stok[{{ $cabang->id }}]" min="0" max="99999999" required
                                       value="{{ old('stok.'.$cabang->id, $saldoStok[$cabang->id] ?? 0) }}"
                                       class="w-28 rounded-lg border-gray-300 text-sm">
                            </span>
                        </label>
                    @endforeach
                </div>
            @else
                <p class="mt-3 rounded-lg border bg-white px-3 py-3 text-sm text-gray-500">Tidak ada cabang yang dapat dikelola akun ini.</p>
            @endif
        </section>

        <div class="flex gap-3 md:col-span-2">
            <button class="rounded-lg bg-[#2557a7] px-4 py-2 text-white">Simpan</button>
            <a class="px-4 py-2" href="{{ route('barang.index') }}">Batal</a>
        </div>
    </form>
</x-app-layout>
