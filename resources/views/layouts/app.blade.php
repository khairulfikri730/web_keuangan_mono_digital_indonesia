<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KasirPro') — {{ \App\Models\Setting::get('store_name', 'KasirPro') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Apply theme BEFORE page render to prevent flash
        (function() {
            const saved = localStorage.getItem('monoframe-theme');
            if (saved === 'light') {
                document.documentElement.classList.remove('dark');
            }
        })();

        tailwind.config = {
            darkMode: 'class',
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }

        /* ===== DARK MODE (default) ===== */
        html.dark body { background-color: #0f172a; color: #e2e8f0; }
        html.dark .sidebar-bg { background-color: #0B1120; }
        html.dark .topbar-bg { background-color: rgba(15,23,42,0.6); border-color: rgba(30,41,59,0.5); }
        html.dark .card { background-color: rgba(30,41,59,0.5); border-color: rgba(255,255,255,0.05); }
        html.dark ::-webkit-scrollbar-track { background: #0B1120; }
        html.dark ::-webkit-scrollbar-thumb { background: #334155; }

        /* ===== LIGHT MODE ===== */
        html:not(.dark) body { background-color: #f1f5f9; color: #1e293b; }
        html:not(.dark) .sidebar-bg { background-color: #1e293b; }
        html:not(.dark) .topbar-bg { background-color: rgba(255,255,255,0.85); border-color: #e2e8f0; backdrop-filter: blur(12px); }
        html:not(.dark) .topbar-bg h1,
        html:not(.dark) .topbar-bg button { color: #334155; }
        html:not(.dark) .topbar-bg .text-slate-400 { color: #64748b !important; }
        html:not(.dark) .card { background-color: #ffffff; border-color: #e2e8f0; color: #1e293b; }
        html:not(.dark) ::-webkit-scrollbar-track { background: #f1f5f9; }
        html:not(.dark) ::-webkit-scrollbar-thumb { background: #cbd5e1; }

        /* Light mode - Global Wildcard Overrides & Layout Elements */
        html:not(.dark) body, 
        html:not(.dark) main,
        html:not(.dark) .sidebar-bg,
        html:not(.dark) .topbar-bg {
            background-color: #f8fafc !important; 
            border-color: #e2e8f0 !important;
        }

        /* Override sidebar active/hover states in light mode */
        html:not(.dark) .sidebar-bg a:hover,
        html:not(.dark) .sidebar-bg .bg-gray-800 {
            background-color: #f1f5f9 !important;
            color: #2563eb !important;
            font-weight: 800 !important;
        }
        
        /* Force ALL text in sidebar to be BLACK */
        html:not(.dark) .sidebar-bg,
        html:not(.dark) .sidebar-bg a,
        html:not(.dark) .sidebar-bg p,
        html:not(.dark) .sidebar-bg span,
        html:not(.dark) .sidebar-bg i,
        html:not(.dark) .sidebar-bg .text-gray-300,
        html:not(.dark) .sidebar-bg .text-gray-500,
        html:not(.dark) .sidebar-bg .text-slate-400,
        html:not(.dark) .sidebar-bg .text-slate-500,
        html:not(.dark) .sidebar-bg.text-gray-300 {
            color: #000000 !important;
            font-weight: 700 !important;
        }

        html:not(.dark) .topbar-bg .text-white {
            color: #000000 !important;
        }
        
        /* Fix brand logo text in light mode */
        html:not(.dark) .sidebar-bg .text-blue-500,
        html:not(.dark) .sidebar-bg .text-white {
            color: #000000 !important;
            font-weight: 900 !important;
        }

        /* Generic Backgrounds - Exclude Semantic Colored Elements (protect base colored buttons but allow hover variants to be overridden) */
        html:not(.dark) [class*="bg-slate-950"]:not([class*=" bg-blue-"]):not([class^="bg-blue-"]):not([class*=" bg-emerald-"]):not([class^="bg-emerald-"]):not([class*=" bg-red-"]):not([class^="bg-red-"]):not([class*=" bg-amber-"]):not([class^="bg-amber-"]):not([class*=" bg-cyan-"]):not([class^="bg-cyan-"]):not([class*=" bg-purple-"]):not([class^="bg-purple-"]):not([class*="peer-checked:"]),
        html:not(.dark) [class*="bg-slate-900"]:not([class*=" bg-blue-"]):not([class^="bg-blue-"]):not([class*=" bg-emerald-"]):not([class^="bg-emerald-"]):not([class*=" bg-red-"]):not([class^="bg-red-"]):not([class*=" bg-amber-"]):not([class^="bg-amber-"]):not([class*=" bg-cyan-"]):not([class^="bg-cyan-"]):not([class*=" bg-purple-"]):not([class^="bg-purple-"]):not([class*="peer-checked:"]),
        html:not(.dark) [class*="bg-[#0f172a]" i]:not([class*=" bg-blue-"]):not([class^="bg-blue-"]):not([class*=" bg-emerald-"]):not([class^="bg-emerald-"]):not([class*=" bg-red-"]):not([class^="bg-red-"]):not([class*=" bg-amber-"]):not([class^="bg-amber-"]):not([class*=" bg-cyan-"]):not([class^="bg-cyan-"]):not([class*=" bg-purple-"]):not([class^="bg-purple-"]):not([class*="peer-checked:"]),
        html:not(.dark) [class*="bg-[#111827]" i]:not([class*=" bg-blue-"]):not([class^="bg-blue-"]):not([class*=" bg-emerald-"]):not([class^="bg-emerald-"]):not([class*=" bg-red-"]):not([class^="bg-red-"]):not([class*=" bg-amber-"]):not([class^="bg-amber-"]):not([class*=" bg-cyan-"]):not([class^="bg-cyan-"]):not([class*=" bg-purple-"]):not([class^="bg-purple-"]):not([class*="peer-checked:"]),
        html:not(.dark) [class*="bg-[#1e293b]" i]:not([class*=" bg-blue-"]):not([class^="bg-blue-"]):not([class*=" bg-emerald-"]):not([class^="bg-emerald-"]):not([class*=" bg-red-"]):not([class^="bg-red-"]):not([class*=" bg-amber-"]):not([class^="bg-amber-"]):not([class*=" bg-cyan-"]):not([class^="bg-cyan-"]):not([class*=" bg-purple-"]):not([class^="bg-purple-"]):not([class*="peer-checked:"]) { 
            background-color: #ffffff !important; border-color: #e2e8f0 !important; 
        }
        
        html:not(.dark) [class*="bg-slate-800"]:not([class*=" bg-blue-"]):not([class^="bg-blue-"]):not([class*=" bg-emerald-"]):not([class^="bg-emerald-"]):not([class*=" bg-red-"]):not([class^="bg-red-"]):not([class*=" bg-amber-"]):not([class^="bg-amber-"]):not([class*=" bg-cyan-"]):not([class^="bg-cyan-"]):not([class*=" bg-purple-"]):not([class^="bg-purple-"]):not([class*="peer-checked:"]) { 
            background-color: #ffffff !important; border-color: #e2e8f0 !important; 
        }
        html:not(.dark) [class*="hover:bg-slate-800"]:hover:not([class*=" bg-blue-"]):not([class^="bg-blue-"]):not([class*=" bg-emerald-"]):not([class^="bg-emerald-"]):not([class*=" bg-red-"]):not([class^="bg-red-"]):not([class*=" bg-amber-"]):not([class^="bg-amber-"]):not([class*=" bg-cyan-"]):not([class^="bg-cyan-"]):not([class*=" bg-purple-"]):not([class^="bg-purple-"]):not([class*="peer-checked:"]) { 
            background-color: #f1f5f9 !important; 
        }
        
        html:not(.dark) [class*="bg-slate-700"]:not([class*=" bg-blue-"]):not([class^="bg-blue-"]):not([class*=" bg-emerald-"]):not([class^="bg-emerald-"]):not([class*=" bg-red-"]):not([class^="bg-red-"]):not([class*=" bg-amber-"]):not([class^="bg-amber-"]):not([class*=" bg-cyan-"]):not([class^="bg-cyan-"]):not([class*=" bg-purple-"]):not([class^="bg-purple-"]):not([class*="peer-checked:"]) { 
            background-color: #f1f5f9 !important; border-color: #e2e8f0 !important; 
        }
        html:not(.dark) [class*="hover:bg-slate-700"]:hover:not([class*=" bg-blue-"]):not([class^="bg-blue-"]):not([class*=" bg-emerald-"]):not([class^="bg-emerald-"]):not([class*=" bg-red-"]):not([class^="bg-red-"]):not([class*=" bg-amber-"]):not([class^="bg-amber-"]):not([class*=" bg-cyan-"]):not([class^="bg-cyan-"]):not([class*=" bg-purple-"]):not([class^="bg-purple-"]):not([class*="peer-checked:"]) { 
            background-color: #e2e8f0 !important; 
        }

        /* Custom Modal and Components Override for Light Mode */
        html:not(.dark) .glass-modal { background: rgba(255, 255, 255, 0.95) !important; }
        html:not(.dark) .bg-navy-900\/60 { background-color: rgba(241, 245, 249, 0.6) !important; }

        /* Handle custom toggles in light mode */
        html:not(.dark) .peer:not(:checked) ~ div[class*="peer-checked:bg-"] { background-color: #cbd5e1 !important; }

        html:not(.dark) [class*="border-white/"],
        html:not(.dark) [class*="border-slate-800"],
        html:not(.dark) [class*="border-slate-700"] { border-color: #e2e8f0 !important; }

        /* Typography overrides (excluding elements with colored backgrounds like badges) */
        html:not(.dark) h1.text-white, html:not(.dark) h2.text-white, html:not(.dark) h3.text-white, 
        html:not(.dark) h4.text-white, html:not(.dark) p.text-white, html:not(.dark) label.text-white,
        html:not(.dark) span.text-white:not([class*="bg-"]), html:not(.dark) div.text-white:not([class*="bg-"]),
        html:not(.dark) i.text-white:not([class*="bg-"]):not(.fas):not(.far), html:not(.dark) a.text-white:not([class*="bg-"]) { color: #1e293b !important; }
        
        /* Force text on slate buttons to be dark in light mode */
        html:not(.dark) button.text-white:not([class*="bg-blue-"]):not([class*="bg-emerald-"]):not([class*="bg-red-"]):not([class*="bg-amber-"]):not([class*="bg-cyan-"]):not([class*="bg-purple-"]) {
            color: #1e293b !important;
            font-weight: 800 !important;
        }

        html:not(.dark) [class*="text-slate-200"], html:not(.dark) [class*="text-slate-300"] { color: #334155 !important; }
        html:not(.dark) [class*="text-slate-400"] { color: #64748b !important; }
        html:not(.dark) [class*="text-slate-500"] { color: #94a3b8 !important; }

        /* Inline Style Overrides (For pages like Catalog) & Custom Classes */
        html:not(.dark) [style*="rgba(15,23,42"], html:not(.dark) [style*="rgba(15, 23, 42"] { background-color: #f8fafc !important; }
        html:not(.dark) [style*="rgba(30,41,59"], html:not(.dark) [style*="rgba(30, 41, 59"] { background-color: #ffffff !important; }
        html:not(.dark) [style*="rgba(71,85,105"], html:not(.dark) [style*="rgba(71, 85, 105"] { border-color: #e2e8f0 !important; }
        html:not(.dark) .stat-card { background: #ffffff !important; border-color: #e2e8f0 !important; }

        /* Form Controls */
        html:not(.dark) input:not([type=checkbox]):not([type=radio]):not([type=color]),
        html:not(.dark) select,
        html:not(.dark) textarea {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            color: #1e293b !important;
        }
        html:not(.dark) input::placeholder,
        html:not(.dark) textarea::placeholder { color: #94a3b8 !important; }

        html:not(.dark) main .shadow-2xl { box-shadow: 0 4px 24px rgba(0,0,0,0.06) !important; }

        /* Light mode - POS specific */
        html:not(.dark) .pos-height { background-color: #f1f5f9 !important; color: #1e293b !important; }
        html:not(.dark) .pos-height .bg-slate-900 { background-color: #f1f5f9 !important; }
        html:not(.dark) .pos-height .bg-slate-800 { background-color: #ffffff !important; border-color: #e2e8f0 !important; }
        html:not(.dark) .pos-height .bg-slate-700 { background-color: #f1f5f9 !important; }
        html:not(.dark) .pos-height .text-white { color: #1e293b !important; }
        html:not(.dark) .pos-height .text-slate-200,
        html:not(.dark) .pos-height .text-slate-300 { color: #334155 !important; }
        html:not(.dark) .pos-height .text-slate-400 { color: #64748b !important; }
        html:not(.dark) .pos-height .border-white\/5,
        html:not(.dark) .pos-height .border-white\/10 { border-color: #e2e8f0 !important; }
        html:not(.dark) .pos-height input,
        html:not(.dark) .pos-height select,
        html:not(.dark) .pos-height textarea {
            background-color: #f8fafc !important;
            border-color: #e2e8f0 !important;
            color: #1e293b !important;
        }

        /* Light mode - Product cards */
        html:not(.dark) .product-card { background-color: #ffffff !important; }
        html:not(.dark) .product-card .text-white { color: #1e293b !important; }
        html:not(.dark) .product-card .text-indigo-300 { color: #4f46e5 !important; }

        /* Light mode - Worksheet selector */
        html:not(.dark) .bg-\[\#111827\] { background-color: #f8fafc !important; color: #1e293b !important; }
        html:not(.dark) .bg-\[\#111827\] .text-white { color: #1e293b !important; }

        /* Light mode - Notification dropdown */
        html:not(.dark) .topbar-bg .bg-slate-800 { background-color: #ffffff !important; border-color: #e2e8f0 !important; }
        html:not(.dark) .topbar-bg .bg-slate-700\/50:hover { background-color: #f1f5f9 !important; }
        html:not(.dark) .topbar-bg .border-slate-700 { border-color: #e2e8f0 !important; }

        /* Light mode - Topbar clock */
        html:not(.dark) .topbar-bg .bg-slate-900\/50 { background-color: #f1f5f9 !important; border-color: #e2e8f0 !important; }
        html:not(.dark) .topbar-bg .bg-slate-800\/50 { background-color: #f1f5f9 !important; border-color: #e2e8f0 !important; }

        /* Light mode - Tables */
        html:not(.dark) main table { border-color: #e2e8f0; }
        html:not(.dark) main thead { background-color: #f8fafc !important; }
        html:not(.dark) main th { color: #475569 !important; border-color: #e2e8f0 !important; }
        html:not(.dark) main td { border-color: #f1f5f9 !important; }
        html:not(.dark) main tbody tr:hover { background-color: #f8fafc !important; }

        /* Light mode - Modals (teleported to body) */
        html:not(.dark) [x-teleport] .bg-slate-800,
        html:not(.dark) body > div[x-show] .bg-slate-800 { background-color: #ffffff !important; border-color: #e2e8f0 !important; }
        html:not(.dark) [x-teleport] .bg-slate-800\/50,
        html:not(.dark) body > div[x-show] .bg-slate-800\/50 { background-color: #f8fafc !important; }
        html:not(.dark) [x-teleport] .text-white,
        html:not(.dark) body > div[x-show] .text-white { color: #1e293b !important; }
        html:not(.dark) [x-teleport] .text-slate-300,
        html:not(.dark) [x-teleport] .text-slate-400,
        html:not(.dark) body > div[x-show] .text-slate-300,
        html:not(.dark) body > div[x-show] .text-slate-400 { color: #64748b !important; }
        html:not(.dark) [x-teleport] .border-slate-700,
        html:not(.dark) [x-teleport] .border-slate-700\/50,
        html:not(.dark) body > div[x-show] .border-slate-700,
        html:not(.dark) body > div[x-show] .border-slate-700\/50 { border-color: #e2e8f0 !important; }
        html:not(.dark) [x-teleport] input,
        html:not(.dark) [x-teleport] select,
        html:not(.dark) [x-teleport] textarea,
        html:not(.dark) body > div[x-show] input,
        html:not(.dark) body > div[x-show] select,
        html:not(.dark) body > div[x-show] textarea {
            background-color: #f8fafc !important;
            border-color: #e2e8f0 !important;
            color: #1e293b !important;
        }
        html:not(.dark) [x-teleport] .bg-slate-900\/50,
        html:not(.dark) body > div[x-show] .bg-slate-900\/50 { background-color: #f1f5f9 !important; }
        html:not(.dark) [x-teleport] .bg-slate-700,
        html:not(.dark) body > div[x-show] .bg-slate-700 { background-color: #e2e8f0 !important; }

        /* Light mode - Hover states */
        html:not(.dark) main .hover\:bg-slate-700:hover { background-color: #e2e8f0 !important; }
        html:not(.dark) main .hover\:bg-slate-800:hover { background-color: #f1f5f9 !important; }

        /* Smooth theme transition */
        body, .sidebar-bg, .topbar-bg, main, .card, .product-card, .pos-height, .pos-height > div {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        /* ===== COMMON (both modes) ===== */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-thumb { border-radius: 9999px; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.02); border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.2); }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        .active-link { background-color: #00FF7F !important; color: #000000 !important; font-weight: 900 !important; border-radius: 0.5rem !important; }
        .active-link i { color: #000000 !important; }
        [x-cloak] { display: none !important; }
        .transition-premium { transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
        .group:hover .group-hover\:bounce { animation: bounce 1s infinite; }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        /* Theme toggle button animation */
        .theme-toggle-icon { transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease; }
        .theme-toggle:hover .theme-toggle-icon { transform: rotate(30deg) scale(1.1); }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen flex font-inter" style="padding-top: env(safe-area-inset-top); padding-bottom: env(safe-area-inset-bottom);" x-data="{ sidebarOpen: false, darkMode: document.documentElement.classList.contains('dark') }">
    @include('components.report-export-modal')

    {{-- Mobile Overlay --}}
    <div class="fixed inset-0 bg-black/60 z-[140] lg:hidden" x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak></div>

    {{-- Sidebar --}}
    <aside class="fixed top-0 bottom-0 left-0 w-64 sidebar-bg text-gray-300 p-4 z-[150] flex flex-col lg:translate-x-0 -translate-x-full transition-transform duration-300 shadow-2xl"
           style="padding-bottom: calc(1rem + env(safe-area-inset-bottom));"
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
                <a href="{{ route('dashboard') }}" @click="sidebarOpen = false" class="flex items-center px-3 py-2.5 rounded-lg hover:bg-gray-800 transition-colors {{ request()->routeIs('dashboard') ? 'active-link' : '' }}">
                    <i class="fas fa-th-large w-6 mr-1 text-sm"></i> Overview
                </a>
            </div>

            <!-- TRANSAKSI -->
            <div class="mb-6">
                <p class="text-[11px] text-gray-500 uppercase font-black tracking-widest mb-2 px-3">Transaksi</p>
                <div class="space-y-1">
                    @if(auth()->user()->hasPermission('pos'))
                    <a href="{{ route('pos.index') }}" @click="sidebarOpen = false" class="flex items-center px-3 py-2.5 rounded-lg hover:bg-gray-800 transition-colors {{ request()->routeIs('pos.*') ? 'active-link' : '' }}">
                        <i class="fas fa-shopping-cart w-6 mr-1 text-sm"></i> POS Kasir
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('transactions.view'))
                    <a href="{{ route('transactions.index') }}" @click="sidebarOpen = false" class="flex items-center px-3 py-2.5 rounded-lg hover:bg-gray-800 transition-colors {{ request()->routeIs('transactions.*') ? 'active-link' : '' }}">
                        <i class="fas fa-receipt w-6 mr-1 text-sm"></i> Daftar Transaksi
                    </a>
                    @endif
                </div>
            </div>

            <!-- MANAJEMEN -->
            @if(auth()->user()->hasPermission('shifts.view') || auth()->user()->hasPermission('products.view') || auth()->user()->hasPermission('categories.view') || auth()->user()->hasPermission('stock.view'))
            <div class="mb-6">
                <p class="text-[11px] text-gray-500 uppercase font-black tracking-widest mb-2 px-3">Manajemen</p>

                <div class="space-y-1">
                    @if(auth()->user()->hasPermission('shifts.view'))
                    <a href="{{ route('shifts.index') }}" @click="sidebarOpen = false" class="flex items-center px-3 py-2.5 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('shifts.*') ? 'active-link' : '' }}">
                        <i class="fas fa-clock w-6 mr-1 text-sm"></i> Sesi Shift
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('products.view'))
                    <a href="{{ route('products.index') }}" @click="sidebarOpen = false" class="flex items-center px-3 py-2.5 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('products.*') ? 'active-link' : '' }}">
                        <i class="fas fa-box w-6 mr-1 text-sm"></i> Katalog Produk
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('categories.view'))
                    <a href="{{ route('categories.index') }}" @click="sidebarOpen = false" class="flex items-center px-3 py-2.5 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('categories.*') ? 'active-link' : '' }}">
                        <i class="fas fa-tags w-6 mr-1 text-sm"></i> Kategori Produk
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('stock.view'))
                    @php
                        $sidebarStockAlerts = \App\Models\Product::active()
                            ->whereNotIn('product_kind', ['unlimited','service'])
                            ->where(function($q) {
                                $q->where('stock', '<=', 0)
                                  ->orWhereColumn('stock', '<=', 'min_stock');
                            })
                            ->where('min_stock', '>', 0)
                            ->count();
                    @endphp
                    <a href="{{ route('stock.index') }}" @click="sidebarOpen = false" class="flex items-center justify-between px-3 py-2.5 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('stock.*') ? 'active-link' : '' }}">
                        <span class="flex items-center"><i class="fas fa-warehouse w-6 mr-1 text-sm"></i> Gudang Stok</span>
                        @if($sidebarStockAlerts > 0)
                        <span class="w-5 h-5 bg-red-500 text-white text-[10px] font-black rounded-full flex items-center justify-center shadow-lg shadow-red-500/20 animate-pulse">{{ $sidebarStockAlerts }}</span>
                        @endif
                    </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- KEUANGAN -->
            @if(auth()->user()->hasPermission('cashflow.view') || auth()->user()->hasPermission('sales.view') || auth()->user()->hasPermission('reports_financial') || auth()->user()->hasPermission('reports_shifts') || auth()->user()->hasPermission('capitals.view') || auth()->user()->hasPermission('monthly_expenses.view') || auth()->user()->hasPermission('expense_categories.view'))
            <div class="mb-6">
                <p class="text-[11px] text-gray-500 uppercase font-black tracking-widest mb-2 px-3">Keuangan & Analisis</p>

                <div class="space-y-1">
                    @if(auth()->user()->hasPermission('cashflow.view'))
                    <a href="{{ route('cashflow.index') }}" @click="sidebarOpen = false" class="flex items-center px-3 py-2.5 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('cashflow.*') ? 'active-link' : '' }}">
                        <i class="fas fa-money-bill-transfer w-6 mr-1 text-sm"></i> Cashflow
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('sales.view'))
                    <a href="{{ route('sales.index') }}" @click="sidebarOpen = false" class="flex items-center px-3 py-2.5 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('sales.*') ? 'active-link' : '' }}">
                        <i class="fas fa-chart-line w-6 mr-1 text-sm"></i> Analisa Penjualan
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('capitals.view'))
                    <a href="{{ route('capitals.index') }}" @click="sidebarOpen = false" class="flex items-center px-3 py-2.5 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('capitals.*') ? 'active-link' : '' }}">
                        <i class="fas fa-wallet w-6 mr-1 text-sm"></i> Modal Usaha
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('monthly_expenses.view'))
                    <a href="{{ route('monthly_expenses.index') }}" @click="sidebarOpen = false" class="flex items-center px-3 py-2.5 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('monthly_expenses.*') ? 'active-link' : '' }}">
                        <i class="fas fa-file-invoice w-6 mr-1 text-sm"></i> Biaya Bulanan
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('expense_categories.view'))
                    <a href="{{ route('expense_categories.index') }}" @click="sidebarOpen = false" class="flex items-center px-3 py-2.5 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('expense_categories.*') ? 'active-link' : '' }}">
                        <i class="fas fa-list-check w-6 mr-1 text-sm"></i> Master Jenis Biaya
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('reports_financial'))
                    <a href="{{ route('reports.financial') }}" @click="sidebarOpen = false" class="flex items-center px-3 py-2.5 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('reports.financial') ? 'active-link' : '' }}">
                        <i class="fas fa-file-invoice-dollar w-6 mr-1 text-sm"></i> Laporan Laba Rugi
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('reports_shifts'))
                    <a href="{{ route('reports.shifts') }}" @click="sidebarOpen = false" class="flex items-center px-3 py-2.5 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('reports.shifts') ? 'active-link' : '' }}">
                        <i class="fas fa-history w-6 mr-1 text-sm"></i> Laporan Shift
                    </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- INVOICE GENERATOR -->
            @if(auth()->user()->hasPermission('invoices.view'))
            <div class="mb-6">
                <p class="text-[11px] text-gray-500 uppercase font-black tracking-widest mb-2 px-3">Invoice Generator</p>
                <div class="space-y-1">
                    <a href="{{ route('invoices.index') }}" @click="sidebarOpen = false" class="flex items-center px-3 py-2.5 rounded-lg hover:bg-gray-800 transition-colors {{ request()->routeIs('invoices.index') ? 'active-link' : '' }}">
                        <i class="fas fa-file-invoice w-6 mr-1 text-sm text-blue-400"></i> Semua Invoice
                    </a>
                    <a href="{{ route('invoices.create') }}" @click="sidebarOpen = false" class="flex items-center px-3 py-2.5 rounded-lg hover:bg-gray-800 transition-colors {{ request()->routeIs('invoices.create') ? 'active-link' : '' }}">
                        <i class="fas fa-plus-circle w-6 mr-1 text-sm text-emerald-400"></i> Buat Invoice
                    </a>
                </div>
            </div>
            @endif

            <!-- LAINNYA (Owner only) -->
            @if(auth()->user()->hasPermission('team.view') || auth()->user()->hasPermission('settings') || auth()->user()->hasPermission('schedules.view'))
            <div class="mb-6">
                <p class="text-[11px] text-gray-500 uppercase font-black tracking-widest mb-2 px-3">Lainnya</p>

                <div class="space-y-1">
                    @if(auth()->user()->hasPermission('team.view'))
                    <a href="{{ route('team.index') }}" @click="sidebarOpen = false" class="flex items-center px-3 py-2.5 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('team.*') ? 'active-link' : '' }}">
                        <i class="fas fa-users-gear w-6 mr-1 text-sm"></i> Manajemen Tim
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('schedules.view'))
                    <a href="{{ route('schedules.index') }}" @click="sidebarOpen = false" class="flex items-center justify-between px-3 py-2.5 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('schedules.*') ? 'active-link' : '' }}">
                        <span class="flex items-center"><i class="fas fa-calendar-alt w-6 mr-1 text-sm text-yellow-400"></i> Jadwal Kerja</span>
                        <span class="px-1.5 py-0.5 bg-yellow-500 text-black text-[9px] font-black rounded-sm tracking-wider uppercase">Baru</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('settings'))
                    <a href="{{ route('settings.index') }}" @click="sidebarOpen = false" class="flex items-center px-3 py-2.5 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('settings.*') ? 'active-link' : '' }}">
                        <i class="fas fa-sliders w-6 mr-1 text-sm"></i> Pengaturan Toko
                    </a>
                    @endif
                </div>
            </div>
            @endif
        </nav>

        <!-- USER FOOTER -->
        <div class="mt-auto pt-4 border-t border-slate-800/50" x-data="{ profileOpen: false }">
            <button @click="profileOpen = !profileOpen" class="flex items-center gap-3 px-3 py-2 w-full hover:bg-slate-800/60 rounded-xl transition-colors">
                <img src="{{ auth()->user()->avatarUrl() }}" class="w-9 h-9 rounded-full ring-2 ring-{{ auth()->user()->roleColor() }}-500/30 object-cover shrink-0" alt="{{ auth()->user()->name }}">
                <div class="flex-1 min-w-0 text-left">
                    <p class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[9px] font-black text-{{ auth()->user()->roleColor() }}-400 uppercase tracking-wider">{{ auth()->user()->roleBadge() }}</p>
                </div>
                <i class="fas fa-chevron-down text-[10px] text-slate-500 transition-transform" :class="profileOpen ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="profileOpen" @click.away="profileOpen = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="bg-slate-800 border border-white/5 rounded-2xl shadow-2xl mx-2 overflow-hidden mt-1">
                <a href="{{ route('account.profile') }}" class="flex items-center gap-3 px-4 py-3 text-xs font-bold text-slate-400 hover:bg-slate-700 hover:text-white transition-colors">
                    <i class="fas fa-user-cog w-4 text-center text-blue-400"></i> Pengaturan Akun
                </a>

                <div class="border-t border-white/5"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-3 px-4 py-3 text-xs font-bold text-red-400 hover:bg-red-500/10 w-full text-left transition-colors">
                        <i class="fas fa-power-off w-4 text-center"></i> Logout
                    </button>
                </form>
            </div>
        </div>

    </aside>

    {{-- Main Content --}}
    <div class="flex-1 min-w-0 lg:ml-64 min-h-screen flex flex-col">
        {{-- Topbar --}}
        <header class="fixed top-0 right-0 left-0 lg:left-64 z-[100] topbar-bg backdrop-blur-md border-b px-6 py-4 flex items-center justify-between transition-all">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = true" class="lg:hidden text-slate-400 hover:text-white transition-colors">
                    <i class="fas fa-bars-staggered text-xl"></i>
                </button>
                <div>
                    <h1 class="text-lg font-black text-white tracking-tight uppercase">@yield('page-title', 'Dashboard')</h1>
                </div>
            </div>
            
            <div class="flex items-center gap-4">

                {{-- Notification Bell --}}
                @php
                    $stockQuery = \App\Models\Product::active()
                        ->where('product_kind', '!=', 'unlimited')
                        ->where('product_kind', '!=', 'service');
                    
                    if (isset($activeWorksheetId) && $activeWorksheetId !== 'all') {
                        $stockQuery->where('worksheet_id', $activeWorksheetId);
                    } elseif (!auth()->user()->isOwner() && isset($userWorksheets)) {
                        $stockQuery->whereIn('worksheet_id', $userWorksheets->pluck('id'));
                    }

                    $lowStockAlert = (clone $stockQuery)
                        ->whereColumn('stock', '<=', 'min_stock')
                        ->where('min_stock', '>', 0)
                        ->with('category')
                        ->get();
                        
                    $outOfStockAlert = (clone $stockQuery)
                        ->where('stock', '<=', 0)
                        ->with('category')
                        ->get();
                        
                    $pendingShifts = auth()->user()->hasPermission('shifts.manage') ? \App\Models\Shift::where('status', 'pending_approval')->get() : collect();
                    $totalAlerts = $lowStockAlert->count() + $outOfStockAlert->count() + $pendingShifts->count();
                @endphp
                <div class="relative" x-data="{ notifOpen: false }" @click.outside="notifOpen = false">
                    <button @click="notifOpen = !notifOpen" class="relative w-9 h-9 rounded-xl bg-slate-800/50 border border-slate-700/50 hover:bg-slate-700 text-slate-400 hover:text-white inline-flex items-center justify-center transition-all">
                        <i class="fas fa-bell text-sm"></i>
                        @if($totalAlerts > 0)
                        <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-[10px] font-black rounded-full flex items-center justify-center shadow-lg shadow-red-500/30 animate-pulse">{{ $totalAlerts }}</span>
                        @endif
                    </button>

                    {{-- Dropdown --}}
                    <div x-show="notifOpen" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 -translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                         class="absolute right-0 top-12 w-80 bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl shadow-black/50 overflow-hidden z-50">
                        <div class="p-4 border-b border-slate-700 flex justify-between items-center">
                            <h3 class="text-sm font-black text-white"><i class="fas fa-bell text-amber-400 mr-2"></i>Notifikasi Sistem</h3>
                            @if($totalAlerts > 0)
                            <span class="text-[10px] font-bold bg-red-500/10 text-red-400 px-2 py-0.5 rounded-full border border-red-500/20">{{ $totalAlerts }} peringatan</span>
                            @endif
                        </div>
                        <div class="max-h-80 overflow-y-auto">
                            @if($totalAlerts === 0)
                                <div class="p-6 text-center">
                                    <i class="fas fa-check-circle text-3xl text-emerald-400 mb-2"></i>
                                    <p class="text-sm text-slate-400 font-medium">Semua stok aman!</p>
                                </div>
                            @else
                                @foreach($outOfStockAlert as $p)
                                <a href="{{ route('stock.index', ['action' => 'restock', 'product_id' => $p->id]) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-700/50 transition-colors border-b border-slate-700/50" title="Klik untuk melakukan Restock">
                                    <div class="w-8 h-8 rounded-lg bg-red-500/10 border border-red-500/20 flex items-center justify-center shrink-0">
                                        <i class="fas fa-times-circle text-red-400 text-xs"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-white truncate">{{ $p->name }}</p>
                                        <p class="text-[10px] text-red-400 font-bold">STOK HABIS · {{ $p->stock }} tersisa</p>
                                    </div>
                                </a>
                                @endforeach
                                @foreach($lowStockAlert as $p)
                                <a href="{{ route('stock.index', ['action' => 'restock', 'product_id' => $p->id]) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-700/50 transition-colors border-b border-slate-700/50" title="Klik untuk melakukan Restock">
                                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center shrink-0">
                                        <i class="fas fa-exclamation-triangle text-amber-400 text-xs"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-white truncate">{{ $p->name }}</p>
                                        <p class="text-[10px] text-amber-400 font-bold">STOK MENIPIS · {{ $p->stock }}/{{ $p->min_stock }} (min)</p>
                                    </div>
                                </a>
                                @endforeach
                            @endif

                            @if($pendingShifts->count() > 0)
                                <div class="px-4 py-2 bg-slate-900 border-y border-slate-700/50 flex justify-between items-center sticky top-0 z-10">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest"><i class="fas fa-clock text-amber-400 mr-1.5"></i>Persetujuan Shift</span>
                                    <span class="text-[9px] font-bold bg-amber-500/10 text-amber-400 px-2 py-0.5 rounded-full">{{ $pendingShifts->count() }} menunggu</span>
                                </div>
                                @foreach($pendingShifts as $ps)
                                <a href="{{ route('reports.shifts', ['status' => 'pending_approval']) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-700/50 transition-colors border-b border-slate-700/50">
                                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center shrink-0">
                                        <i class="fas fa-lock text-amber-400 text-xs"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-white truncate">Shift Kasir: {{ $ps->opener->name ?? 'User' }}</p>
                                        <p class="text-[10px] text-amber-400 font-bold">
                                            {{ auth()->user()->isOwner() ? 'Menunggu Persetujuan Anda' : 'Menunggu Persetujuan Admin' }}
                                        </p>
                                    </div>
                                </a>
                                @endforeach
                            @endif
                        </div>
                        @if($totalAlerts > 0)
                        <a href="{{ route('products.index', ['stock_status' => 'low']) }}" class="block p-3 text-center text-xs font-bold text-blue-400 hover:bg-slate-700/50 transition-colors border-t border-slate-700">
                            <i class="fas fa-arrow-right mr-1"></i> Kelola Stok
                        </a>
                        @endif
                    </div>
                </div>

                {{-- Theme Toggle --}}
                <button @click="darkMode = !darkMode; document.documentElement.classList.toggle('dark'); localStorage.setItem('monoframe-theme', darkMode ? 'dark' : 'light')" 
                        class="theme-toggle w-9 h-9 rounded-xl border inline-flex items-center justify-center transition-all"
                        :class="darkMode ? 'bg-slate-800/50 border-slate-700/50 text-amber-400 hover:bg-slate-700 hover:text-amber-300' : 'bg-white border-slate-200 text-indigo-500 hover:bg-indigo-50 hover:text-indigo-600 shadow-sm'"
                        :title="darkMode ? 'Ganti ke Light Mode' : 'Ganti ke Dark Mode'">
                    <i class="theme-toggle-icon" :class="darkMode ? 'fas fa-sun' : 'fas fa-moon'"></i>
                </button>

                <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-900/50 border border-slate-800/50 text-[11px] font-bold text-slate-400 tracking-wider uppercase">
                    <i class="far fa-clock text-blue-500"></i>
                    <span id="clock"></span>
                </div>
            </div>
        </header>

        {{-- Alerts --}}
        <div class="px-6 pt-4">
            {{-- Flash messages will be handled by SweetAlert2 --}}
        </div>

        {{-- Page Content --}}
        <main class="flex-1 min-w-0 px-4 sm:px-6 pb-8 pt-24">
            {{-- Big Worksheet Selector --}}
            {{-- Big Worksheet Selector --}}
            @if(auth()->user()->isOwner() || (isset($userWorksheets) && $userWorksheets->count() > 0))
            <div class="mb-6 bg-[#0F172A] rounded-2xl border border-emerald-500 shadow-lg shadow-emerald-500/10 relative z-40">
                <div class="bg-emerald-500 px-4 py-2 rounded-t-[15px] border-b border-emerald-600 flex items-center justify-between">
                    <h2 class="text-xs font-black text-white uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-store-alt"></i> WORKSHEET BISNIS / CABANG
                    </h2>
                </div>
                <div class="p-4" x-data="{ wsOpen: false }" @click.outside="wsOpen = false">
                    <div class="flex items-center gap-3">
                        {{-- Dropdown Container --}}
                        <div class="relative flex-1">
                            <button @click="wsOpen = !wsOpen" class="w-full flex items-center justify-between bg-[#111827] border border-emerald-500/50 hover:border-emerald-400 rounded-xl px-4 py-3 text-sm font-bold text-emerald-400 transition-colors shadow-inner focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-md bg-emerald-500/20 flex items-center justify-center">
                                        <i class="fas fa-layer-group text-xs"></i>
                                    </div>
                                    <span class="text-base text-white">
                                        @if(isset($userWorksheets) && $userWorksheets->count() > 0)
                                            {{ $activeWorksheet ? $activeWorksheet->name : 'Pilih Worksheet' }}
                                        @else
                                            Belum Ada Worksheet
                                        @endif
                                    </span>
                                </div>
                                <i class="fas fa-chevron-down text-emerald-500 transition-transform duration-200" :class="wsOpen ? 'rotate-180' : ''"></i>
                            </button>
                            
                            <div x-show="wsOpen" x-cloak x-transition.opacity.duration.200ms class="absolute left-0 right-0 top-full mt-2 bg-slate-800 border border-slate-700 rounded-xl shadow-2xl z-50 overflow-hidden">
                                <div class="max-h-64 overflow-y-auto p-2 space-y-1">
                                    @if(isset($userWorksheets) && $userWorksheets->count() > 0)
                                        @foreach($userWorksheets as $ws)
                                        <form action="{{ route('worksheets.switch') }}" method="POST" class="m-0">
                                            @csrf
                                            <input type="hidden" name="worksheet_id" value="{{ $ws->id }}">
                                            <button type="submit" class="w-full text-left px-4 py-3 rounded-lg text-sm font-bold {{ $activeWorksheetId == $ws->id ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }} flex items-center justify-between transition-colors">
                                                <div class="flex items-center gap-3">
                                                    <i class="fas fa-store {{ $activeWorksheetId == $ws->id ? 'text-emerald-400' : 'text-slate-500' }}"></i>
                                                    <span>{{ $ws->name }}</span>
                                                </div>
                                                @if($activeWorksheetId == $ws->id) <i class="fas fa-check"></i> @endif
                                            </button>
                                        </form>
                                        @endforeach
                                    @else
                                        <div class="px-4 py-3 text-sm text-slate-500 text-center">
                                            Belum ada data worksheet
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        {{-- Action Buttons --}}
                        @if(auth()->user()->isOwner())
                        <div class="flex items-center gap-1.5 shrink-0">
                            {{-- Add --}}
                            <button x-data @click="$dispatch('open-modal', 'add-worksheet')" class="w-10 h-10 md:w-[50px] md:h-[50px] rounded-xl border border-emerald-500/50 hover:border-emerald-400 text-emerald-400 bg-[#111827] flex items-center justify-center transition-all shadow-inner focus:outline-none" title="Tambah Worksheet">
                                <i class="fas fa-plus md:text-lg text-sm"></i>
                            </button>
                            
                            @if($activeWorksheet)
                                {{-- Edit --}}
                                <button x-data @click="$dispatch('open-modal', 'edit-worksheet-{{ $activeWorksheet->id }}')" class="w-10 h-10 md:w-[50px] md:h-[50px] rounded-xl border border-emerald-500/50 hover:border-emerald-400 text-emerald-400 bg-[#111827] flex items-center justify-center transition-all shadow-inner focus:outline-none" title="Edit Worksheet">
                                    <i class="fas fa-pen md:text-lg text-sm"></i>
                                </button>
                                
                                {{-- Delete --}}
                                <form action="{{ route('worksheets.destroy', $activeWorksheet) }}" method="POST" id="form-delete-worksheet" class="m-0">
                                    @csrf @method('DELETE')
                                    <button type="button" 
                                            onclick="Swal.fire({
                                                title: 'Hapus Worksheet?',
                                                text: 'Data yang terkait tidak akan terhapus, tetapi Anda tidak bisa lagi mengaksesnya lewat Worksheet ini.',
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonColor: '#ef4444',
                                                cancelButtonColor: '#64748b',
                                                confirmButtonText: 'Ya, Hapus!',
                                                cancelButtonText: 'Batal'
                                            }).then((result) => {
                                                if (result.isConfirmed) document.getElementById('form-delete-worksheet').submit();
                                            })"
                                            class="w-10 h-10 md:w-[50px] md:h-[50px] rounded-xl bg-red-500 hover:bg-red-600 text-white flex items-center justify-center transition-all shadow-md shadow-red-500/20" title="Hapus Worksheet">
                                        <i class="fas fa-trash md:text-lg text-sm"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const isDark = document.documentElement.classList.contains('dark');
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: isDark ? '#1e293b' : '#ffffff',
            color: isDark ? '#f8fafc' : '#1e293b',
            iconColor: 'currentColor',
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        @if(session('success'))
            Toast.fire({
                icon: 'success',
                title: '{{ session('success') }}',
                customClass: { popup: 'border border-emerald-500/20 shadow-xl shadow-emerald-900/20', icon: 'text-emerald-400' }
            });
        @endif

        @if(session('error'))
            Toast.fire({
                icon: 'error',
                title: '{{ session('error') }}',
                customClass: { popup: 'border border-red-500/20 shadow-xl shadow-red-900/20', icon: 'text-red-400' }
            });
        @endif

        @if($errors->any())
            Toast.fire({
                icon: 'error',
                title: '{!! implode("<br>", $errors->all()) !!}',
                customClass: { popup: 'border border-red-500/20 shadow-xl shadow-red-900/20', icon: 'text-red-400' }
            });
        @endif

        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit',second:'2-digit'});
            document.getElementById('clock').textContent = timeString;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
    
    {{-- Global Modals for Worksheet Management --}}
    @if(auth()->check() && auth()->user()->isOwner() && isset($userWorksheets))
        {{-- Add Modal --}}
        <div x-data="{ show: false }" x-show="show" @open-modal.window="if ($event.detail === 'add-worksheet') show = true" @close-modal.window="show = false" class="fixed inset-0 z-[99] flex items-center justify-center overflow-y-auto overflow-x-hidden" style="display: none;">
            <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="show = false"></div>
            <div x-show="show" x-transition.scale.origin.bottom class="relative bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-md m-4 z-10 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800/50">
                    <h3 class="text-lg font-black text-white flex items-center gap-2">
                        <i class="fas fa-plus text-emerald-400"></i> Tambah Worksheet Baru
                    </h3>
                    <button @click="show = false" class="text-slate-400 hover:text-white transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form action="{{ route('worksheets.store') }}" method="POST">
                    @csrf
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nama Worksheet <span class="text-red-400">*</span></label>
                            <input type="text" name="name" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-emerald-500 transition-all" required placeholder="Contoh: Cabang Jakarta">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Modal Awal / Saldo Awal (Rp) <span class="text-red-400">*</span></label>
                            <input type="number" name="initial_balance" value="0" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-emerald-500 transition-all" required min="0">
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-700/50 flex justify-end gap-3 bg-slate-800/50">
                        <button type="button" @click="show = false" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-300 hover:bg-slate-700 transition-colors">Batal</button>
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white px-6 py-2 rounded-xl text-sm font-bold shadow-lg shadow-emerald-500/30 transition-all">Tambah</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Edit Modals --}}
        @foreach($userWorksheets as $ws)
        <div x-data="{ show: false }" x-show="show" @open-modal.window="if ($event.detail === 'edit-worksheet-{{ $ws->id }}') show = true" @close-modal.window="show = false" class="fixed inset-0 z-[99] flex items-center justify-center overflow-y-auto overflow-x-hidden" style="display: none;">
            <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="show = false"></div>
            <div x-show="show" x-transition.scale.origin.bottom class="relative bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-md m-4 z-10 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800/50">
                    <h3 class="text-lg font-black text-white flex items-center gap-2">
                        <i class="fas fa-pen text-blue-400"></i> Edit Worksheet
                    </h3>
                    <button @click="show = false" class="text-slate-400 hover:text-white transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form action="{{ route('worksheets.update', $ws) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nama Worksheet <span class="text-red-400">*</span></label>
                            <input type="text" name="name" value="{{ $ws->name }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 transition-all" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Modal Awal / Saldo Awal (Rp) <span class="text-red-400">*</span></label>
                            <input type="number" name="initial_balance" value="{{ $ws->initial_balance }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 transition-all" required min="0">
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-700/50 flex justify-end gap-3 bg-slate-800/50">
                        <button type="button" @click="show = false" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-300 hover:bg-slate-700 transition-colors">Batal</button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/30 transition-all">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
        @endforeach
    @endif

    @stack('scripts')
</body>
</html>
