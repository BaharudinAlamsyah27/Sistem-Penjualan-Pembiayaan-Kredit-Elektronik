<x-app-layout>
    @section('title', $cabang->exists ? 'Ubah Cabang' : 'Tambah Cabang')
    <form class="max-w-2xl space-y-5 rounded-xl border bg-white p-6" method="POST" action="{{ $cabang->exists ? route('cabang.update', $cabang) : route('cabang.store') }}">@csrf @if($cabang->exists) @method('PUT') @endif
        <h2 class="text-lg font-semibold">{{ $cabang->exists ? 'Ubah Data Cabang' : 'Tambah Cabang' }}</h2>
        @foreach(['kode'=>'Kode','nama'=>'Nama Cabang','no_hp'=>'Nomor Telepon','alamat'=>'Alamat'] as $field=>$label)<label class="block text-sm font-medium">{{ $label }}<input name="{{ $field }}" value="{{ old($field, $cabang->$field) }}" class="mt-1 w-full rounded-lg border-gray-300" {{ in_array($field,['kode','nama']) ? 'required' : '' }}>{{ $errors->first($field) ? '' : '' }}</label>@error($field)<p class="text-sm text-red-600">{{ $message }}</p>@enderror @endforeach
        <label class="flex items-center gap-2"><input type="checkbox" name="aktif" value="1" @checked(old('aktif', $cabang->exists ? $cabang->aktif : true))> Cabang aktif</label><div class="flex gap-3"><button class="rounded-lg bg-[#2557a7] px-4 py-2 text-white">Simpan</button><a class="px-4 py-2 text-gray-600" href="{{ route('cabang.index') }}">Batal</a></div>
    </form>
</x-app-layout>
