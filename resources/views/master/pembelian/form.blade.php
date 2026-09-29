<x-app-layout>
    @section('title', 'Catat Pembelian')

    <form method="POST" action="{{ route('pembelian.store') }}"
          x-data="{ items: [{barang_id:'',qty:1,harga:''}], add(){this.items.push({barang_id:'',qty:1,harga:''})}, remove(i){if(this.items.length>1)this.items.splice(i,1)} }"
          class="max-w-4xl space-y-5 rounded-xl border bg-white p-6">
        @csrf
        <h2 class="text-lg font-semibold">Pembelian Stok Masuk</h2>

        @if($cabangs->isNotEmpty())
            <label class="block text-sm font-medium">Cabang tujuan
                <select required name="cabang_id" class="mt-1 w-full rounded-lg border-gray-300">
                    <option value="">Pilih cabang</option>
                    @foreach($cabangs as $cabang)
                        <option value="{{ $cabang->id }}" @selected(old('cabang_id') == $cabang->id)>{{ $cabang->nama }}</option>
                    @endforeach
                </select>
            </label>
            @if($errors->has('cabang_id'))
                <p class="text-sm text-red-600">{{ $errors->first('cabang_id') }}</p>
            @endif
        @endif

        <div class="grid gap-4 md:grid-cols-3">
            <label class="text-sm font-medium">Tanggal
                <input required type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" class="mt-1 w-full rounded-lg border-gray-300">
            </label>
            <label class="text-sm font-medium">Supplier
                <input required name="supplier" value="{{ old('supplier') }}" class="mt-1 w-full rounded-lg border-gray-300">
            </label>
            <label class="text-sm font-medium">Metode
                <select name="metode" required class="mt-1 w-full rounded-lg border-gray-300">
                    <option value="cashmoney" @selected(old('metode') === 'cashmoney')>Cashmoney</option>
                    <option value="tempo" @selected(old('metode') === 'tempo')>Tempo</option>
                </select>
            </label>
        </div>

        <div>
            <div class="mb-2 flex items-center justify-between">
                <h3 class="font-semibold">Barang yang dibeli</h3>
                <button type="button" @click="add()" class="text-sm font-semibold text-blue-700">+ Tambah Barang</button>
            </div>
            <template x-for="(item,index) in items" :key="index">
                <div class="mb-3 grid items-end gap-3 rounded-lg bg-gray-50 p-3 md:grid-cols-4">
                    <label class="text-sm">Barang
                        <select required :name="`items[${index}][barang_id]`" x-model="item.barang_id" class="mt-1 w-full rounded-lg border-gray-300">
                            <option value="">Pilih barang</option>
                            @foreach($barangs as $barang)
                                <option value="{{ $barang->id }}">{{ $barang->kode }} — {{ $barang->nama }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm">Jumlah
                        <input required type="number" min="1" :name="`items[${index}][qty]`" x-model="item.qty" class="mt-1 w-full rounded-lg border-gray-300">
                    </label>
                    <label class="text-sm">Harga per unit
                        <input required type="number" min="0" step="0.01" :name="`items[${index}][harga]`" x-model="item.harga" class="mt-1 w-full rounded-lg border-gray-300">
                    </label>
                    <button type="button" @click="remove(index)" class="pb-2 text-left text-red-600">Hapus baris</button>
                </div>
            </template>
            @if($errors->has('items'))
                <p class="text-sm text-red-600">{{ $errors->first('items') }}</p>
            @endif
        </div>

        <p class="text-sm text-gray-500">Total pembelian dihitung ulang di server. Stok bertambah melalui pembelian dan tercatat sebagai mutasi masuk.</p>
        <div class="flex gap-3">
            <button class="rounded-lg bg-[#2557a7] px-4 py-2 text-white">Simpan Pembelian</button>
            <a class="px-4 py-2" href="{{ route('pembelian.index') }}">Batal</a>
        </div>
    </form>
</x-app-layout>
