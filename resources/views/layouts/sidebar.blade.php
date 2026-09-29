<!-- Sidebar NexaERP -->
<aside class="w-64 bg-white border-r border-gray-200 flex flex-col shrink-0 min-h-screen">
    <!-- Logo -->
    <div class="h-16 flex items-center px-6 border-b border-gray-100">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            <span class="text-2xl font-bold tracking-tight text-[#2557a7]">NexaERP<span class="text-blue-500">.</span></span>
        </a>
    </div>

    <!-- Navigation Menu Items -->
    <nav class="flex-1 overflow-y-auto py-4 space-y-6 text-[13.5px]">
        <!-- Group: MENU UTAMA -->
        <div>
            <div class="px-6 pb-2 text-[10.5px] font-bold tracking-wider text-gray-400 uppercase">
                Menu Utama
            </div>
            <div class="space-y-0.5">
                <a href="{{ route('dashboard') }}"
                   class="flex items-center px-6 py-2.5 transition-colors {{ request()->routeIs('dashboard') ? 'border-l-4 border-[#2557a7] bg-blue-50/50 text-[#2557a7] font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                    <span>Dashboard Analytics</span>
                </a>
            </div>
        </div>

        <!-- Group: SALES & KREDIT -->
        <div>
            <div class="px-6 pb-2 text-[10.5px] font-bold tracking-wider text-gray-400 uppercase">
                Sales & Kredit
            </div>
            <div class="space-y-0.5">
                <a href="{{ route('pelanggan.index') }}"
                   class="flex items-center px-6 py-2.5 transition-colors {{ request()->routeIs('pelanggan.*') ? 'border-l-4 border-[#2557a7] bg-blue-50/50 text-[#2557a7] font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                    <span>Data Pelanggan</span>
                </a>
                <a href="{{ route('sales-order.index') }}"
                   class="flex items-center px-6 py-2.5 transition-colors {{ request()->routeIs('sales-order.*') ? 'border-l-4 border-[#2557a7] bg-blue-50/50 text-[#2557a7] font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                    <span>Sales Order (SO)</span>
                </a>
                <a href="{{ route('survey.index') }}"
                   class="flex items-center px-6 py-2.5 transition-colors {{ request()->routeIs('survey.*') ? 'border-l-4 border-[#2557a7] bg-blue-50/50 text-[#2557a7] font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                    <span>Survey Kelayakan</span>
                </a>
                <a href="{{ route('angsuran.index') }}"
                   class="flex items-center px-6 py-2.5 transition-colors {{ request()->routeIs('angsuran.*') ? 'border-l-4 border-[#2557a7] bg-blue-50/50 text-[#2557a7] font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                    <span>Penerimaan Angsuran</span>
                </a>
            </div>
        </div>

        <!-- Group: INVENTORI & LOGISTIK -->
        <div>
            <div class="px-6 pb-2 text-[10.5px] font-bold tracking-wider text-gray-400 uppercase">
                Inventori & Logistik
            </div>
            <div class="space-y-0.5">
                <a href="{{ route('barang.index') }}"
                   class="flex items-center px-6 py-2.5 transition-colors {{ request()->routeIs('barang.*') ? 'border-l-4 border-[#2557a7] bg-blue-50/50 text-[#2557a7] font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                    <span>Data Barang</span>
                </a>
                <a href="{{ route('pembelian.index') }}"
                   class="flex items-center px-6 py-2.5 transition-colors {{ request()->routeIs('pembelian.*') ? 'border-l-4 border-[#2557a7] bg-blue-50/50 text-[#2557a7] font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                    <span>Pembelian & Retur</span>
                </a>
                <a href="{{ route('surat-jalan.index') }}"
                   class="flex items-center px-6 py-2.5 transition-colors {{ request()->routeIs('surat-jalan.*') ? 'border-l-4 border-[#2557a7] bg-blue-50/50 text-[#2557a7] font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                    <span>Surat Jalan (TTB)</span>
                </a>
            </div>
        </div>

        <!-- Group: KEUANGAN (LAPORAN) -->
        <div>
            <div class="px-6 pb-2 text-[10.5px] font-bold tracking-wider text-gray-400 uppercase">
                Keuangan (Laporan)
            </div>
            <div class="space-y-0.5">
                <a href="{{ route('laporan.laba-rugi') }}"
                   class="flex items-center px-6 py-2.5 transition-colors {{ request()->routeIs('laporan.laba-rugi') ? 'border-l-4 border-[#2557a7] bg-blue-50/50 text-[#2557a7] font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                    <span>Laba / Rugi</span>
                </a>
                <a href="{{ route('laporan.tagihan') }}"
                   class="flex items-center px-6 py-2.5 transition-colors {{ request()->routeIs('laporan.tagihan') ? 'border-l-4 border-[#2557a7] bg-blue-50/50 text-[#2557a7] font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                    <span>Laporan Tagihan</span>
                </a>
            </div>
        </div>

        <!-- Group: MASTER & SETUP -->
        <div>
            <div class="px-6 pb-2 text-[10.5px] font-bold tracking-wider text-gray-400 uppercase">
                Master & Setup
            </div>
            <div class="space-y-0.5">
                <a href="{{ route('karyawan.index') }}"
                   class="flex items-center px-6 py-2.5 transition-colors {{ request()->routeIs('karyawan.*') ? 'border-l-4 border-[#2557a7] bg-blue-50/50 text-[#2557a7] font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                    <span>Karyawan & Akses</span>
                </a>

                @if(auth()->user()->hasRole('admin_pusat'))
                <a href="{{ route('cabang.index') }}"
                   class="flex items-center px-6 py-2.5 transition-colors {{ request()->routeIs('cabang.*') ? 'border-l-4 border-[#2557a7] bg-blue-50/50 text-[#2557a7] font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                    <span>Data Cabang</span>
                </a>
                @endif
            </div>
        </div>
    </nav>
</aside>
