<x-app-layout>
    @section('title', $pelanggan->exists ? 'Ubah Pelanggan' : 'Tambah Pelanggan')

    <form class="grid max-w-4xl gap-5 rounded-xl border bg-white p-6 md:grid-cols-2" method="POST"
          action="{{ $pelanggan->exists ? route('pelanggan.update', $pelanggan) : route('pelanggan.store') }}">
        @csrf
        @if($pelanggan->exists)
            @method('PUT')
        @endif

        <h2 class="text-lg font-semibold md:col-span-2">{{ $pelanggan->exists ? 'Ubah Data Pelanggan' : 'Tambah Pelanggan' }}</h2>

        @if($cabangs->isNotEmpty())
            <label class="text-sm font-medium">Cabang
                <select name="cabang_id" required class="mt-1 w-full rounded-lg border-gray-300">
                    <option value="">Pilih cabang</option>
                    @foreach($cabangs as $cabang)
                        <option value="{{ $cabang->id }}" @selected(old('cabang_id', $pelanggan->cabang_id) == $cabang->id)>{{ $cabang->nama }}</option>
                    @endforeach
                </select>
            </label>
            @if($errors->has('cabang_id'))
                <p class="text-sm text-red-600">{{ $errors->first('cabang_id') }}</p>
            @endif
        @endif

        @foreach(['kode' => 'Kode', 'nama' => 'Nama', 'no_hp' => 'Nomor Telepon', 'no_ktp' => 'Nomor KTP', 'kabupaten' => 'Kabupaten', 'kecamatan' => 'Kecamatan'] as $field => $label)
            <label class="text-sm font-medium">{{ $label }}
                <input name="{{ $field }}" value="{{ old($field, $pelanggan->$field) }}" class="mt-1 w-full rounded-lg border-gray-300" @required(in_array($field, ['kode', 'nama', 'kabupaten', 'kecamatan']))>
            </label>
            @if($errors->has($field))
                <p class="text-sm text-red-600">{{ $errors->first($field) }}</p>
            @endif
        @endforeach

        <label class="text-sm font-medium md:col-span-2">Alamat
            <textarea name="alamat" required rows="2" class="mt-1 w-full rounded-lg border-gray-300">{{ old('alamat', $pelanggan->alamat) }}</textarea>
        </label>
        @if($errors->has('alamat'))
            <p class="text-sm text-red-600 md:col-span-2">{{ $errors->first('alamat') }}</p>
        @endif

        <label class="text-sm font-medium md:col-span-2">Catatan
            <textarea name="catatan" rows="2" class="mt-1 w-full rounded-lg border-gray-300">{{ old('catatan', $pelanggan->catatan) }}</textarea>
        </label>

        <div class="flex gap-3 md:col-span-2">
            <button class="rounded-lg bg-[#2557a7] px-4 py-2 text-white">Simpan</button>
            <a class="px-4 py-2" href="{{ route('pelanggan.index') }}">Batal</a>
        </div>
    </form>
</x-app-layout>
