<x-app-layout>
    @section('title', $title ?? 'Modul NexaERP')

    <div class="bg-white rounded-xl border border-gray-200 p-8 text-center max-w-2xl mx-auto my-12 shadow-xs">
        <div class="w-16 h-16 rounded-full bg-blue-50 text-[#2557a7] flex items-center justify-center mx-auto mb-4 border border-blue-100">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-900 mb-2">{{ $title ?? 'Modul NexaERP' }}</h2>
        <p class="text-sm text-gray-500 mb-6">
            Halaman ini telah terhubung ke navigasi sidebar. Fitur form dan logika bisnis lengkap modul ini siap dikembangkan pada tahapan fase berikutnya sesuai panduan <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded text-blue-700 font-semibold">GEMINI.md</code>.
        </p>
        <div class="flex justify-center gap-3">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-semibold text-white bg-[#2557a7] hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Dashboard
            </a>
        </div>
    </div>
</x-app-layout>
