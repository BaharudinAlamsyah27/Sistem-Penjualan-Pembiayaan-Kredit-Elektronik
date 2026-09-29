<x-app-layout>
    @section('title', $karyawan->exists ? 'Ubah Karyawan' : 'Tambah Karyawan')

    <form class="max-w-2xl space-y-5 rounded-xl border bg-white p-6" method="POST"
          action="{{ $karyawan->exists ? route('karyawan.update', $karyawan) : route('karyawan.store') }}">
        @csrf
        @if($karyawan->exists)
            @method('PUT')
        @endif

        <h2 class="text-lg font-semibold">{{ $karyawan->exists ? 'Ubah Karyawan' : 'Tambah Karyawan' }}</h2>

        @if($cabangs->isNotEmpty())
            <label class="block text-sm font-medium">Cabang
                <select required name="cabang_id" class="mt-1 w-full rounded-lg border-gray-300">
                    <option value="">Pilih cabang</option>
                    @foreach($cabangs as $cabang)
                        <option value="{{ $cabang->id }}" @selected(old('cabang_id', $karyawan->cabang_id) == $cabang->id)>{{ $cabang->nama }}</option>
                    @endforeach
                </select>
            </label>
            @if($errors->has('cabang_id'))
                <p class="text-sm text-red-600">{{ $errors->first('cabang_id') }}</p>
            @endif
        @endif

        @foreach(['kode' => 'Kode', 'nama' => 'Nama', 'no_hp' => 'Nomor Telepon', 'area' => 'Area'] as $field => $label)
            <label class="block text-sm font-medium">{{ $label }}
                <input name="{{ $field }}" value="{{ old($field, $karyawan->$field) }}" class="mt-1 w-full rounded-lg border-gray-300" @required(in_array($field, ['kode', 'nama']))>
            </label>
            @if($errors->has($field))
                <p class="text-sm text-red-600">{{ $errors->first($field) }}</p>
            @endif
        @endforeach

        <label class="block text-sm font-medium">Jabatan
            <select required name="jabatan" class="mt-1 w-full rounded-lg border-gray-300">
                @foreach(['sales', 'supervisor', 'kolektor', 'surveyor', 'driver'] as $jabatan)
                    <option value="{{ $jabatan }}" @selected(old('jabatan', $karyawan->jabatan) == $jabatan)>{{ ucfirst($jabatan) }}</option>
                @endforeach
            </select>
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $karyawan->exists ? $karyawan->aktif : true))>
            Aktif
        </label>

        <div class="flex gap-3">
            <button class="rounded-lg bg-[#2557a7] px-4 py-2 text-white">Simpan</button>
            <a class="px-4 py-2" href="{{ route('karyawan.index') }}">Batal</a>
        </div>
    </form>
</x-app-layout>
