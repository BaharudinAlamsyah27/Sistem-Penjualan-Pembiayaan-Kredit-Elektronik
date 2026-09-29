<x-app-layout>
    @section('title', 'Template Kwitansi')
    <div class="mb-5"><h2 class="text-xl font-semibold">Template Kwitansi</h2><p class="text-sm text-gray-500">Unggah foto atau scan formulir fisik untuk acuan pengaturan posisi cetak.</p></div>

    @if(session('success'))<div class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mb-4 rounded-lg bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>@endif

    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h3 class="font-semibold text-gray-900">{{ $templateTersedia ? 'Template sudah tersedia' : 'Belum ada template' }}</h3>
        <p class="mt-1 text-sm text-gray-500">Format JPG, JPEG, atau PNG. Ukuran maksimal 10 MB. File disimpan privat dan hanya dapat dilihat oleh admin pusat.</p>

        @if($templateTersedia)
            <div class="mt-5 overflow-hidden rounded-lg border bg-gray-50 p-2"><img src="{{ route('pengaturan.template-kwitansi.preview') }}" alt="Pratinjau template kwitansi" class="mx-auto max-h-[620px] max-w-full object-contain"></div>
            <div class="mt-3 flex gap-3 text-sm"><a class="text-blue-700 underline" href="{{ route('pengaturan.template-kwitansi.kalibrasi') }}">Pratinjau posisi</a><a class="text-blue-700 underline" href="{{ route('pengaturan.template-kwitansi.kalibrasi', ['grid' => 1]) }}">Tampilkan garis bantu 10 mm</a></div>
            <form method="POST" action="{{ route('pengaturan.template-kwitansi.destroy') }}" class="mt-4" onsubmit="return confirm('Hapus template kwitansi yang tersimpan?')">@csrf @method('DELETE')<button class="rounded-lg border border-rose-200 px-4 py-2 text-sm text-rose-700">Hapus Template</button></form>
        @endif

        <form method="POST" action="{{ route('pengaturan.template-kwitansi.upload') }}" enctype="multipart/form-data" class="mt-5 space-y-3 border-t pt-5">@csrf
            <label for="template" class="block text-sm font-medium text-gray-700">{{ $templateTersedia ? 'Ganti template' : 'Pilih foto atau scan kwitansi' }}</label>
            <input id="template" name="template" type="file" accept="image/jpeg,image/png" required class="block w-full rounded-lg border border-gray-300 p-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-[#2557a7]">
            <button class="rounded-lg bg-[#2557a7] px-4 py-2 text-sm font-medium text-white">Unggah Template</button>
        </form>
    </div>
</x-app-layout>
