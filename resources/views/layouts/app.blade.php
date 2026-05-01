<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KasirPro') — {{ \App\Models\Setting::get('store_name', 'KasirPro') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        sidebar: '#0B1120',
                    },
                    fontFamily: {
                        inter: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #0f172a; color: #e2e8f0; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #0B1120; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 9999px; }
        .active-link { @apply bg-blue-600 text-white rounded-lg !important; }
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen flex font-inter" x-data="{ sidebarOpen: false }">

    {{-- Mobile Overlay --}}
    <div class="fixed inset-0 bg-black/60 z-40 lg:hidden" x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak></div>

    {{-- Sidebar --}}
    <aside class="fixed top-0 left-0 w-64 bg-[#0B1120] text-gray-300 h-screen p-4 z-50 flex flex-col lg:translate-x-0 -translate-x-full transition-transform duration-300 shadow-2xl"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

        <!-- LOGO -->
        <div class="mb-8 px-2">
            <h1 class="text-white font-bold text-lg tracking-tight">{{ strtoupper(\App\Models\Setting::get('store_name', 'KasirPro')) }}</h1>
            <p class="text-xs text-gray-500 uppercase tracking-widest">SaaS Dashboard</p>
        </div>

        <nav class="flex-1 overflow-y-auto custom-scrollbar px-1">
            <!-- DASHBOARD -->
            <div class="mb-6">
                <p class="text-[11px] text-gray-500 uppercase font-black tracking-widest mb-2 px-3">Dashboard</p>
                <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2.5 rounded-lg hover:bg-gray-800 transition-colors {{ request()->routeIs('dashboard') ? 'active-link' : '' }}">
                    <i class="fas fa-th-large w-6 mr-1 text-sm"></i> Overview
                </a>
            </div>

            <!-- TRANSAKSI -->
            <div class="mb-6">
                <p class="text-[11px] text-gray-500 uppercase font-black tracking-widest mb-2 px-3">Transaksi</p>
                <div class="space-y-1">
                    <a href="{{ route('pos.index') }}" class="flex items-center px-3 py-2.5 rounded-lg hover:bg-gray-800 transition-colors {{ request()->routeIs('pos.*') ? 'active-link' : '' }}">
                        <i class="fas fa-shopping-cart w-6 mr-1 text-sm"></i> POS Kasir
                    </a>
                    <a href="{{ route('transactions.index') }}" class="flex items-center px-3 py-2.5 rounded-lg hover:bg-gray-800 transition-colors {{ request()->routeIs('transactions.*') ? 'active-link' : '' }}">
                        <i class="fas fa-receipt w-6 mr-1 text-sm"></i> Daftar Transaksi
                    </a>
                </div>
            </div>

            @if(auth()->user()->isOwner())
            <!-- MANAJEMEN -->
            <div class="mb-6">
                <p class="text-[11px] text-gray-500 uppercase font-black tracking-widest mb-2 px-3">Manajemen</p>

                <div class="space-y-1">
                    <a href="{{ route('shifts.index') }}" class="flex items-center px-3 py-2.5 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('shifts.*') ? 'active-link' : '' }}">
                        <i class="fas fa-clock w-6 mr-1 text-sm"></i> Sesi Shift
                    </a>
                    <a href="{{ route('products.index') }}" class="flex items-center px-3 py-2.5 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('products.*') ? 'active-link' : '' }}">
                        <i class="fas fa-box w-6 mr-1 text-sm"></i> Katalog Produk
                    </a>
                    <a href="{{ route('categories.index') }}" class="flex items-center px-3 py-2.5 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('categories.*') ? 'active-link' : '' }}">
                        <i class="fas fa-tags w-6 mr-1 text-sm"></i> Kategori Produk
                    </a>
                    <a href="{{ route('stock.index') }}" class="flex items-center px-3 py-2.5 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('stock.*') ? 'active-link' : '' }}">
                        <i class="fas fa-warehouse w-6 mr-1 text-sm"></i> Gudang Stok
                    </a>
                </div>
            </div>

            <!-- KEUANGAN -->
            <div class="mb-6">
                <p class="text-[11px] text-gray-500 uppercase font-black tracking-widest mb-2 px-3">Keuangan & Analisis</p>

                <div class="space-y-1">
                    <a href="{{ route('cashflow.index') }}" class="flex items-center px-3 py-2.5 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('cashflow.*') ? 'active-link' : '' }}">
                        <i class="fas fa-money-bill-transfer w-6 mr-1 text-sm"></i> Arus Kas
                    </a>
                    <a href="{{ route('sales.index') }}" class="flex items-center px-3 py-2.5 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('sales.*') ? 'active-link' : '' }}">
                        <i class="fas fa-chart-line w-6 mr-1 text-sm"></i> Analisa Penjualan
                    </a>
                    <a href="{{ route('reports.financial') }}" class="flex items-center px-3 py-2.5 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('reports.financial') ? 'active-link' : '' }}">
                        <i class="fas fa-file-invoice-dollar w-6 mr-1 text-sm"></i> Laporan Laba Rugi
                    </a>
                    <a href="{{ route('reports.shifts') }}" class="flex items-center px-3 py-2.5 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('reports.shifts') ? 'active-link' : '' }}">
                        <i class="fas fa-history w-6 mr-1 text-sm"></i> Laporan Shift
                    </a>
                </div>
            </div>

            <!-- LAINNYA -->
            <div class="mb-6">
                <p class="text-[11px] text-gray-500 uppercase font-black tracking-widest mb-2 px-3">Lainnya</p>

                <div class="space-y-1">
                    <a href="{{ route('team.index') }}" class="flex items-center px-3 py-2.5 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('team.*') ? 'active-link' : '' }}">
                        <i class="fas fa-users-gear w-6 mr-1 text-sm"></i> Manajemen Tim
                    </a>
                    <a href="{{ route('settings.index') }}" class="flex items-center px-3 py-2.5 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('settings.*') ? 'active-link' : '' }}">
                        <i class="fas fa-sliders w-6 mr-1 text-sm"></i> Pengaturan Toko
                    </a>
                </div>
            </div>
            @endif
        </nav>

        <!-- USER FOOTER -->
        <div class="mt-auto pt-4 border-t border-slate-800/50">
            <div class="flex items-center gap-3 px-3">
                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-xs">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-white truncate uppercase">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-gray-500 uppercase">{{ auth()->user()->role }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-500 hover:text-red-400 transition-colors">
                        <i class="fas fa-power-off text-xs"></i>
                    </button>
                </form>
            </div>
        </div>

    </aside>

    {{-- Main Content --}}
    <div class="flex-1 lg:ml-64 min-h-screen flex flex-col">
        {{-- Topbar --}}
        <header class="sticky top-0 z-30 bg-slate-900/60 backdrop-blur-md border-b border-slate-800/50 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = true" class="lg:hidden text-slate-400 hover:text-white transition-colors">
                    <i class="fas fa-bars-staggered text-xl"></i>
                </button>
                <div>
                    <h1 class="text-lg font-black text-white tracking-tight uppercase">@yield('page-title', 'Dashboard')</h1>
                </div>
            </div>
            
            <div class="flex items-center gap-6">
                <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-900/50 border border-slate-800/50 text-[11px] font-bold text-slate-400 tracking-wider uppercase">
                    <i class="far fa-clock text-blue-500"></i>
                    <span id="clock"></span>
                </div>
            </div>
        </header>

        {{-- Alerts --}}
        <div class="px-6 pt-4">
            @if(session('success'))
            <div class="alert mb-4 flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-5 py-4 rounded-2xl text-sm shadow-xl shadow-emerald-950/20">
                <i class="fas fa-check-circle text-lg"></i>
                <span class="font-medium">{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="ml-auto hover:text-white transition-colors"><i class="fas fa-times"></i></button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert mb-4 flex items-center gap-3 bg-red-500/10 border border-red-500/20 text-red-400 px-5 py-4 rounded-2xl text-sm shadow-xl shadow-red-950/20">
                <i class="fas fa-exclamation-triangle text-lg"></i>
                <span class="font-medium">{{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" class="ml-auto hover:text-white transition-colors"><i class="fas fa-times"></i></button>
            </div>
            @endif
        </div>

        {{-- Page Content --}}
        <main class="flex-1 px-6 pb-8 pt-4">
            @yield('content')
        </main>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit',second:'2-digit'});
            document.getElementById('clock').textContent = timeString;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
    @stack('scripts')
</body>
</html>
