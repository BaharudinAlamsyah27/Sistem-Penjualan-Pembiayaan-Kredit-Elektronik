<x-app-layout>
    @section('title', 'Surat Jalan (TTB)')

    <div class="mb-5"><h2 class="text-xl font-semibold">Surat Jalan (TTB)</h2><p class="text-sm text-gray-500">Tinjau jadwal pengiriman dan verifikasi surat jalan.</p></div>
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    <form method="GET" class="mb-4">
        <select name="status" class="rounded-lg border-gray-300">
            <option value="">Semua status</option>
            <option value="menunggu" @selected(request('status') === 'menunggu')>Menunggu</option>
            <option value="terverifikasi" @selected(request('status') === 'terverifikasi')>Terverifikasi</option>
        </select>
        <button class="ml-2 rounded-lg border bg-white px-4 py-2 text-sm">Filter</button>
    </form>

    <div class="overflow-x-auto rounded-xl border bg-white">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500"><tr><th class="p-4">Nomor TTB</th><th class="p-4">Pelanggan / SO</th><th class="p-4">Barang</th><th class="p-4">Jadwal Kirim</th><th class="p-4">Driver</th><th class="p-4">Status / Aksi</th></tr></thead>
            <tbody class="divide-y">
                @forelse($suratJalans as $ttb)
                    <tr>
                        <td class="p-4 font-medium">{{ $ttb->no_ttb }}</td>
                        <td class="p-4">{{ $ttb->salesOrder->pelanggan->nama }}<span class="block text-gray-500">{{ $ttb->salesOrder->no_so }}</span></td>
                        <td class="p-4">{{ $ttb->salesOrder->barang->nama }} × {{ $ttb->salesOrder->qty }}</td>
                        <td class="p-4">{{ $ttb->tgl_kirim?->format('d/m/Y') ?? '—' }}</td>
                        <td class="p-4">{{ $ttb->driver?->nama ?? '—' }}</td>
                        <td class="p-4">
                            @if($ttb->status_verifikasi === 'terverifikasi')
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs text-emerald-700">Terverifikasi</span>
                            @else
                                <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs text-amber-700">Menunggu</span>
                                <form method="POST" action="{{ route('surat-jalan.verify', $ttb) }}" class="mt-2">
                                    @csrf
                                    <button class="rounded-lg bg-[#2557a7] px-3 py-1.5 text-xs font-medium text-white">Tandai Terverifikasi</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-6 text-center text-gray-500">Belum ada surat jalan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $suratJalans->links() }}</div>
</x-app-layout>
