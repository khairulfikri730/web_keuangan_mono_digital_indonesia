@extends('layouts.app')

@section('title', 'Rekap Penjualan & Margin')
@section('page-title', 'Dashboard Analytics')
@section('page-subtitle', 'Rekap Penjualan & Margin Profit')

@section('content')
<div x-data="analyticsDashboard()" class="flex flex-col gap-6">

    {{-- HEADER & QUICK FILTERS --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-2">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Dashboard Analytics</h1>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mt-1">Rekap Penjualan & Margin Profit</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex bg-slate-800/80 backdrop-blur-md rounded-xl p-1 border border-slate-700/50 shadow-inner" id="periodFilterBar">
                @php
                    $periodFrom = request('date_from');
                    $periodTo = request('date_to');
                    $today = now()->format('Y-m-d');
                    $startOfWeek = now()->startOfWeek()->format('Y-m-d');
                    $startOfMonth = now()->startOfMonth()->format('Y-m-d');
                    $startOfYear = now()->startOfYear()->format('Y-m-d');
                    
                    $activePeriod = 'custom';
                    if (!$periodFrom || ($periodFrom == $today && $periodTo == $today)) $activePeriod = 'today';
                    elseif ($periodFrom == $startOfWeek && $periodTo == $today) $activePeriod = 'week';
                    elseif ($periodFrom == $startOfMonth && $periodTo == $today) $activePeriod = 'month';
                    elseif ($periodFrom == $startOfYear && $periodTo == $today) $activePeriod = 'year';
                @endphp
                <button type="button" onclick="setPeriod('{{ $today }}', '{{ $today }}')" class="px-4 py-2 text-[10px] font-black rounded-lg transition-all {{ $activePeriod == 'today' ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:text-slate-200' }}">HARI INI</button>
                <button type="button" onclick="setPeriod('{{ $startOfWeek }}', '{{ $today }}')" class="px-4 py-2 text-[10px] font-black rounded-lg transition-all {{ $activePeriod == 'week' ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:text-slate-200' }}">MINGGUAN</button>
                <button type="button" onclick="setPeriod('{{ $startOfMonth }}', '{{ $today }}')" class="px-4 py-2 text-[10px] font-black rounded-lg transition-all {{ $activePeriod == 'month' ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:text-slate-200' }}">BULANAN</button>
                <button type="button" onclick="setPeriod('{{ $startOfYear }}', '{{ $today }}')" class="px-4 py-2 text-[10px] font-black rounded-lg transition-all {{ $activePeriod == 'year' ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:text-slate-200' }}">TAHUNAN</button>
            </div>
            
                <span class="text-[10px] font-black text-emerald-400 uppercase tracking-widest">Live Report</span>
            </div>
            
            <button onclick="window.openExportModal()" class="w-11 h-11 bg-slate-800 border border-white/5 text-slate-400 rounded-2xl hover:bg-slate-700 hover:text-white transition-premium flex items-center justify-center shadow-lg" title="Ekspor Laporan (PDF/Excel/CSV)">
                <i class="fas fa-file-export"></i>
            </button>
        </div>
    </div>

    {{-- FILTER PANEL --}}
    <div class="bg-slate-800/40 backdrop-blur-xl rounded-3xl p-6 border border-white/5 shadow-2xl relative z-40 overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-600/5 rounded-full blur-[80px] pointer-events-none"></div>

        <form id="filterForm" action="{{ route('sales.index') }}" method="GET" class="flex flex-col md:flex-row gap-5 items-end md:items-center w-full">
            <div class="flex-1 w-full grid grid-cols-1 md:grid-cols-4 gap-5">
                <div class="col-span-1 md:col-span-2 flex items-center gap-3">
                    <div class="w-full relative group">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1.5 block"><i class="far fa-calendar-alt mr-1"></i> Tanggal Awal</label>
                        <input type="date" id="date_from" name="date_from" value="{{ request('date_from', $dateFrom->format('Y-m-d')) }}" style="color-scheme: dark;" class="w-full bg-[#0f172a] border border-white/10 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:shadow-[0_0_15px_rgba(59,130,246,0.2)] transition-all placeholder-slate-500/60 group-hover:shadow-sm">
                    </div>
                    <span class="text-slate-600 font-black self-end mb-3">-</span>
                    <div class="w-full relative group">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1.5 block"><i class="far fa-calendar-check mr-1"></i> Tanggal Akhir</label>
                        <input type="date" id="date_to" name="date_to" value="{{ request('date_to', $dateTo->format('Y-m-d')) }}" style="color-scheme: dark;" class="w-full bg-[#0f172a] border border-white/10 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:shadow-[0_0_15px_rgba(59,130,246,0.2)] transition-all placeholder-slate-500/60 group-hover:shadow-sm">
                    </div>
                </div>
                
                <div class="group relative">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1.5 block"><i class="fas fa-wallet mr-1"></i> Metode Pembayaran</label>
                    <select name="payment_method" style="color-scheme: dark;" class="w-full bg-[#0f172a] border border-white/10 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:shadow-[0_0_15px_rgba(59,130,246,0.2)] transition-all appearance-none group-hover:shadow-sm">
                        <option value="" class="bg-[#1e293b] hover:bg-slate-700">Semua Metode</option>
                        <option value="cash" class="bg-[#1e293b] hover:bg-slate-700" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Tunai (Cash)</option>
                        <option value="transfer" class="bg-[#1e293b] hover:bg-slate-700" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                        <option value="qris" class="bg-[#1e293b] hover:bg-slate-700" {{ request('payment_method') == 'qris' ? 'selected' : '' }}>QRIS</option>
                        <option value="debit" class="bg-[#1e293b] hover:bg-slate-700" {{ request('payment_method') == 'debit' ? 'selected' : '' }}>Kartu Debit/Kredit</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 bottom-3 text-slate-500 text-xs pointer-events-none group-hover:text-slate-400 transition-colors"></i>
                </div>

                <div class="group relative">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1.5 block"><i class="fas fa-user-tag mr-1"></i> Kasir</label>
                    <select name="user_id" style="color-scheme: dark;" class="w-full bg-[#0f172a] border border-white/10 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:shadow-[0_0_15px_rgba(59,130,246,0.2)] transition-all appearance-none group-hover:shadow-sm">
                        <option value="" class="bg-[#1e293b] hover:bg-slate-700">Semua Kasir</option>
                        @foreach($users as $u)
                        <option value="{{ $u->id }}" class="bg-[#1e293b] hover:bg-slate-700" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 bottom-3 text-slate-500 text-xs pointer-events-none group-hover:text-slate-400 transition-colors"></i>
                </div>
            </div>

            <div class="flex gap-3 shrink-0 w-full md:w-auto mt-4 md:mt-0">
                @if(request()->has('date_from'))
                <a href="{{ route('sales.index') }}" class="py-2.5 px-5 bg-transparent hover:bg-white/5 text-slate-300 hover:text-white font-bold rounded-xl transition-all text-sm border border-white/10 text-center flex-1 md:flex-none">Reset</a>

                @endif
                <button type="submit" class="py-2.5 px-6 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white font-bold rounded-xl transition-all shadow-[0_5px_15px_rgba(59,130,246,0.3)] hover:shadow-[0_5px_20px_rgba(59,130,246,0.4)] text-sm flex items-center justify-center gap-2 flex-1 md:flex-none transform hover:-translate-y-0.5">
                    <i class="fas fa-filter text-xs"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    {{-- KPI & BALANCES GRID --}}
    @php
        $grossSales = $summary->total_sales + $summary->total_discount;
        $marginProfit = $summary->total_sales - $summary->total_cogs;
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Total Omzet --}}
        <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-3xl p-6 shadow-xl shadow-blue-900/20 relative overflow-hidden group border border-white/10">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:scale-125 transition-transform duration-500"></div>
            <div class="flex flex-col h-full justify-between relative z-10">
                <div class="flex items-center justify-between mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-white backdrop-blur-md border border-white/20">
                        <i class="fas fa-chart-line text-xl"></i>
                    </div>
                    <span class="text-[10px] font-black text-blue-100 uppercase tracking-widest bg-white/10 px-2 py-1 rounded-md border border-white/10">Omzet Kotor</span>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-white tracking-tight">Rp {{ number_format($grossSales, 0, ',', '.') }}</h3>
                    <p class="text-blue-100/60 text-[10px] font-bold mt-1 uppercase tracking-widest">{{ $summary->total_count }} Transaksi Selesai</p>
                </div>
            </div>
        </div>

        {{-- Penjualan Bersih --}}
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-3xl p-6 shadow-xl shadow-emerald-900/20 relative overflow-hidden group border border-white/10">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:scale-125 transition-transform duration-500"></div>
            <div class="flex flex-col h-full justify-between relative z-10">
                <div class="flex items-center justify-between mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-white backdrop-blur-md border border-white/20">
                        <i class="fas fa-wallet text-xl"></i>
                    </div>
                    <span class="text-[10px] font-black text-emerald-100 uppercase tracking-widest bg-white/10 px-2 py-1 rounded-md border border-white/10">Penjualan Bersih</span>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-white tracking-tight">Rp {{ number_format($summary->total_sales, 0, ',', '.') }}</h3>
                    <p class="text-emerald-100/60 text-[10px] font-bold mt-1 uppercase tracking-widest">Omzet Riil di Kasir</p>
                </div>
            </div>
        </div>

        {{-- Laba Bersih --}}
        <div class="bg-gradient-to-br {{ $netProfit >= 0 ? 'from-purple-600 to-purple-700 shadow-purple-900/20' : 'from-rose-600 to-rose-700 shadow-rose-900/20' }} rounded-3xl p-6 shadow-xl relative overflow-hidden group border border-white/10">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:scale-125 transition-transform duration-500"></div>
            <div class="flex flex-col h-full justify-between relative z-10">
                <div class="flex items-center justify-between mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-white backdrop-blur-md border border-white/20">
                        <i class="fas {{ $netProfit >= 0 ? 'fa-coins' : 'fa-chart-line rotate-180' }} text-xl"></i>
                    </div>
                    <span class="text-[10px] font-black text-white uppercase tracking-widest bg-white/10 px-2 py-1 rounded-md border border-white/10">Laba Bersih</span>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-white tracking-tight">Rp {{ $netProfit < 0 ? '-' : '' }}{{ number_format(abs($netProfit), 0, ',', '.') }}</h3>
                    <p class="text-white/60 text-[10px] font-bold mt-1 uppercase tracking-widest">{{ $netProfit >= 0 ? 'Keuntungan Bersih' : 'Defisit / Kerugian' }}</p>
                </div>
            </div>
        </div>

        {{-- Total Pengeluaran --}}
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-3xl p-6 shadow-xl shadow-red-900/20 relative overflow-hidden group border border-white/10">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:scale-125 transition-transform duration-500"></div>
            <div class="flex flex-col h-full justify-between relative z-10">
                <div class="flex items-center justify-between mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-white backdrop-blur-md border border-white/20">
                        <i class="fas fa-external-link-alt text-xl"></i>
                    </div>
                    <span class="text-[10px] font-black text-red-100 uppercase tracking-widest bg-white/10 px-2 py-1 rounded-md border border-white/10">Pengeluaran</span>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-white tracking-tight">Rp {{ number_format($totalExpense, 0, ',', '.') }}</h3>
                    <p class="text-red-100/60 text-[10px] font-bold mt-1 uppercase tracking-widest">Biaya Operasional</p>
                </div>
            </div>
        </div>

        {{-- Tunai / Laci (Sub-Card) --}}
        <div class="bg-slate-800/80 backdrop-blur-md rounded-2xl p-5 border border-white/5 shadow-lg group hover:border-blue-500/30 transition-all">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400 group-hover:bg-blue-600 group-hover:text-white transition-all">
                    <i class="fas fa-cash-register"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-0.5">Saldo Tunai</p>
                    <h3 class="text-base font-black text-white">Rp {{ number_format($saldoLaci, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>

        {{-- Saldo Bank (Sub-Card) --}}
        <div class="bg-slate-800/80 backdrop-blur-md rounded-2xl p-5 border border-white/5 shadow-lg group hover:border-purple-500/30 transition-all">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-400 group-hover:bg-purple-600 group-hover:text-white transition-all">
                    <i class="fas fa-university"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-0.5">Saldo Bank</p>
                    <h3 class="text-base font-black text-white">Rp {{ number_format($saldoBank, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>

        {{-- Total Diskon (Sub-Card) --}}
        <div class="bg-slate-800/80 backdrop-blur-md rounded-2xl p-5 border border-white/5 shadow-lg group hover:border-amber-500/30 transition-all">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-400 group-hover:bg-amber-600 group-hover:text-white transition-all">
                    <i class="fas fa-tag"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-0.5">Total Diskon</p>
                    <h3 class="text-base font-black text-white">Rp {{ number_format($summary->total_discount, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>

        {{-- Extra Info / Shortcut --}}
        <div class="bg-slate-800/40 backdrop-blur-md rounded-2xl p-5 border border-white/5 shadow-lg flex items-center justify-center">
            <a href="{{ route('cashflow.index') }}" class="text-[10px] font-black text-blue-400 uppercase tracking-widest hover:text-blue-300 transition-colors flex items-center gap-2">
                Lihat Detail Arus Kas <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    {{-- CHARTS SECTION --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-2">
        
        {{-- LINE CHART --}}
        <div class="lg:col-span-8 bg-slate-800/40 backdrop-blur-md rounded-3xl p-8 border border-white/5 shadow-xl">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-3">
                    <span class="w-2 h-6 bg-blue-500 rounded-full"></span>
                    Tren Penjualan Harian
                </h3>
            </div>
            <div class="relative h-[300px] w-full">
                <canvas id="salesLineChart"></canvas>
            </div>
        </div>

        {{-- PIE CHART --}}
        <div class="lg:col-span-4 bg-slate-800/40 backdrop-blur-md rounded-3xl p-8 border border-white/5 shadow-xl flex flex-col">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-3">
                    <span class="w-2 h-6 bg-emerald-500 rounded-full"></span>
                    Metode Pembayaran
                </h3>
            </div>
            <div class="relative flex-1 flex items-center justify-center min-h-[250px]">
                <canvas id="paymentPieChart"></canvas>
            </div>
        </div>

        {{-- BAR CHART KATEGORI --}}
        <div class="lg:col-span-6 bg-slate-800/40 backdrop-blur-md rounded-3xl p-8 border border-white/5 shadow-xl">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-3">
                    <span class="w-2 h-6 bg-amber-500 rounded-full"></span>
                    Omzet per Kategori
                </h3>
            </div>
            <div class="relative h-[250px] w-full">
                <canvas id="categoryBarChart"></canvas>
            </div>
        </div>

        {{-- BONUS LEVEL PRO: TOP STATS --}}
        <div class="lg:col-span-6 bg-slate-800/40 backdrop-blur-md rounded-3xl p-8 border border-white/5 shadow-xl flex flex-col">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-3">
                    <span class="w-2 h-6 bg-purple-500 rounded-full"></span>
                    Statistik Unggulan
                </h3>
            </div>
            
            <div class="space-y-6 flex-1">
                {{-- Top Product --}}
                <div class="group flex items-center gap-5 bg-slate-900/40 p-5 rounded-2xl border border-white/5 hover:border-blue-500/30 transition-all">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0 border border-blue-500/20 group-hover:bg-blue-600 group-hover:text-white transition-all"><i class="fas fa-crown text-xl"></i></div>
                    <div class="flex-1">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Produk Paling Laris</p>
                        <h4 class="text-base font-black text-white truncate">{{ $topProducts->first() ? $topProducts->first()->product_name : 'Belum Ada' }}</h4>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Terjual</p>
                        <h4 class="text-base font-black text-emerald-400">{{ $topProducts->first() ? $topProducts->first()->total_qty : 0 }}</h4>
                    </div>
                </div>

                {{-- Peak Hour --}}
                <div class="group flex items-center gap-5 bg-slate-900/40 p-5 rounded-2xl border border-white/5 hover:border-orange-500/30 transition-all">
                    <div class="w-12 h-12 rounded-xl bg-orange-500/10 text-orange-400 flex items-center justify-center shrink-0 border border-orange-500/20 group-hover:bg-orange-600 group-hover:text-white transition-all"><i class="fas fa-fire text-xl"></i></div>
                    <div class="flex-1">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Jam Paling Ramai</p>
                        <h4 class="text-base font-black text-white uppercase tracking-tight">{{ $peakHours->first() ? sprintf('%02d:00 - %02d:59', $peakHours->first()->hour, $peakHours->first()->hour) : 'Belum Ada' }}</h4>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Transaksi</p>
                        <h4 class="text-base font-black text-orange-400">{{ $peakHours->first() ? $peakHours->first()->count : 0 }}</h4>
                    </div>
                </div>

                {{-- Most Profitable --}}
                <div class="group flex items-center gap-5 bg-slate-900/40 p-5 rounded-2xl border border-white/5 hover:border-purple-500/30 transition-all">
                    <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center shrink-0 border border-purple-500/20 group-hover:bg-purple-600 group-hover:text-white transition-all"><i class="fas fa-gem text-xl"></i></div>
                    <div class="flex-1">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Produk Paling Untung</p>
                        <h4 class="text-base font-black text-white truncate">{{ $topProducts->sortByDesc('total_margin')->first() ? $topProducts->sortByDesc('total_margin')->first()->product_name : 'Belum Ada' }}</h4>
                    </div>
                    <div class="text-right text-purple-400">
                        <i class="fas fa-arrow-up text-xs"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TRANSACTION TABLE SECTION --}}
    <div class="bg-slate-800/40 backdrop-blur-md rounded-3xl border border-white/5 shadow-2xl overflow-hidden mt-2">
        <div class="p-8 border-b border-white/5 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-3">
                    <span class="w-2 h-6 bg-blue-400 rounded-full"></span>
                    Detail Transaksi
                </h3>
            </div>
            <div class="flex gap-3 w-full md:w-auto">
                <button onclick="window.openExportModal()" class="w-10 h-10 bg-slate-800 border border-white/5 text-slate-400 rounded-2xl hover:bg-slate-700 hover:text-white transition-premium flex items-center justify-center shadow-lg" title="Ekspor Laporan (PDF/Excel/CSV)">
                    <i class="fas fa-file-export"></i>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/5">
                        <th class="px-8 py-5">Invoice & Waktu</th>
                        <th class="px-8 py-5">Kasir</th>
                        <th class="px-8 py-5">Metode</th>
                        <th class="px-8 py-5 text-right">Kotor / Diskon</th>
                        <th class="px-8 py-5 text-right">Total Bersih</th>
                        <th class="px-8 py-5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($transactions as $trx)
                    <tr class="group hover:bg-white/5 transition-colors">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-slate-700/50 flex items-center justify-center text-slate-400 border border-white/5 group-hover:bg-blue-600 group-hover:text-white transition-all">
                                    <i class="fas fa-file-invoice text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-black text-white group-hover:text-blue-400 transition-colors">#{{ $trx->invoice_number }}</p>
                                    <p class="text-[10px] font-bold text-slate-500 mt-0.5 uppercase">{{ $trx->created_at->translatedFormat('d M Y, H:i') }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-slate-700 flex items-center justify-center text-[10px] font-black text-slate-400">
                                    {{ substr($trx->user->name, 0, 1) }}
                                </div>
                                <p class="text-xs font-bold text-slate-300">{{ $trx->user->name }}</p>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            @php
                                $colors = [
                                    'cash' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                    'transfer' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                    'qris' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                    'debit' => 'bg-amber-500/10 text-amber-400 border-amber-500/20'
                                ];
                                $icons = ['cash' => 'fa-money-bill-wave', 'transfer' => 'fa-building-columns', 'qris' => 'fa-qrcode', 'debit' => 'fa-credit-card'];
                                $m = $trx->payment_method;
                            @endphp
                            <span class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest border {{ $colors[$m] ?? 'bg-slate-700 text-slate-300 border-slate-600' }} flex items-center gap-1.5 w-fit">
                                <i class="fas {{ $icons[$m] ?? 'fa-wallet' }} text-[8px]"></i>
                                {{ strtoupper($m) }}
                            </span>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <p class="text-xs font-black text-slate-300">Rp {{ number_format($trx->total_price + $trx->discount, 0, ',', '.') }}</p>
                            @if($trx->discount > 0)
                            <p class="text-[10px] font-bold text-red-400 mt-0.5">- Rp {{ number_format($trx->discount, 0, ',', '.') }}</p>
                            @endif
                        </td>
                        <td class="px-8 py-6 text-right">
                            <p class="text-sm font-black text-emerald-400">Rp {{ number_format($trx->total_price, 0, ',', '.') }}</p>
                        </td>
                        <td class="px-8 py-6 text-center">
                            <a href="{{ route('transactions.show', $trx) }}" class="inline-flex w-8 h-8 rounded-lg bg-slate-700/50 text-slate-400 hover:bg-blue-600 hover:text-white transition-all border border-white/5 items-center justify-center shadow-sm">
                                <i class="fas fa-chevron-right text-xs"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-8 py-12 text-center text-slate-500 font-bold text-sm italic">Belum ada transaksi pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($transactions->hasPages())
        <div class="px-8 py-6 border-t border-white/5 bg-slate-900/20">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>

</div>

{{-- SCRIPT UNTUK CHART.JS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.font.family = "'Inter', 'Nunito', sans-serif";
    
    // Data PHP to JS
    const rawSalesDay = @json($salesPerDay);
    const rawCategory = @json($byCategory);
    const rawPayment = @json($byPayment);

    // LINE CHART: Penjualan Harian
    const ctxLine = document.getElementById('salesLineChart');
    if(ctxLine && rawSalesDay.length > 0) {
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: rawSalesDay.map(d => d.date),
                datasets: [{
                    label: 'Omzet Harian (Rp)',
                    data: rawSalesDay.map(d => d.total),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    pointBackgroundColor: '#1e293b',
                    pointBorderColor: '#3b82f6',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#fff',
                        bodyColor: '#cbd5e1',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 10,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#334155', drawBorder: false },
                        border: { display: false }
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false }
                    }
                }
            }
        });
    }

    // BAR CHART: Omzet per Kategori
    const ctxBar = document.getElementById('categoryBarChart');
    if(ctxBar && rawCategory.length > 0) {
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: rawCategory.map(c => c.category_name),
                datasets: [{
                    label: 'Omzet',
                    data: rawCategory.map(c => c.total),
                    backgroundColor: '#f59e0b',
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        callbacks: {
                            label: function(context) {
                                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#334155' }, border: { display: false }, ticks: { display: false } },
                    x: { grid: { display: false }, border: { display: false } }
                }
            }
        });
    }

    // PIE CHART: Metode Pembayaran
    const ctxPie = document.getElementById('paymentPieChart');
    if(ctxPie && rawPayment.length > 0) {
        const colors = { 'cash': '#10b981', 'transfer': '#3b82f6', 'qris': '#a855f7', 'debit': '#f59e0b' };
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: rawPayment.map(p => p.payment_method.toUpperCase()),
                datasets: [{
                    data: rawPayment.map(p => p.total),
                    backgroundColor: rawPayment.map(p => colors[p.payment_method] || '#64748b'),
                    borderWidth: 2,
                    borderColor: '#1e293b',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { color: '#cbd5e1', padding: 20, usePointStyle: true } },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        callbacks: {
                            label: function(context) {
                                return ' ' + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.raw);
                            }
                        }
                    }
                }
            }
        });
    }
});

function setPeriod(from, to) {
    document.getElementById('date_from').value = from;
    document.getElementById('date_to').value = to;
    document.getElementById('filterForm').submit();
}

function analyticsDashboard() {
    return {
        // Alpine initialization logic if needed
    }
}
</script>
@endsection
