@extends('layouts.app')

@section('title', 'Rekap Penjualan & Margin')
@section('page-title', 'Dashboard Analytics')
@section('page-subtitle', 'Rekap Penjualan & Margin Profit')

@section('content')
<div x-data="analyticsDashboard()" class="flex flex-col gap-6">

    {{-- HEADER & QUICK FILTERS --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-2 relative z-[50]">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Dashboard Analytics</h1>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mt-1">Rekap Penjualan & Margin Profit</p>
        </div>
        <div class="flex items-center gap-3">
            <x-custom-filter :dateFrom="$dateFrom" :dateTo="$dateTo" />
            
            <button onclick="window.openExportModal()" class="w-11 h-11 bg-slate-800 border border-white/5 text-slate-400 rounded-2xl hover:bg-slate-700 hover:text-white transition-premium flex items-center justify-center shadow-lg" title="Ekspor Laporan (PDF/Excel/CSV)">
                <i class="fas fa-file-export"></i>
            </button>
        </div>
    </div>

    {{-- FILTER PANEL --}}
    <div class="bg-slate-800/40 backdrop-blur-xl rounded-3xl p-6 border border-white/5 shadow-2xl relative z-40 overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-600/5 rounded-full blur-[80px] pointer-events-none"></div>

        <form id="filterForm" action="{{ route('sales.index') }}" method="GET" class="flex flex-col md:flex-row gap-5 items-end md:items-center w-full">
            <input type="hidden" name="date_from" value="{{ is_object($dateFrom) ? $dateFrom->format('Y-m-d') : $dateFrom }}">
            <input type="hidden" name="date_to" value="{{ is_object($dateTo) ? $dateTo->format('Y-m-d') : $dateTo }}">
            <div class="flex-1 w-full grid grid-cols-1 md:grid-cols-2 gap-5">
                
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

                {{-- Most Profitable Product --}}
                <div class="group flex items-center gap-5 bg-slate-900/40 p-5 rounded-2xl border border-white/5 hover:border-purple-500/30 transition-all">
                    <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center shrink-0 border border-purple-500/20 group-hover:bg-purple-600 group-hover:text-white transition-all"><i class="fas fa-gem text-xl"></i></div>
                    <div class="flex-1">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Produk Paling Untung</p>
                        <h4 class="text-base font-black text-white truncate">{{ isset($topProfitProduct) && $topProfitProduct ? $topProfitProduct->product_name : 'Belum Ada' }}</h4>
                        <p class="text-[10px] font-bold text-slate-400 mt-1">{{ isset($topProfitProduct) && $topProfitProduct ? 'Terjual: ' . $topProfitProduct->total_qty : '-' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Total Profit</p>
                        <h4 class="text-base font-black text-purple-400">{{ isset($topProfitProduct) && $topProfitProduct ? 'Rp ' . number_format($topProfitProduct->profit, 0, ',', '.') : 'Rp 0' }}</h4>
                        <p class="text-[10px] font-bold text-purple-500/70 mt-1">Margin: {{ isset($topProfitProduct) && $topProfitProduct ? $topProfitProduct->margin : 0 }}%</p>
                    </div>
                </div>

                {{-- Worst Margin Product --}}
                @if(isset($worstMarginProduct) && $worstMarginProduct)
                @php
                    $isLoss = $worstMarginProduct->profit < 0;
                    $isLowMargin = $worstMarginProduct->margin <= 20 && !$isLoss;
                    
                    $badgeColor = $isLoss ? 'bg-red-500/20 text-red-400 border-red-500/30' : ($isLowMargin ? 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30' : 'bg-slate-500/20 text-slate-400 border-slate-500/30');
                    $iconColor = $isLoss ? 'text-red-400 group-hover:bg-red-600' : ($isLowMargin ? 'text-yellow-400 group-hover:bg-yellow-600' : 'text-slate-400 group-hover:bg-slate-600');
                    $borderColor = $isLoss ? 'hover:border-red-500/30' : ($isLowMargin ? 'hover:border-yellow-500/30' : 'hover:border-slate-500/30');
                    $valueColor = $isLoss ? 'text-red-400' : ($isLowMargin ? 'text-yellow-400' : 'text-slate-400');
                    $insight = $isLoss || $isLowMargin ? 'Harga jual terlalu rendah dibanding HPP.' : '';
                    $statusText = $isLoss ? 'Rugi' : ($isLowMargin ? 'Margin Rendah' : 'Tidak Efisien');
                @endphp
                <div class="group flex items-center gap-5 bg-slate-900/40 p-5 rounded-2xl border border-white/5 {{ $borderColor }} transition-all relative overflow-hidden">
                    <div class="absolute right-0 top-0 text-[8px] font-black uppercase tracking-widest px-2 py-1 rounded-bl-lg border-b border-l {{ $badgeColor }}">
                        {{ $statusText }}
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-white/5 {{ $iconColor }} flex items-center justify-center shrink-0 border border-white/10 group-hover:text-white transition-all">
                        <i class="fas {{ $isLoss ? 'fa-arrow-trend-down' : 'fa-exclamation-triangle' }} text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Produk Margin Terendah</p>
                        <h4 class="text-base font-black text-white truncate pr-16">{{ $worstMarginProduct->product_name }}</h4>
                        <div class="flex items-center gap-2 mt-1">
                            <p class="text-[10px] font-bold text-slate-400">Terjual: {{ $worstMarginProduct->total_qty }}</p>
                            @if($insight)
                            <span class="text-[9px] font-bold {{ $valueColor }} opacity-80 italic hidden sm:inline-block"><i class="fas fa-info-circle mr-0.5"></i> {{ $insight }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Profit</p>
                        <h4 class="text-base font-black {{ $valueColor }}">{{ $worstMarginProduct->profit < 0 ? '-' : '' }}Rp {{ number_format(abs($worstMarginProduct->profit), 0, ',', '.') }}</h4>
                        <p class="text-[10px] font-bold {{ $valueColor }} opacity-70 mt-1">Margin: {{ $worstMarginProduct->margin }}%</p>
                    </div>
                </div>
                @endif

                {{-- Most Profitable Hour --}}
                <div class="group flex items-center gap-5 bg-slate-900/40 p-5 rounded-2xl border border-white/5 hover:border-emerald-500/30 transition-all">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/20 group-hover:bg-emerald-600 group-hover:text-white transition-all"><i class="fas fa-chart-line text-xl"></i></div>
                    <div class="flex-1">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Jam Paling Profit</p>
                        <h4 class="text-base font-black text-white uppercase tracking-tight">{{ isset($topProfitableHour) && $topProfitableHour ? sprintf('%02d:00 - %02d:59', $topProfitableHour->hour, $topProfitableHour->hour) : 'Belum Ada' }}</h4>
                        <p class="text-[10px] font-bold text-slate-400 mt-1">{{ isset($topProfitableHour) && $topProfitableHour ? $topProfitableHour->trx_count . ' transaksi' : '-' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Total Profit</p>
                        <h4 class="text-base font-black text-emerald-400">{{ isset($topProfitableHour) && $topProfitableHour ? 'Rp ' . number_format($topProfitableHour->profit, 0, ',', '.') : 'Rp 0' }}</h4>
                        <p class="text-[10px] font-bold text-emerald-500/70 mt-1">Margin: {{ isset($topProfitableHour) && $topProfitableHour ? $topProfitableHour->margin : 0 }}%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- HEATMAP PENJUALAN SECTION --}}
    @if(isset($heatmapMatrix) && isset($heatmapInsights))
    <div class="bg-slate-800/40 backdrop-blur-md rounded-3xl border border-white/5 shadow-2xl overflow-hidden mt-2">
        <div class="p-8 border-b border-white/5 flex flex-col lg:flex-row gap-8 justify-between">
            <div class="flex-1">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-3 mb-2">
                    <span class="w-2 h-6 bg-teal-500 rounded-full"></span>
                    Heatmap Kepadatan Penjualan
                </h3>
                <p class="text-[10px] font-bold text-slate-500">Intensitas warna menunjukkan kepadatan jumlah transaksi berdasarkan hari dan jam.</p>
                
                <div class="mt-8 overflow-x-auto pb-4">
                    <div class="min-w-[800px]">
                        {{-- Hours Header --}}
                        <div class="flex ml-12 mb-2">
                            @for($h=0; $h<24; $h++)
                            <div class="flex-1 text-center text-[9px] font-black text-slate-500">{{ sprintf('%02d', $h) }}</div>
                            @endfor
                        </div>

                        {{-- Matrix --}}
                        <div class="space-y-1">
                            @php
                                $dayLabels = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'];
                            @endphp
                            @for($d=1; $d<=7; $d++)
                            <div class="flex items-center">
                                <div class="w-12 text-[10px] font-black text-slate-400 text-right pr-4">{{ $dayLabels[$d] }}</div>
                                <div class="flex-1 flex gap-1">
                                    @for($h=0; $h<24; $h++)
                                        @php
                                            $cell = $heatmapMatrix[$d][$h];
                                            $trx = $cell['trx_count'];
                                            $rev = $cell['revenue'];
                                            $max = $heatmapInsights['max_trx'] > 0 ? $heatmapInsights['max_trx'] : 1;
                                            $intensity = ($trx / $max); // 0 to 1
                                            
                                            // Determine opacity class
                                            if ($intensity == 0) $bgClass = 'bg-slate-800/50';
                                            elseif ($intensity <= 0.2) $bgClass = 'bg-teal-500/20';
                                            elseif ($intensity <= 0.4) $bgClass = 'bg-teal-500/40';
                                            elseif ($intensity <= 0.6) $bgClass = 'bg-teal-500/60';
                                            elseif ($intensity <= 0.8) $bgClass = 'bg-teal-500/80';
                                            else $bgClass = 'bg-teal-500';
                                        @endphp
                                        <div class="flex-1 aspect-square {{ $bgClass }} rounded-sm relative group cursor-crosshair transition-colors hover:ring-1 hover:ring-white/50 z-10 hover:z-20">
                                            {{-- Tooltip --}}
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-max bg-slate-900 border border-white/10 shadow-xl rounded-xl p-3 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all pointer-events-none">
                                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 border-b border-white/5 pb-1">{{ $dayLabels[$d] }}, {{ sprintf('%02d:00', $h) }} - {{ sprintf('%02d:59', $h) }}</p>
                                                <div class="flex items-center gap-4">
                                                    <div>
                                                        <p class="text-[9px] font-bold text-slate-500 uppercase">Transaksi</p>
                                                        <p class="text-sm font-black text-teal-400">{{ $trx }}x</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-[9px] font-bold text-slate-500 uppercase">Omzet</p>
                                                        <p class="text-sm font-black text-emerald-400">Rp {{ number_format($rev, 0, ',', '.') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>

            {{-- Insights Sidebar --}}
            <div class="lg:w-72 flex flex-col gap-4">
                <div class="bg-slate-900/40 border border-white/5 rounded-2xl p-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0">
                        <i class="fas fa-calendar-day text-lg"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-0.5">Hari Paling Ramai</p>
                        <h4 class="text-sm font-black text-white">{{ $heatmapInsights['busiest_day'] }}</h4>
                    </div>
                </div>
                <div class="bg-slate-900/40 border border-white/5 rounded-2xl p-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center shrink-0">
                        <i class="fas fa-bed text-lg"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-0.5">Hari Paling Sepi</p>
                        <h4 class="text-sm font-black text-white">{{ $heatmapInsights['quietest_day'] }}</h4>
                    </div>
                </div>
                <div class="bg-slate-900/40 border border-white/5 rounded-2xl p-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center shrink-0">
                        <i class="fas fa-clock text-lg"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-0.5">Jam Terbaik</p>
                        <h4 class="text-sm font-black text-white">{{ $heatmapInsights['best_hour'] }}</h4>
                    </div>
                </div>
                <div class="bg-slate-900/40 border border-white/5 rounded-2xl p-4 flex items-center gap-4 border-l-4 border-l-purple-500 relative overflow-hidden group">
                    <div class="absolute right-0 bottom-0 text-purple-500/5 -mb-2 -mr-2 group-hover:scale-110 transition-transform">
                        <i class="fas fa-bullhorn text-6xl"></i>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center shrink-0 relative z-10">
                        <i class="fas fa-bullhorn text-lg"></i>
                    </div>
                    <div class="relative z-10">
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-0.5">Hari Promo Terbaik</p>
                        <h4 class="text-sm font-black text-white">{{ $heatmapInsights['promo_day'] }}</h4>
                        <p class="text-[8px] font-bold text-purple-400/70 mt-1 uppercase">Berdasarkan Hari Sepi</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- INACTIVE PRODUCTS SECTION --}}
    @if(isset($inactiveProducts) && $inactiveProducts->count() > 0)
    <div class="bg-slate-800/40 backdrop-blur-md rounded-3xl border border-white/5 shadow-2xl overflow-hidden mt-2">
        <div class="p-8 border-b border-white/5">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-3">
                <span class="w-2 h-6 bg-rose-500 rounded-full"></span>
                Produk Tidak Aktif / Mati
            </h3>
            <p class="text-[10px] font-bold text-slate-500 mt-2">Daftar produk yang belum terjual selama lebih dari 7 hari berturut-turut.</p>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($inactiveProducts as $ip)
            <div class="bg-slate-900/40 border border-white/5 hover:border-{{ $ip->inactive_color }}-500/30 rounded-2xl p-5 relative overflow-hidden group transition-all">
                <div class="absolute right-0 top-0 text-[8px] font-black uppercase tracking-widest px-2 py-1 rounded-bl-lg border-b border-l bg-{{ $ip->inactive_color }}-500/20 text-{{ $ip->inactive_color }}-400 border-{{ $ip->inactive_color }}-500/30">
                    {{ $ip->inactive_status }}
                </div>
                <div class="flex flex-col gap-1">
                    <h4 class="text-base font-black text-white truncate pr-16" title="{{ $ip->name }}">{{ $ip->name }}</h4>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">{{ $ip->category ? $ip->category->name : 'Tanpa Kategori' }}</p>
                </div>
                <div class="mt-4 pt-4 border-t border-white/5">
                    <p class="text-xs font-bold text-slate-400 mb-1">
                        <i class="far fa-clock w-4"></i> Tdk terjual: <span class="text-{{ $ip->inactive_color }}-400">{{ $ip->days_since_last_sale == 999 ? 'Belum pernah' : $ip->days_since_last_sale . ' hari' }}</span>
                    </p>
                    <p class="text-xs font-bold text-slate-400 mb-1">
                        <i class="fas fa-boxes w-4"></i> Sisa stok: <span class="text-white">{{ $ip->product_kind === 'unlimited' ? '∞ (Unlimited)' : $ip->stock }}</span>
                    </p>
                    @if($ip->product_kind !== 'unlimited')
                    <div class="mt-3 bg-rose-500/10 rounded-lg p-2 border border-rose-500/20">
                        <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest mb-0.5">Potensi Rugi Stok</p>
                        <p class="text-sm font-black text-rose-400">Rp {{ number_format($ip->potential_loss, 0, ',', '.') }}</p>
                    </div>
                    @else
                    <div class="mt-3 bg-slate-500/10 rounded-lg p-2 border border-slate-500/20">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-0.5">Status Perhatian</p>
                        <p class="text-sm font-black text-slate-400">Kurang Peminat</p>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

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
                            <p class="text-xs font-black text-slate-300">Rp {{ number_format($trx->total + $trx->discount, 0, ',', '.') }}</p>
                            @if($trx->discount > 0)
                            <p class="text-[10px] font-bold text-red-400 mt-0.5">- Rp {{ number_format($trx->discount, 0, ',', '.') }}</p>
                            @endif
                        </td>
                        <td class="px-8 py-6 text-right">
                            <p class="text-sm font-black text-emerald-400">Rp {{ number_format($trx->total, 0, ',', '.') }}</p>
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

function analyticsDashboard() {
    return {
        // no-op
    }
}
</script>
@endsection
