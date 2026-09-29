<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NexaERP - Sistem Penjualan Kredit Elektronik Multi-Cabang</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-[#f4f6fa] text-[#1f2937] flex flex-col justify-between">
    <!-- Navbar -->
    <header class="w-full bg-white border-b border-gray-200 py-4 px-6 md:px-12 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2">
            <span class="text-2xl font-bold tracking-tight text-[#2557a7]">NexaERP<span class="text-blue-500">.</span></span>
        </div>
        <div>
            @auth
                <a href="{{ route('dashboard') }}" class="px-5 py-2 rounded-lg text-xs font-semibold text-white bg-[#2557a7] hover:bg-blue-700 transition shadow-xs">
                    Buka Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="px-5 py-2 rounded-lg text-xs font-semibold text-white bg-[#2557a7] hover:bg-blue-700 transition shadow-xs">
                    Masuk ke Sistem (Login)
                </a>
            @endauth
        </div>
    </header>

    <!-- Hero Content -->
    <main class="flex-1 flex items-center justify-center p-6">
        <div class="max-w-2xl text-center space-y-6">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium bg-blue-100/70 text-[#2557a7] border border-blue-200/50">
                <span>Versi MVP Multi-Cabang</span>
            </div>

            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight leading-tight">
                Sistem Penjualan & Pembiayaan Kredit Elektronik
            </h1>

            <p class="text-sm md:text-base text-gray-500 max-w-xl mx-auto leading-relaxed">
                Kelola penjualan kredit, survey kelayakan lapangan, penerbitan surat jalan & faktur, hingga penagihan angsuran kolektor per cabang secara terpusat.
            </p>

            <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-6 py-3 rounded-lg text-sm font-semibold text-white bg-[#2557a7] hover:bg-blue-700 transition shadow-md">
                        Masuk ke Dashboard Analytics
                    </a>
                @else
                    <a href="{{ route('login') }}" class="w-full sm:w-auto px-6 py-3 rounded-lg text-sm font-semibold text-white bg-[#2557a7] hover:bg-blue-700 transition shadow-md">
                        Login Sebagai Petugas / Admin
                    </a>
                @endauth
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-6 border-t border-gray-200 text-center text-xs text-gray-400">
        &copy; {{ date('Y') }} NexaERP. Seluruh hak cipta dilindungi.
    </footer>
</body>
</html>
